<?php

namespace Modules\BookingModule\Http\Controllers\Web\Admin;

use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Entities\BookingAdditionalInformation;
use Modules\BookingModule\Entities\BookingDetail;
use Modules\BookingModule\Entities\BookingScheduleHistory;
use Illuminate\Http\RedirectResponse;
use Modules\BookingModule\Entities\BookingStatusHistory;
use Modules\BookingModule\Http\Traits\BookingTrait;
use Modules\CategoryManagement\Entities\Category;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ProviderManagement\Entities\SubscribedService;
use Modules\ServiceManagement\Entities\Service;
use Modules\ServiceManagement\Entities\Variation;
use Modules\UserManagement\Entities\Serviceman;
use Modules\UserManagement\Entities\UserAddress;
use Modules\ZoneManagement\Entities\Zone;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BookingController extends Controller
{

    private Booking $booking;
    private BookingStatusHistory $bookingStatusHistory;
    private BookingScheduleHistory $bookingScheduleHistory;
    private $subscribedSubCategories;
    private Category $category;
    private Zone $zone;
    private Serviceman $serviceman;
    private Provider $provider;
    private UserAddress $userAddress;
    private BookingDetail $bookingDetails;
    private BookingAdditionalInformation $bookingAdditionalInformation;

    use BookingTrait;
    use AuthorizesRequests;

    public function __construct(Booking $booking, BookingDetail $bookingDetails, BookingStatusHistory $bookingStatusHistory, BookingScheduleHistory $bookingScheduleHistory, SubscribedService $subscribedService, Category $category, Zone $zone, Serviceman $serviceman, Provider $provider, UserAddress $userAddress, BookingAdditionalInformation $bookingAdditionalInformation)
    {
        $this->booking = $booking;
        $this->bookingDetails = $bookingDetails;
        $this->bookingStatusHistory = $bookingStatusHistory;
        $this->bookingScheduleHistory = $bookingScheduleHistory;
        $this->category = $category;
        $this->zone = $zone;
        $this->serviceman = $serviceman;
        $this->provider = $provider;
        $this->userAddress = $userAddress;
        $this->bookingAdditionalInformation = $bookingAdditionalInformation;
        try {
            $this->subscribedSubCategories = $subscribedService->where(['is_subscribed' => 1])->pluck('sub_category_id')->toArray();
        } catch (\Exception $exception) {
            $this->subscribedSubCategories = $subscribedService->pluck('sub_category_id')->toArray();
        }
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     * @throws AuthorizationException
     */
    public function index(Request $request): Renderable
    {
        $this->authorize('booking_view');
        $request->validate([
            'booking_status' => 'in:' . implode(',', array_column(BOOKING_STATUSES, 'key')) . ',all',
        ]);

        $queryParams = $request->only(['zone_ids', 'category_ids', 'sub_category_ids', 'start_date', 'end_date', 'search']);
        $filterCounter = collect($queryParams)->filter()->count();
        $bookingStatus = $queryParams['booking_status'] = $request->input('booking_status', 'pending');
        $queryParams['booking_type'] = $request->input('booking_type', '');
        if (empty($queryParams['start_date'])) {
            $queryParams['start_date'] = null;
        }
        if (empty($queryParams['end_date'])) {
            $queryParams['end_date'] = null;
        }


        $maxBookingAmount = (business_config('max_booking_amount', 'booking_setup'))->live_values;
        $bookings = $this->booking
            ->with(['customer'])
            ->search($request['search'], ['readable_id'])
            ->when($bookingStatus != 'all', function ($query) use ($bookingStatus, $maxBookingAmount, $request) {
                $query->when($bookingStatus == 'pending', function ($query) use ($maxBookingAmount) {
                    $query->adminPendingBookings($maxBookingAmount);
                })->when($bookingStatus == 'accepted', function ($query) use ($maxBookingAmount) {
                    $query->adminAcceptedBookings($maxBookingAmount);
                })->ofBookingStatus($request['booking_status']);
            })
            ->filterByZoneIds($request['zone_ids'])
            ->filterBySubcategoryIds($request['sub_category_ids'])
            ->filterByCategoryIds($request['category_ids'])
            ->filterByDateRange($request['start_date'], $request['end_date'])
            ->latest()
            ->paginate(pagination_limit())
            ->appends($queryParams);

        $zones = $this->zone->withoutGlobalScope('translate')->select('id', 'name')->get();
        $categories = $this->category->select('id', 'parent_id', 'name')->where('position', 1)->get();
        $subCategories = $this->category->select('id', 'parent_id', 'name')->where('position', 2)->get();

        return view('bookingmodule::admin.booking.list', compact('bookings', 'zones', 'categories', 'subCategories', 'queryParams', 'filterCounter'));
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function checkBooking(): Renderable
    {
        $this->booking->where('is_checked', 0)->update(['is_checked' => 1]);
    }

    /**
     * @param Request $request
     * @return Factory|View|Application
     * @throws AuthorizationException
     */
    public function bookingVerificationList(Request $request): Factory|View|Application
    {
        $this->authorize('booking_view');
        $request->validate([
            'booking_status' => 'in:' . implode(',', array_column(BOOKING_STATUSES, 'key')) . ',all',
            'type' => 'in:pending,denied'
        ]);
        $request['booking_status'] = $request['booking_status'] ?? 'pending';

        $queryParams = [];
        $filterCounter = 0;
        $type = $request->type ?? 'pending';

        if ($request->has('zone_ids')) {
            $zoneIds = $request['zone_ids'];
            $queryParams['zone_ids'] = $zoneIds;
            $filterCounter += count($zoneIds);
        }

        if ($request->has('category_ids')) {
            $categoryIds = $request['category_ids'];
            $queryParams['category_ids'] = $categoryIds;
            $filterCounter += count($categoryIds);
        }

        if ($request->has('sub_category_ids')) {
            $subCategoryIds = $request['sub_category_ids'];
            $queryParams['sub_category_ids'] = $subCategoryIds;
            $filterCounter += count($subCategoryIds);
        }

        if ($request->has('start_date')) {
            $startDate = $request['start_date'];
            $queryParams['start_date'] = $startDate;
            if (!is_null($request['start_date'])) $filterCounter++;
        } else {
            $queryParams['start_date'] = null;
        }

        if ($request->has('end_date')) {
            $endDate = $request['end_date'];
            $queryParams['end_date'] = $endDate;
            if (!is_null($request['end_date'])) $filterCounter++;
        } else {
            $queryParams['end_date'] = null;
        }

        if ($request->has('search')) {
            $search = $request['search'];
            $queryParams['search'] = $search;
        }

        $queryParams['type'] = $type;

        if ($request->has('booking_status')) {
            $bookingStatus = $request['booking_status'];
            $queryParams['booking_status'] = $bookingStatus;
        } else {
            $queryParams['booking_status'] = 'pending';
        }

        $maxBookingAmount = (business_config('max_booking_amount', 'booking_setup'))->live_values;

        $bookings = $this->booking->with(['customer'])
            ->when($request->has('search'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $keys = explode(' ', $request['search']);
                    foreach ($keys as $key) {
                        $query->orWhere('readable_id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($bookingStatus == 'pending', function ($query) use ($maxBookingAmount, $type) {
                $query->when($type == 'pending', function ($query) {
                    $query->where('is_verified', '0');
                })->when($type == 'denied', function ($query) {
                    $query->where('is_verified', '2');
                })
                    ->where('payment_method', 'cash_after_service')
                    ->Where('total_booking_amount', '>', $maxBookingAmount)
                    ->whereIn('booking_status', ['pending', 'accepted']);
            })
            ->when($request->has('zone_ids'), function ($query) use ($request) {
                $query->whereIn('zone_id', $request['zone_ids']);
            })->when($queryParams['start_date'] != null && $queryParams['end_date'] != null, function ($query) use ($request) {
                if ($request['start_date'] == $request['end_date']) {
                    $query->whereDate('created_at', Carbon::parse($request['start_date'])->startOfDay());
                } else {
                    $query->whereBetween('created_at', [Carbon::parse($request['start_date'])->startOfDay(), Carbon::parse($request['end_date'])->endOfDay()]);
                }
            })->when($request->has('sub_category_ids'), function ($query) use ($request) {
                $query->whereIn('sub_category_id', $request['sub_category_ids']);
            })->when($request->has('category_ids'), function ($query) use ($request) {
                $query->whereIn('category_id', $request['category_ids']);
            })
            ->latest()->paginate(pagination_limit())->appends($queryParams);

        $zones = $this->zone->select('id', 'name')->withoutGlobalScope('translate')->get();
        $categories = $this->category->select('id', 'parent_id', 'name')->where('position', 1)->get();
        $subCategories = $this->category->select('id', 'parent_id', 'name')->where('position', 2)->get();

        return view('bookingmodule::admin.booking.verification-list', compact('bookings', 'zones', 'categories', 'subCategories', 'queryParams', 'filterCounter', 'type'));
    }

    /**
     * @param Request $request
     * @return Factory|View|Application
     * @throws AuthorizationException
     */
    public function bookingOfflinePaymentList(Request $request): Factory|View|Application
    {
        $this->authorize('booking_view');
        $request['booking_status'] = $request['booking_status'] ?? 'pending';

        $queryParams = [];
        $filterCounter = 0;

        if ($request->has('zone_ids')) {
            $zoneIds = $request['zone_ids'];
            $queryParams['zone_ids'] = $zoneIds;
            $filterCounter += count($zoneIds);
        }

        if ($request->has('category_ids')) {
            $categoryIds = $request['category_ids'];
            $queryParams['category_ids'] = $categoryIds;
            $filterCounter += count($categoryIds);
        }

        if ($request->has('sub_category_ids')) {
            $subCategoryIds = $request['sub_category_ids'];
            $queryParams['sub_category_ids'] = $subCategoryIds;
            $filterCounter += count($subCategoryIds);
        }

        if ($request->has('start_date')) {
            $startDate = $request['start_date'];
            $queryParams['start_date'] = $startDate;
            if (!is_null($request['start_date'])) $filterCounter++;
        } else {
            $queryParams['start_date'] = null;
        }

        if ($request->has('end_date')) {
            $endDate = $request['end_date'];
            $queryParams['end_date'] = $endDate;
            if (!is_null($request['end_date'])) $filterCounter++;
        } else {
            $queryParams['end_date'] = null;
        }

        if ($request->has('search')) {
            $search = $request['search'];
            $queryParams['search'] = $search;
        }

        if ($request->has('booking_status')) {
            $bookingStatus = $request['booking_status'];
            $queryParams['booking_status'] = $bookingStatus;
        } else {
            $queryParams['booking_status'] = 'pending';
        }

        $maxBookingAmount = (business_config('max_booking_amount', 'booking_setup'))->live_values;

        $bookings = $this->booking->with(['customer'])
            ->when($request->has('search'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $keys = explode(' ', $request['search']);
                    foreach ($keys as $key) {
                        $query->orWhere('readable_id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->whereIn('booking_status', ['pending', 'accepted'])
            ->where('payment_method', 'offline_payment')->where('is_paid', 0)
            ->when($request->has('zone_ids'), function ($query) use ($request) {
                $query->whereIn('zone_id', $request['zone_ids']);
            })->when($queryParams['start_date'] != null && $queryParams['end_date'] != null, function ($query) use ($request) {
                if ($request['start_date'] == $request['end_date']) {
                    $query->whereDate('created_at', Carbon::parse($request['start_date'])->startOfDay());
                } else {
                    $query->whereBetween('created_at', [Carbon::parse($request['start_date'])->startOfDay(), Carbon::parse($request['end_date'])->endOfDay()]);
                }
            })->when($request->has('sub_category_ids'), function ($query) use ($request) {
                $query->whereIn('sub_category_id', $request['sub_category_ids']);
            })->when($request->has('category_ids'), function ($query) use ($request) {
                $query->whereIn('category_id', $request['category_ids']);
            })
            ->latest()->paginate(pagination_limit())->appends($queryParams);

        $zones = $this->zone->select('id', 'name')->withoutGlobalScope('translate')->get();
        $categories = $this->category->select('id', 'parent_id', 'name')->where('position', 1)->get();
        $subCategories = $this->category->select('id', 'parent_id', 'name')->where('position', 2)->get();

        return view('bookingmodule::admin.booking.offline-payment-list', compact('bookings', 'zones', 'categories', 'subCategories', 'queryParams', 'filterCounter'));
    }

    /**
     * Display a listing of the resource.
     * @param $id
     * @param Request $request
     * @return Renderable|RedirectResponse
     * @throws AuthorizationException
     */
    public function details($id, Request $request): Renderable|RedirectResponse
    {
        $this->authorize('booking_view');
        Validator::make($request->all(), [
            'web_page' => 'required|in:details,status',
        ]);
        $webPage = $request->has('web_page') ? $request['web_page'] : 'business_setup';

        if ($request->web_page == 'details') {

            $booking = $this->booking->with(['detail.service' => function ($query) {
                $query->withTrashed();
            }, 'detail.service.category', 'detail.service.subCategory', 'detail.variation', 'customer', 'provider', 'service_address', 'serviceman', 'service_address', 'status_histories.user'])->find($id);

            $servicemen = $this->serviceman->with(['user'])
                ->where('provider_id', $booking?->provider_id)
                ->whereHas('user', function ($query) {
                    $query->ofStatus(1);
                })
                ->latest()
                ->get();

            $category = $booking?->detail?->first()?->service?->category;
            $subCategory = $booking?->detail?->first()?->service?->subCategory;
            $services = Service::select('id', 'name')->where('category_id', $category->id)->where('sub_category_id', $subCategory->id)->get();

            $customerAddress = $this->userAddress->find($booking['service_address_id']);
            $zones = Zone::ofStatus(1)->withoutGlobalScope('translate')->get();

            $providers = $this->provider
                ->when($request->has('search'), function ($query) use ($request) {
                    $keys = explode(' ', $request['search']);
                    return $query->where(function ($query) use ($keys) {
                        foreach ($keys as $key) {
                            $query->orWhere('company_phone', 'LIKE', '%' . $key . '%')
                                ->orWhere('company_email', 'LIKE', '%' . $key . '%')
                                ->orWhere('company_name', 'LIKE', '%' . $key . '%');
                        }
                    });
                })
                ->when(isset($booking->sub_category_id), function ($query) use ($request, $booking) {
                    $query->whereHas('subscribed_services', function ($query) use ($request, $booking) {
                        $query->where('sub_category_id', $booking->sub_category_id)->where('is_subscribed', 1);
                    });
                })
                ->where('zone_id', $booking->zone_id)
                ->withCount('bookings', 'reviews')
                ->when(business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values, function ($query) {
                    $query->where('is_suspended', 0);
                })
                ->where('service_availability', 1)
                ->withCount('reviews')
                ->ofApproval(1)->ofStatus(1)->get();

            $sort_by = 'default';
            $id = "325778a8-53bd-4de5-a6bb-826f62edf603";
            $zoneCenter = Zone::selectRaw("*,ST_AsText(ST_Centroid(`coordinates`)) as center")->withoutGlobalScope('translate')->find($id);

            $currentZone = [];
            $centerLat = [];
            $centerLng = [];
            $area = [];

            if (isset($zoneCenter)) {
                $currentZone = format_coordinates(json_decode($zoneCenter->coordinates[0]->toJson(),true));
                $centerLat = trim(explode(' ', $zoneCenter->center)[1], 'POINT()');
                $centerLng = trim(explode(' ', $zoneCenter->center)[0], 'POINT()');

                $area = json_decode($zoneCenter->coordinates[0]->toJson(),true);
            }

            return view('bookingmodule::admin.booking.details', compact('zoneCenter', 'currentZone', 'centerLat', 'centerLng', 'area','booking', 'servicemen', 'webPage', 'customerAddress', 'services', 'zones', 'category', 'subCategory', 'providers', 'sort_by'));

        } elseif ($request->web_page == 'status') {
            $booking = $this->booking->with(['detail.service', 'customer', 'provider', 'service_address', 'serviceman.user', 'service_address', 'status_histories.user'])->find($id);
            $servicemen = $this->serviceman->with(['user'])
                ->where('provider_id', $booking?->provider_id)
                ->whereHas('user', function ($query) {
                    $query->ofStatus(1);
                })
                ->latest()
                ->get();
            $category = $booking?->detail?->first()?->service?->category;
            $subCategory = $booking?->detail?->first()?->service?->subCategory;
            $services = Service::select('id', 'name')->where('category_id', $category->id)->where('sub_category_id', $subCategory->id)->get();
            $customerAddress = $this->userAddress->find($booking['service_address_id']);
            $zones = Zone::ofStatus(1)->withoutGlobalScope('translate')->get();

            $providers = $this->provider
                ->when($request->has('search'), function ($query) use ($request) {
                    $keys = explode(' ', $request['search']);
                    return $query->where(function ($query) use ($keys) {
                        foreach ($keys as $key) {
                            $query->orWhere('company_phone', 'LIKE', '%' . $key . '%')
                                ->orWhere('company_email', 'LIKE', '%' . $key . '%')
                                ->orWhere('company_name', 'LIKE', '%' . $key . '%');
                        }
                    });
                })
                ->when(isset($booking->sub_category_id), function ($query) use ($request, $booking) {
                    $query->whereHas('subscribed_services', function ($query) use ($request, $booking) {
                        $query->where('sub_category_id', $booking->sub_category_id)->where('is_subscribed', 1);
                    });
                })
                ->where('zone_id', $booking->zone_id)
                ->withCount('bookings', 'reviews')
                ->ofApproval(1)->ofStatus(1)->get();
            $sort_by = 'default';
            return view('bookingmodule::admin.booking.status', compact('booking', 'webPage', 'servicemen', 'customerAddress', 'category', 'subCategory', 'services', 'providers', 'zones', 'sort_by'));
        }

        Toastr::success(translate(ACCESS_DENIED['message']));
        return back();
    }

    /**
     * Display a listing of the resource.
     * @param $bookingId
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function statusUpdate($bookingId, Request $request): JsonResponse
    {
        $this->authorize('booking_can_manage_status');

        Validator::make($request->all(), [
            'booking_status' => 'required|in:' . implode(',', array_column(BOOKING_STATUSES, 'key')),
        ]);

        $booking = $this->booking->where('id', $bookingId)->first();

        if (isset($booking)) {
            $booking->booking_status = $request['booking_status'];

            $bookingStatusHistory = $this->bookingStatusHistory;
            $bookingStatusHistory->booking_id = $bookingId;
            $bookingStatusHistory->changed_by = $request->user()->id;
            $bookingStatusHistory->booking_status = $request['booking_status'];

            if ($booking->isDirty('booking_status')) {
                DB::transaction(function () use ($bookingStatusHistory, $booking) {
                    $booking->save();
                    $bookingStatusHistory->save();
                });

                return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
            }
            return response()->json(response_formatter(NO_CHANGES_FOUND), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }

    public function verificationUpdate($bookingId, Request $request): JsonResponse
    {
        $this->authorize('booking_can_manage_status');

        $booking = $this->booking->where('id', $bookingId)->first();
        if (isset($booking)) {
            $booking->is_verified = 1;
            $booking->save();

            if (isset($booking->provider_id)) {
                $fcmToken = Provider::with('owner')->whereId($booking->provider_id)->first()->owner->fcm_token ?? null;
                $language_key = $this->provider->with('owner')->whereId($booking->provider_id)->first()->owner?->current_language_key;
                if (!is_null($fcmToken) && (!business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values || $booking?->provider?->is_suspended == 0)) {
                    $title = get_push_notification_message('new_service_request_arrived', 'provider_notification', $language_key);
                    device_notification($fcmToken, $title, null, null, $booking->id, 'booking');
                }
            } else {
                $provider_ids = SubscribedService::where('sub_category_id', $booking->sub_category_id)->ofSubscription(1)->pluck('provider_id')->toArray();
                $providers = Provider::with('owner')->whereIn('id', $provider_ids)->where('zone_id', $booking->zone_id)->get();
                foreach ($providers as $provider) {
                    $fcmToken = $provider->owner->fcm_token ?? null;
                    $title = get_push_notification_message('booking_accepted', 'provider_notification', $provider?->owner?->current_language_key);
                    if (!is_null($fcmToken) && (!business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values || $provider?->is_suspended == 0)) device_notification($fcmToken, $title, null, null, $booking->id, 'booking');
                }
            }
            return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }


    /**
     * @param $bookingId
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function verificationStatus($bookingId, Request $request): RedirectResponse
    {

        $this->authorize('booking_can_manage_status');

        $request->validate([
            'status' => 'required|in:approve,deny,cancel',
            'booking_deny_note' => 'required_if:status,deny|string|nullable'
        ]);

        $booking = $this->booking->where('id', $bookingId)->first();
        if (isset($booking) && $request->status == 'deny') {
            $booking->is_verified = 2;
            $booking->save();

            $additionalInfo = new $this->bookingAdditionalInformation;
            $additionalInfo->booking_id = $booking->id;
            $additionalInfo->key = 'booking_deny_note';
            $additionalInfo->value = $request->booking_deny_note;
            $additionalInfo->save();

            Toastr::success(translate(DEFAULT_STORE_200['message']));
            return back();
        } elseif (isset($booking) && $request->status == 'approve') {
            $booking->is_verified = 1;
            $booking->save();

            if (isset($booking->provider_id)) {
                $fcmToken = Provider::with('owner')->whereId($booking->provider_id)->first()->owner->fcm_token ?? null;
                $language_key = $this->provider->with('owner')->whereId($booking->provider_id)->first()->owner?->current_language_key;
                if (!is_null($fcmToken) && (!business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values || $booking?->provider?->is_suspended == 0)) {
                    $title = get_push_notification_message('booking_accepted', 'provider_notification', $language_key);
                    device_notification($fcmToken, $title, null, null, $booking->id, 'booking');
                }
            } else {
                $provider_ids = SubscribedService::where('sub_category_id', $booking->sub_category_id)->ofSubscription(1)->pluck('provider_id')->toArray();
                $providers = Provider::with('owner')->whereIn('id', $provider_ids)->where('zone_id', $booking->zone_id)->get();
                foreach ($providers as $provider) {
                    $fcmToken = $provider->owner->fcm_token ?? null;
                    $title = get_push_notification_message('booking_accepted', 'provider_notification', $provider?->owner?->current_language_key);
                    if (!is_null($fcmToken) && (!business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values || $provider?->is_suspended == 0)) device_notification($fcmToken, $title, null, null, $booking->id, 'booking');
                }
            }

            Toastr::success(translate(DEFAULT_STATUS_UPDATE_200['message']));
            return back();
        } elseif (isset($booking) && $request->status == 'cancel') {
            $booking->booking_status = 'canceled';
            $booking->is_verified = 3;

            $bookingStatusHistory = $this->bookingStatusHistory;
            $bookingStatusHistory->booking_id = $bookingId;
            $bookingStatusHistory->changed_by = $request->user()->id;
            $bookingStatusHistory->booking_status = 'canceled';

            if ($booking->isDirty('booking_status')) {
                DB::transaction(function () use ($bookingStatusHistory, $booking) {
                    $booking->save();
                    $bookingStatusHistory->save();
                });

                Toastr::success(translate(DEFAULT_STATUS_UPDATE_200['message']));
                return back();
            }
        }

        Toastr::success(translate(DEFAULT_404['message']));
        return back();

    }

    /**
     * Display a listing of the resource.
     * @param $bookingId
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function paymentUpdate($bookingId, Request $request): JsonResponse
    {
        $this->authorize('booking_can_manage_status');

        Validator::make($request->all(), [
            'payment_status' => 'required|in:1,0',
        ]);

        $booking = $this->booking->where('id', $bookingId)->first();
        if (isset($booking)) {
            $booking->is_paid = $request->payment_status == '1' ? 1 : 0;

            if ($booking->isDirty('is_paid')) {
                $booking->save();
                return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
            }
            return response()->json(response_formatter(NO_CHANGES_FOUND), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }

    /**
     * Display a listing of the resource.
     * @param $bookingId
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function scheduleUpadte($bookingId, Request $request): JsonResponse
    {
        $this->authorize('booking_can_manage_status');

        Validator::make($request->all(), [
            'service_schedule' => 'required',
        ]);

        $booking = $this->booking->where('id', $bookingId)->first();

        if (isset($booking)) {
            $booking->service_schedule = Carbon::parse($request->service_schedule)->toDateTimeString();

            $bookingScheduleHistory = $this->bookingScheduleHistory;
            $bookingScheduleHistory->booking_id = $bookingId;
            $bookingScheduleHistory->changed_by = $request->user()->id;
            $bookingScheduleHistory->schedule = $request['service_schedule'];

            if ($booking->isDirty('service_schedule')) {
                $booking->save();
                $bookingScheduleHistory->save();
                return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
            }
            return response()->json(response_formatter(NO_CHANGES_FOUND), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }

    /**
     * Display a listing of the resource.
     * @param $bookingId
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function servicemanUpdate($bookingId, Request $request): JsonResponse
    {
        $this->authorize('booking_can_manage_status');

        Validator::make($request->all(), [
            'serviceman_id' => 'required|uuid',
        ]);

        $booking = $this->booking->where('id', $bookingId)->first();

        if (isset($booking)) {
            $booking->serviceman_id = $request->serviceman_id;

            if ($booking->isDirty('serviceman_id')) {
                $booking->save();
                return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
            }
            return response()->json(response_formatter(NO_CHANGES_FOUND), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }

    /**
     * Display a listing of the resource.
     * @param $service_address_id
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function serviceAddressUpdate($service_address_id, Request $request): RedirectResponse
    {
        $this->authorize('booking_edit');

        Validator::make($request->all(), [
            'city' => 'required',
            'street' => 'required',
            'zip_code' => 'required',
            'country' => 'required',
            'address' => 'required',
            'contact_person_name' => 'required',
            'contact_person_number' => 'required',
            'address_label' => 'required',
            'zone_id' => 'required|uuid',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $userAddress = $this->userAddress->find($service_address_id);
        $userAddress->city = $request['city'];
        $userAddress->street = $request['street'];
        $userAddress->zip_code = $request['zip_code'];
        $userAddress->country = $request['country'];
        $userAddress->address = $request['address'];
        $userAddress->contact_person_name = $request['contact_person_name'];
        $userAddress->contact_person_number = $request['contact_person_number'];
        $userAddress->address_label = $request['address_label'];
        $userAddress->zone_id = $request['zone_id'];
        $userAddress->lat = $request['latitude'];
        $userAddress->lon = $request['longitude'];
        $userAddress->save();

        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return string|StreamedResponse
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function download(Request $request): string|StreamedResponse
    {
        $this->authorize('booking_export');

        $request->validate([
            'booking_status' => 'in:' . implode(',', array_column(BOOKING_STATUSES, 'key')) . ',all',
        ]);

        $queryParams = [];
        $filterCounter = 0;
        $type = $request->type ?? 'pending';

        if ($request->has('zone_ids')) {
            $zoneIds = $request['zone_ids'];
            $queryParams['zone_ids'] = $zoneIds;
            $filterCounter += count($zoneIds);
        }

        if ($request->has('category_ids')) {
            $categoryIds = $request['category_ids'];
            $queryParams['category_ids'] = $categoryIds;
            $filterCounter += count($categoryIds);
        }

        if ($request->has('sub_category_ids')) {
            $subCategoryIds = $request['sub_category_ids'];
            $queryParams['sub_category_ids'] = $subCategoryIds;
            $filterCounter += count($subCategoryIds);
        }

        if ($request->has('start_date')) {
            $startDate = $request['start_date'];
            $queryParams['start_date'] = $startDate;
            if (!is_null($request['start_date'])) $filterCounter++;
        } else {
            $queryParams['start_date'] = null;
        }

        if ($request->has('end_date')) {
            $endDate = $request['end_date'];
            $queryParams['end_date'] = $endDate;
            if (!is_null($request['end_date'])) $filterCounter++;
        } else {
            $queryParams['end_date'] = null;
        }

        if ($request->has('search')) {
            $search = $request['search'];
            $queryParams['search'] = $search;
        }

        $queryParams['type'] = $type;

        if ($request->has('booking_status')) {
            $bookingStatus = $request['booking_status'];
            $queryParams['booking_status'] = $bookingStatus;
        } else {
            $queryParams['booking_status'] = 'pending';
        }
        $maxBookingAmount = (business_config('max_booking_amount', 'booking_setup'))->live_values;
        $items = $this->booking
            ->with(['customer'])
            ->when($request->has('search'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $keys = explode(' ', $request['search']);
                    foreach ($keys as $key) {
                        $query->orWhere('readable_id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($bookingStatus == 'pending', function ($query) use ($maxBookingAmount, $type) {
                $query->when($type == 'pending', function ($query) {
                    $query->where('is_verified', '0');
                })->when($type == 'denied', function ($query) {
                    $query->where('is_verified', '2');
                })
                    ->where('payment_method', 'cash_after_service')
                    ->Where('total_booking_amount', '>', $maxBookingAmount)
                    ->whereIn('booking_status', ['pending', 'accepted']);
            })
            ->when($request->has('zone_ids'), function ($query) use ($request) {
                $query->whereIn('zone_id', $request['zone_ids']);
            })->when($queryParams['start_date'] != null && $queryParams['end_date'] != null, function ($query) use ($request) {
                if ($request['start_date'] == $request['end_date']) {
                    $query->whereDate('created_at', Carbon::parse($request['start_date'])->startOfDay());
                } else {
                    $query->whereBetween('created_at', [Carbon::parse($request['start_date'])->startOfDay(), Carbon::parse($request['end_date'])->endOfDay()]);
                }
            })->when($request->has('sub_category_ids'), function ($query) use ($request) {
                $query->whereIn('sub_category_id', $request['sub_category_ids']);
            })->when($request->has('category_ids'), function ($query) use ($request) {
                $query->whereIn('category_id', $request['category_ids']);
            })
            ->latest()->get();

        return (new FastExcel($items))->download(time() . '-file.xlsx');
    }


    /**
     * Display a listing of the resource.
     * @param $id
     * @param Request $request
     * @return Renderable
     */
    public function invoice($id, Request $request): Renderable
    {
        $booking = $this->booking->with(['detail.service' => function ($query) {
            $query->withTrashed();
        }, 'customer', 'provider', 'service_address', 'serviceman', 'service_address', 'status_histories.user'])->find($id);
        return view('bookingmodule::admin.booking.invoice', compact('booking'));
    }

    /**
     * Display a listing of the resource.
     * @param $id
     * @param Request $request
     * @return Renderable
     */
    public function customerInvoice($id, $lang): Renderable
    {
        App::setLocale($lang);
        $booking = $this->booking->with(['detail.service' => function ($query) {
            $query->withTrashed();
        }, 'customer', 'provider', 'service_address', 'serviceman', 'service_address', 'status_histories.user'])->find($id);
        return view('bookingmodule::admin.booking.invoice', compact('booking'));
    }

    /**
     * Display a listing of the resource.
     * @param $id
     * @param Request $request
     * @return Renderable
     */
    public function providerInvoice($id, $lang): Renderable
    {
        App::setLocale($lang);
        $booking = $this->booking->with(['detail.service' => function ($query) {
            $query->withTrashed();
        }, 'customer', 'provider', 'service_address', 'serviceman', 'service_address', 'status_histories.user'])->find($id);
        return view('bookingmodule::admin.booking.invoice', compact('booking'));
    }

    /**
     * Display a listing of the resource.
     * @param $id
     * @param Request $request
     * @return Renderable
     */
    public function servicemanInvoice($id, $lang): Renderable
    {
        App::setLocale($lang);
        $booking = $this->booking->with(['detail.service' => function ($query) {
            $query->withTrashed();
        }, 'customer', 'provider', 'service_address', 'serviceman', 'service_address', 'status_histories.user'])->find($id);
        return view('bookingmodule::admin.booking.invoice', compact('booking'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetVariant(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'zone_id' => 'required|uuid',
            'service_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 200);
        }

        $variations = Variation::where('service_id', $request['service_id'])
            ->where('zone_id', $request['zone_id'])
            ->where('price', '>', 0)
            ->get();
        return response()->json(response_formatter(DEFAULT_200, $variations, null), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetServiceInfo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'zone_id' => 'required|uuid',
            'service_id' => 'required|uuid',
            'variant_key' => 'required',
            'quantity' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 200);
        }

        $service = Service::active()
            ->with(['category.category_discount', 'category.campaign_discount', 'service_discount'])
            ->where('id', $request['service_id'])
            ->with(['variations' => fn($query) => $query->where('variant_key', $request['variant_key'])->where('zone_id', $request['zone_id'])])
            ->first();

        $quantity = $request['quantity'];
        $variation_price = $service?->variations[0]?->price;

        $basic_discount = basic_discount_calculation($service, $variation_price * $quantity);
        $campaign_discount = campaign_discount_calculation($service, $variation_price * $quantity);
        $subtotal = round($variation_price * $quantity, 2);

        $applicable_discount = ($campaign_discount >= $basic_discount) ? $campaign_discount : $basic_discount;

        $tax = round((($variation_price * $quantity - $applicable_discount) * $service['tax']) / 100, 2);

        $basic_discount = $basic_discount > $campaign_discount ? $basic_discount : 0;
        $campaign_discount = $campaign_discount >= $basic_discount ? $campaign_discount : 0;

        $data = collect([
            'service_id' => $service->id,
            'service_name' => $service->name,
            'variant_key' => $service?->variations[0]?->variant_key,
            'quantity' => $request['quantity'],
            'service_cost' => $variation_price,
            'total_discount_amount' => $basic_discount + $campaign_discount,
            'coupon_code' => null,
            'tax_amount' => round($tax, 2),
            'total_cost' => round($subtotal - $basic_discount - $campaign_discount + $tax, 2),
            'zone_id' => $request['zone_id']
        ]);

        return response()->json([
            'view' => view('bookingmodule::admin.booking.partials.details.table-row', compact('data'))->render()
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException|AuthorizationException
     */
    public function updateBookingService(Request $request): RedirectResponse
    {

        $this->authorize('booking_edit');

        Validator::make($request->all(), [
            'qty' => 'required|array',
            'qty.*' => 'int',
            'service_ids' => 'required|array',
            'service_ids.*' => 'uuid',
            'variant_keys' => 'required|array',
            'variant_keys.*' => 'string',
            'zone_id' => 'required|uuid',
            'booking_id' => 'required|uuid',
        ])->validate();

        $service_info = [];
        foreach ($request['service_ids'] as $key => $service_id) {
            $variant_key = $request['variant_keys'][$key] ?? null;
            $quantity = $request['qty'][$key] ?? 0;

            $service_info[] = [
                'service_id' => $service_id,
                'variant_key' => $variant_key,
                'quantity' => $quantity,
            ];
        }
        $request->merge(['service_info' => collect($service_info)]);

        $existing_services = $this->bookingDetails->where('booking_id', $request['booking_id'])->get();
        foreach ($existing_services as $item) {
            if (!$request['service_info']->where('service_id', $item->service_id)->where('variant_key', $item->variant_key)->first()) {
                $request['service_info']->push([
                    'service_id' => $item->service_id,
                    'variant_key' => $item->variant_key,
                    'quantity' => 0,
                ]);
            }
        }

        foreach ($request['service_info'] as $key => $item) {
            $existing_service = $this->bookingDetails
                ->where('booking_id', $request['booking_id'])
                ->where('service_id', $item['service_id'])
                ->where('variant_key', $item['variant_key'])
                ->first();

            if (!$existing_service) {
                $request['service_id'] = $item['service_id'];
                $request['variant_key'] = $item['variant_key'];
                $request['quantity'] = $item['quantity'];
                $this->addNewBookingService($request);

            } else if ($existing_service && $item['quantity'] == 0) {
                $request['service_id'] = $item['service_id'];
                $request['variant_key'] = $item['variant_key'];
                $request['quantity'] = $item['quantity'];

                $this->remove_service_from_booking($request);

            } else if ($existing_service && $existing_service->quantity < $item['quantity']) {
                $request['service_id'] = $item['service_id'];
                $request['variant_key'] = $item['variant_key'];
                $request['old_quantity'] = $existing_service->quantity;
                $request['new_quantity'] = (int)$item['quantity'];
                $this->increase_service_quantity_from_booking($request);

            } else if ($existing_service && $existing_service->quantity > $item['quantity']) {
                $request['service_id'] = $item['service_id'];
                $request['variant_key'] = $item['variant_key'];
                $request['old_quantity'] = $existing_service->quantity;
                $request['new_quantity'] = (int)$item['quantity'];

                $this->decrease_service_quantity_from_booking($request);

            }
        }

        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function verifyOfflinePayment(Request $request): JsonResponse
    {

        $this->authorize('booking_can_manage_status');

        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 200);
        }

        $booking = $this->booking->find($request['booking_id']);
        $booking->is_paid = 1;
        $booking->save();

        $user = $booking->customer;
        $offline = isNotificationActive(null, 'booking', 'notification', 'user');
        $title = get_push_notification_message('offline_payment_approved', 'customer_notification', $user?->current_language_key);
        if ($user?->fcm_token && $title && $offline) {
            device_notification($user?->fcm_token, $title, null, null, $booking->id, 'booking', null, $user->id);
        }

        placeBookingTransactionForDigitalPayment($booking);

        return response()->json(response_formatter(DEFAULT_UPDATE_200, null), 200);
    }
}
