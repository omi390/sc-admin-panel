<?php

namespace Modules\BookingModule\Http\Controllers\Web\Provider;

use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Entities\BookingDetail;
use Modules\BookingModule\Entities\BookingScheduleHistory;
use Illuminate\Http\RedirectResponse;
use Modules\BookingModule\Entities\BookingStatusHistory;
use Modules\BookingModule\Http\Traits\BookingTrait;
use Modules\BusinessSettingsModule\Entities\PackageSubscriber;
use Modules\CategoryManagement\Entities\Category;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ProviderManagement\Entities\SubscribedService;
use Modules\ServiceManagement\Entities\Service;
use Modules\ServiceManagement\Entities\Variation;
use Modules\UserManagement\Entities\Serviceman;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Entities\UserAddress;
use Modules\ZoneManagement\Entities\Zone;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingController extends Controller
{

    private Booking $booking;
    private BookingDetail $bookingDetail;
    private BookingStatusHistory $bookingStatusHistory;
    private BookingScheduleHistory $bookingScheduleHistory;
    private $subscribed_sub_categories;
    private Category $category;
    private Zone $zone;
    private Serviceman $serviceman;
    private Provider $provider;
    private SubscribedService $subscribed_service;
    private User $user;
    private UserAddress $userAddress;

    use BookingTrait;

    public function __construct(Booking $booking, BookingStatusHistory $bookingStatusHistory, BookingScheduleHistory $bookingScheduleHistory, SubscribedService $subscribedService, Category $category, Zone $zone, Serviceman $serviceman, Provider $provider, SubscribedService $subscribed_service, User $user, UserAddress $userAddress, BookingDetail $bookingDetail)
    {
        $this->booking = $booking;
        $this->bookingStatusHistory = $bookingStatusHistory;
        $this->bookingScheduleHistory = $bookingScheduleHistory;
        $this->category = $category;
        $this->zone = $zone;
        $this->serviceman = $serviceman;
        $this->provider = $provider;
        $this->subscribed_service = $subscribed_service;
        $this->user = $user;
        $this->userAddress = $userAddress;
        $this->bookingDetail = $bookingDetail;

        try {
            $this->subscribed_sub_categories = $subscribedService->where(['provider_id' => auth('api')->user()->provider->id])
                ->where(['is_subscribed' => 1])->pluck('sub_category_id')->toArray();
        } catch (\Exception $exception) {
            $this->subscribed_sub_categories = $subscribedService->pluck('sub_category_id')->toArray();
        }
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request): Renderable
    {
        $request->validate([
            'booking_status' => 'in:' . implode(',', array_column(BOOKING_STATUSES, 'key')) . ',all',
        ]);


        $queryParams = $request->only(['category_ids', 'sub_category_ids', 'start_date', 'end_date', 'search']);
        $filterCounter = collect($queryParams)->filter()->count();
        $bookingStatus = $queryParams['booking_status'] = $request->input('booking_status', 'pending');
        $queryParams['booking_type'] = $request->input('booking_type', '');
        if (empty($queryParams['start_date'])) {
            $queryParams['start_date'] = null;
        }
        if (empty($queryParams['end_date'])) {
            $queryParams['end_date'] = null;
        }

        $maxBookingAmount = business_config('max_booking_amount', 'booking_setup')->live_values;
        $providerId = $request->user()?->provider?->id;
        $packageSubscriber = PackageSubscriber::where('provider_id', $providerId)->first();
        $endDate = optional($packageSubscriber)->package_end_date;
        $canceled = optional($packageSubscriber)->is_canceled;
        $packageEndDate = $endDate ? Carbon::parse($endDate)->endOfDay() : null;
        $currentDate = Carbon::now()->subDay();
        $isPackageEnded = $packageEndDate ? $currentDate->diffInDays($packageEndDate, false) : null;
        $scheduleBookingEligibility = nextBookingEligibility($providerId);

        $bookings = $this->booking->with(['customer'])
            ->when(!in_array($request['booking_status'], ['pending', 'all']), function ($query) use ($request, $maxBookingAmount) {
                $query->ofBookingStatus($request['booking_status'])
                    ->where('provider_id', $request->user()->provider->id)
                    ->when($request['booking_status'] == 'accepted', function ($query) use ($request, $maxBookingAmount) {
                        $query->providerAcceptedBookings($request->user()->provider->id, $maxBookingAmount);
                    });
            })
            ->when($request['booking_status'] == 'pending', function ($query) use ($packageSubscriber, $canceled, $scheduleBookingEligibility, $isPackageEnded, $request, $maxBookingAmount) {
                if ($packageSubscriber) {
                    if ($isPackageEnded > 0 && $scheduleBookingEligibility && !$canceled) {
                        $query->providerPendingBookings($request->user()->provider, $maxBookingAmount);
                    }else{
                        $query->whereRaw('1 = 0');
                    }
                } else {
                    $query->providerPendingBookings($request->user()->provider, $maxBookingAmount);
                }
            })
            ->search($request['string'], ['readable_id'])
            ->filterByDateRange($request['start_date'], $request['end_date'])
            ->filterBySubcategoryIds($request['sub_category_ids'])
            ->filterByCategoryIds($request['category_ids'])
            ->latest()->paginate(pagination_limit())->appends($queryParams);

        if ($bookingStatus == 'pending') {
            $this->booking
                ->whereIn('sub_category_id', $this->subscribed_sub_categories)
                ->where('zone_id', $request->user()->provider->zone_id)
                ->where('is_checked', 0)
                ->update(['is_checked' => 1]);
        }


        $categories = $this->category->select('id', 'parent_id', 'name')->where('position', 1)->get();
        $subCategories = $this->category->select('id', 'parent_id', 'name')->where('position', 2)->get();

        return view('bookingmodule::provider.booking.list', compact('bookings', 'categories', 'subCategories', 'queryParams', 'filterCounter'));
    }

    /**
     * Display a listing of the resource.
     * @param $id
     * @return void
     */
    public function checkBooking($id): void
    {
        $this->booking->where('id', $id)->whereIn('sub_category_id', $this->subscribed_sub_categories)
            ->where('is_checked', 0)->update(['is_checked' => 1]);
    }

    /**
     * Display a listing of the resource.
     * @param $id
     * @param Request $request
     * @return Application|Factory|View|RedirectResponse
     */
    public function details($id, Request $request): View|Factory|RedirectResponse|Application
    {
        Validator::make($request->all(), [
            'web_page' => 'required|in:details,status',
        ]);

        $webPage = $request->has('web_page') ? $request['web_page'] : 'details';
        $booking = $this->booking->with(['detail.service' => fn($query) => $query->withTrashed(), 'detail.service.category', 'detail.service.subCategory', 'detail.variation', 'customer', 'provider', 'service_address', 'serviceman', 'service_address', 'status_histories.user'])->find($id);

        if ($booking['booking_status'] != 'pending' && $booking['provider_id'] != $request->user()->provider->id) {
            Toastr::error(translate(ACCESS_DENIED['message']));
            return redirect(route('provider.booking.list', ['booking_status' => 'accepted']));
        }

        if ($request->web_page == 'details') {
            $servicemen = $this->serviceman->with(['user'])
                ->whereHas('user', function ($q) {
                    $q->ofStatus(1);
                })
                ->where('provider_id', $this->provider->where('user_id', $request->user()->id)->first()->id)
                ->latest()
                ->get();

            $customerAddresses = $this->userAddress->where(['user_id' => $booking?->customer?->id])->get();

            $category = $booking?->detail?->first()?->service?->category;
            $subCategory = $booking?->detail?->first()?->service?->subCategory;
            $services = Service::select('id', 'name')->where('category_id', $category->id)->where('sub_category_id', $subCategory->id)->get();

            $customerAddress = $this->userAddress->find($booking['service_address_id']);
            $zones = Zone::ofStatus(1)->get();

            return view('bookingmodule::provider.booking.details', compact('booking', 'servicemen', 'webPage', 'customerAddresses', 'category', 'subCategory', 'services', 'customerAddress', 'zones'));

        } elseif ($request->web_page == 'status') {
            $customerAddresses = $this->userAddress->where(['user_id' => $booking?->customer?->id])->get();

            $category = $booking?->detail?->first()?->service?->category;
            $subCategory = $booking?->detail?->first()?->service?->subCategory;
            $services = Service::select('id', 'name')->where('category_id', $category->id)->where('sub_category_id', $subCategory->id)->get();

            $customerAddress = $this->userAddress->find($booking['service_address_id']);
            $zones = Zone::ofStatus(1)->get();
            return view('bookingmodule::provider.booking.status', compact('booking', 'webPage', 'customerAddress', 'category', 'subCategory', 'zones', 'services'));
        }
    }

    /**
     * Display a listing of the resource.
     * @param $bookingId
     * @param Request $request
     * @return JsonResponse
     */
    public function statusUpdate($bookingId, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'booking_status' => 'required|in:' . implode(',', array_column(BOOKING_STATUSES, 'key')),
            'otp_field' => ((business_config('booking_otp', 'booking_setup'))->live_values == 1 && $request->booking_status == 'completed') ? 'required' : 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $booking = $this->booking->where('id', $bookingId)->where(function ($query) use ($request) {
            return $query->where('provider_id', $request->user()->provider->id)->orWhereNull('provider_id');
        })->first();

        $provider = $request->user()->provider;

        if (isset($booking)) {

            if ($booking->booking_offline_payments->isNotEmpty()) {
                if ($booking->is_paid == 0 && in_array($request->booking_status, ['ongoing', 'completed'])) {
                    return response()->json(response_formatter(UPDATE_FAILED_FOR_OFFLINE_PAYMENT_VERIFICATION_200), 200);
                }
            }

            if ($request->booking_status == 'completed' && (business_config('booking_otp', 'booking_setup'))?->live_values == 1) {

                $otp_number = implode('', $request->otp_field);
                if ($booking->booking_otp != $otp_number) {
                    return response()->json(response_formatter(OTP_VERIFICATION_FAIL_403), 200);
                }
            }

            if ($request->booking_status == 'accepted') {
                if ($provider?->is_suspended == 1 && business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values) {
                    return response()->json(DEFAULT_SUSPEND_200, 200);
                }

                $nextBookingEligibility = nextBookingEligibility($request->user()->provider->id);
                if (!$nextBookingEligibility){
                    return response()->json(BOOKING_LIMIT_END, 200);
                }
            }

            if($booking->booking_status == 'canceled'){
                return response()->json(response_formatter(BOOKING_ALREADY_CANCELED_200), 200);
            }

            if($booking->booking_status == 'ongoing' && $request['booking_status'] == 'canceled'){
                return response()->json(BOOKING_ALREADY_ONGOING, 200);
            }

            if($booking->booking_status == 'completed' && $request['booking_status'] == 'canceled'){
                return response()->json(BOOKING_ALREADY_COMPLETED, 200);
            }

            if($booking->payment_method != 'cash_after_service' && $request['booking_status'] == 'canceled' && $booking->additional_charge > 0){
                return response()->json(BOOKING_ALREADY_EDITED, 200);
            }

            $booking->booking_status = $request['booking_status'];
            $booking->provider_id = $request->user()->provider->id;

            $bookingStatusHistory = $this->bookingStatusHistory;
            $bookingStatusHistory->booking_id = $bookingId;
            $bookingStatusHistory->changed_by = $request->user()->id;
            $bookingStatusHistory->booking_status = $request['booking_status'];
            if ($booking->isDirty('booking_status')) {
                DB::transaction(function () use ($bookingStatusHistory, $booking) {
                    $booking->save();
                    $bookingStatusHistory->save();
                });

                self::checkBooking($booking->id);

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
     */
    public function evidencePhotosUpload($bookingId, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'evidence_photos' => 'nullable|array',
        ]);

        $booking = $this->booking->where('id', $bookingId)->where(function ($query) use ($request) {
            return $query->where('provider_id', $request->user()->provider->id)->orWhereNull('provider_id');
        })->first();
        if (isset($booking)) {

            $evidence_photos = [];

            if ($booking->evidence_photos != 'null'){
                foreach ($booking->evidence_photos ?? [] as $image) {
                    $img = is_array($image) ? $image['image'] : $image;
                    file_remover('booking/evidence/', $img);
                }

                $booking->evidence_photos = [];
                $booking->save();
            }

            $booking->evidence_photos = [];

            if ($request->has('evidence_photos')) {
                foreach ($request->evidence_photos as $image) {
                    $imageName = file_uploader('booking/evidence/', 'png', $image);
                    $evidence_photos[] = ['image'=>$imageName, 'storage'=> getDisk()];
                }
            }

            $booking->evidence_photos = $evidence_photos;
            $booking->save();

            return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }

    /**
     * Show the specified resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $booking = $this->booking
            ->with(['customer'])
            ->where('id', $request['booking_id'])
            ->where(function ($query) use ($request) {
                return $query->where('provider_id', $request->user()->provider->id);
            })
            ->first();

        if (!isset($booking)) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        $fcmToken = $booking?->customer?->fcm_token;
        $title = translate('Your booking verification OTP is') . ' ' . $booking->booking_otp;

        if ($fcmToken) {
            device_notification($fcmToken, $title, null, null, $booking->id, 'booking', null, $booking?->customer?->id);
            return response()->json(response_formatter(NOTIFICATION_SEND_SUCCESSFULLY_200), 200);

        } else {
            return response()->json(response_formatter(NOTIFICATION_SEND_FAILED_200), 200);
        }
    }

    /**
     * Display a listing of the resource.
     * @param $bookingId
     * @param Request $request
     * @return JsonResponse
     */
    public function paymentUpdate($bookingId, Request $request): JsonResponse
    {
        Validator::make($request->all(), [
            'payment_status' => 'required|in:1,0',
        ]);

        $booking = $this->booking->where('id', $bookingId)->where(function ($query) use ($request) {
            return $query->where('provider_id', $request->user()->provider->id)->orWhereNull('provider_id');
        })->first();

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
     */
    public function scheduleUpdate($bookingId, Request $request): JsonResponse
    {
        Validator::make($request->all(), [
            'service_schedule' => 'required',
        ]);

        $booking = $this->booking->where('id', $bookingId)->where(function ($query) use ($request) {
            return $query->where('provider_id', $request->user()->provider->id)->orWhereNull('provider_id');
        })->first();

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
     */
    public function servicemanUpdate($bookingId, Request $request): JsonResponse
    {
        Validator::make($request->all(), [
            'serviceman_id' => 'required|uuid',
        ]);

        $booking = $this->booking->where('id', $bookingId)->where(function ($query) use ($request) {
            return $query->where('provider_id', $request->user()->provider->id)->orWhereNull('provider_id');
        })->first();

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
     * @param $bookingId
     * @param Request $request
     * @return RedirectResponse
     */
    public function serviceAddressUpdate($bookingId, Request $request): RedirectResponse
    {
        Validator::make($request->all(), [
            'service_address_id' => 'required',
        ]);

        $booking = $this->booking->where('id', $bookingId)->first();

        if (isset($booking)) {
            $booking->service_address_id = $request->service_address_id;

            if ($booking->isDirty('service_address_id')) {
                $booking->save();

                Toastr::success(translate(DEFAULT_STATUS_UPDATE_200['message']));
                return back();
            }
            Toastr::info(translate(NO_CHANGES_FOUND['message']));
            return back();
        }
        Toastr::success(translate(DEFAULT_204['message']));
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
        $request->validate([
            'booking_status' => 'in:' . implode(',', array_column(BOOKING_STATUSES, 'key')) . ',all',
        ]);

        $queryParams = $request->only(['category_ids', 'sub_category_ids', 'start_date', 'end_date', 'search']);
        $filterCounter = collect($queryParams)->filter()->count();
        $bookingStatus = $queryParams['booking_status'] = $request->input('booking_status', 'pending');
        $queryParams['booking_type'] = $request->input('booking_type', '');
        if (empty($queryParams['start_date'])) {
            $queryParams['start_date'] = null;
        }
        if (empty($queryParams['end_date'])) {
            $queryParams['end_date'] = null;
        }

        $maxBookingAmount = business_config('max_booking_amount', 'booking_setup')->live_values;
        $items = $this->booking->with(['customer'])
            ->when(!in_array($request['booking_status'], ['pending', 'all']), function ($query) use ($request, $maxBookingAmount) {
                $query->ofBookingStatus($request['booking_status'])
                    ->where('provider_id', $request->user()->provider->id)
                    ->when($request['booking_status'] == 'accepted', function ($query) use ($request, $maxBookingAmount) {
                        $query->providerAcceptedBookings($request->user()->provider->id, $maxBookingAmount);
                    });
            })
            ->when($request['booking_status'] == 'pending', function ($query) use ($request, $maxBookingAmount) {
                $query->providerPendingBookings($request->user()->provider, $maxBookingAmount);
            })
            ->search($request['string'], ['readable_id'])
            ->filterByDateRange($request['start_date'], $request['end_date'])
            ->filterBySubcategoryIds($request['sub_category_ids'])
            ->filterByCategoryIds($request['category_ids'])
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
        return view('bookingmodule::provider.booking.invoice', compact('booking'));
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
        $variationPrice = $service?->variations[0]?->price;

        $basicDiscount = basic_discount_calculation($service, $variationPrice * $quantity);
        $campaignDiscount = campaign_discount_calculation($service, $variationPrice * $quantity);
        $subTotal = round($variationPrice * $quantity, 2);

        $applicableDiscount = ($campaignDiscount >= $basicDiscount) ? $campaignDiscount : $basicDiscount;

        $tax = round((($variationPrice * $quantity - $applicableDiscount) * $service['tax']) / 100, 2);

        $basicDiscount = $basicDiscount > $campaignDiscount ? $basicDiscount : 0;
        $campaignDiscount = $campaignDiscount >= $basicDiscount ? $campaignDiscount : 0;

        $data = collect([
            'service_id' => $service->id,
            'service_name' => $service->name,
            'variant_key' => $service?->variations[0]?->variant_key,
            'quantity' => $request['quantity'],
            'service_cost' => $variationPrice,
            'total_discount_amount' => $basicDiscount + $campaignDiscount,
            'coupon_code' => null,
            'tax_amount' => round($tax, 2),
            'total_cost' => round($subTotal - $basicDiscount - $campaignDiscount + $tax, 2),
            'zone_id' => $request['zone_id']
        ]);

        return response()->json([
            'view' => view('bookingmodule::admin.booking.partials.details.table-row', compact('data'))->render()
        ]);
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
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function updateBookingService(Request $request): RedirectResponse
    {
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

        $existingServices = $this->bookingDetail->where('booking_id', $request['booking_id'])->get();
        foreach ($existingServices as $item) {
            if (!$request['service_info']->where('service_id', $item->service_id)->where('variant_key', $item->variant_key)->first()) {
                $request['service_info']->push([
                    'service_id' => $item->service_id,
                    'variant_key' => $item->variant_key,
                    'quantity' => 0,
                ]);
            }
        }

        foreach ($request['service_info'] as $key => $item) {
            $existingService = $this->bookingDetail
                ->where('booking_id', $request['booking_id'])
                ->where('service_id', $item['service_id'])
                ->where('variant_key', $item['variant_key'])
                ->first();

            if (!$existingService) {
                $request['service_id'] = $item['service_id'];
                $request['variant_key'] = $item['variant_key'];
                $request['quantity'] = $item['quantity'];
                $this->addNewBookingService($request);

            } else if ($existingService && $item['quantity'] == 0) {
                $request['service_id'] = $item['service_id'];
                $request['variant_key'] = $item['variant_key'];
                $request['quantity'] = $item['quantity'];

                $this->remove_service_from_booking($request);

            } else if ($existingService && $existingService->quantity < $item['quantity']) {
                $request['service_id'] = $item['service_id'];
                $request['variant_key'] = $item['variant_key'];
                $request['old_quantity'] = $existingService->quantity;
                $request['new_quantity'] = (int)$item['quantity'];
                $this->increase_service_quantity_from_booking($request);

            } else if ($existingService && $existingService->quantity > $item['quantity']) {
                $request['service_id'] = $item['service_id'];
                $request['variant_key'] = $item['variant_key'];
                $request['old_quantity'] = $existingService->quantity;
                $request['new_quantity'] = (int)$item['quantity'];

                $this->decrease_service_quantity_from_booking($request);

            }
        }

        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();
    }
}
