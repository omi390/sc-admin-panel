<?php

namespace Modules\BookingModule\Http\Controllers\Api\V1\Provider;

use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Entities\BookingDetail;
use Modules\BookingModule\Entities\BookingScheduleHistory;
use Modules\BookingModule\Entities\BookingStatusHistory;
use Modules\BookingModule\Http\Traits\BookingTrait;
use Modules\CartModule\Entities\Cart;
use Modules\ProviderManagement\Entities\SubscribedService;
use Modules\ServiceManagement\Entities\Service;
use Rap2hpoutre\FastExcel\FastExcel;

class BookingController extends Controller
{

    private Booking $booking;
    private BookingStatusHistory $bookingStatusHistory;
    private BookingScheduleHistory $bookingScheduleHistory;
    private $subscribedSubCategories;
    private BookingDetail $bookingDetail;
    use BookingTrait;

    public function __construct(Booking $booking, BookingStatusHistory $bookingStatusHistory, BookingScheduleHistory $bookingScheduleHistory, SubscribedService $subscribedService, BookingDetail $bookingDetail)
    {
        $this->booking = $booking;
        $this->bookingStatusHistory = $bookingStatusHistory;
        $this->bookingScheduleHistory = $bookingScheduleHistory;
        $this->bookingDetail = $bookingDetail;
        try {
            $this->subscribedSubCategories = $subscribedService->where(['provider_id' => auth('api')->user()->provider->id])
                ->where(['is_subscribed' => 1])->pluck('sub_category_id')->toArray();
        } catch (\Exception $exception) {
            $this->subscribedSubCategories = $subscribedService->pluck('sub_category_id')->toArray();
        }
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'booking_status' => 'required|in:' . implode(',', array_column(BOOKING_STATUSES, 'key')) . ',all',
            'from_date' => 'date',
            'to_date' => 'date',
            'sub_category_ids' => 'array',
            'sub_category_ids.*' => 'uuid',
            'category_ids' => 'array',
            'category_ids.*' => 'uuid'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $maxBookingAmount = business_config('max_booking_amount', 'booking_setup')->live_values;

        //status wise bookings count
        $status_wise_bookings_count = $this->booking
            ->where('provider_id', $request->user()->provider->id)
            ->select('booking_status', DB::raw('count(*) as total'))
            ->groupBy('booking_status')
            ->get();
        $bookings_count = collect(BOOKING_STATUSES)->mapWithKeys(function ($item) use ($status_wise_bookings_count) {
            $total = $status_wise_bookings_count->where('booking_status', $item['key'])->first();
            return [$item['key'] => $total ? $total->total : 0];
        })->toArray();
        $bookings_count['pending'] = $this->booking->providerPendingBookings($request->user()->provider, $maxBookingAmount)->count();
        $bookings_count['accepted'] = $this->booking->providerAcceptedBookings($request->user()->provider->id, $maxBookingAmount)->count();

        //bookings list
        $bookings = $this->booking
            ->with(['customer', 'subCategory:id,name', 'booking_offline_payments' => function ($query) {
                    $query->first() ?? [];
            }])
            ->when(!in_array($request['booking_status'], ['pending', 'all']), function ($query) use ($request, $maxBookingAmount) {
                $query->ofBookingStatus($request['booking_status'])
                    ->where('provider_id', $request->user()->provider->id)
                    ->when($request['booking_status'] == 'accepted', function ($query) use ($request, $maxBookingAmount) {
                        $query->providerAcceptedBookings($request->user()->provider->id, $maxBookingAmount);
                    });
            })
            ->when($request['booking_status'] == 'all', function ($query) use ($request, $maxBookingAmount) {
                $query->where('provider_id', $request->user()->provider->id)
                    ->orWhere(function ($query) use ($request, $maxBookingAmount) {
                        $query->providerPendingBookings($request->user()->provider, $maxBookingAmount);
                    });
            })
            ->when($request['booking_status'] == 'pending', function ($query) use ($request, $maxBookingAmount) {
                $query->providerPendingBookings($request->user()->provider, $maxBookingAmount);
            })
            ->search(base64_decode($request['string']), ['readable_id'])
            ->filterByDateRange($request['from_date'], $request['to_date'])
            ->filterBySubcategoryIds($request['sub_category_ids'])
            ->filterByCategoryIds($request['category_ids'])
            ->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])
            ->withPath('');

        return response()->json(response_formatter(DEFAULT_200, [
            'bookings_count' => $bookings_count,
            'bookings' => $bookings,
        ]), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \OpenSpout\Common\Exception\IOException
     * @throws \OpenSpout\Common\Exception\InvalidArgumentException
     * @throws \OpenSpout\Common\Exception\UnsupportedTypeException
     * @throws \OpenSpout\Writer\Exception\WriterNotOpenedException
     */
    public function download(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'booking_status' => 'required|in:all,' . implode(',', array_column(BOOKING_STATUSES, 'key')),
            'zone_ids' => 'array',
            'from_date' => 'date',
            'to_date' => 'date',
            'sub_category_ids' => 'array',
            'sub_category_ids.*' => 'uuid',
            'category_ids' => 'array',
            'category_ids.*' => 'uuid'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $bookings = $this->booking->where('provider_id', $request->user()->id)
            ->when($request['booking_status'] != 'all', function ($query) use ($request) {
                $query->ofBookingStatus($request['booking_status']);
            })->when($request->has('zone_ids'), function ($query) use ($request) {
                $query->whereIn('zone_id', $request['zone_ids']);
            })->when($request->has('from_date') && $request->has('to_date'), function ($query) use ($request) {
                $query->whereBetween('created_at', [$request['from_date'], $request['to_date']]);
            })->when($request->has('sub_category_ids'), function ($query) use ($request) {
                $query->whereIn('sub_category_id', [$request['sub_category_ids']]);
            })->when($request->has('category_ids'), function ($query) use ($request) {
                $query->whereIn('category_id', [$request['category_ids']]);
            })
            ->latest()->get();

        if (!Storage::disk('public')->exists('/download')) {
            Storage::disk('public')->makeDirectory('/download');
        }
        return response()->json(response_formatter(DEFAULT_200, ['download_link' => (new FastExcel($bookings))->export('storage/app/public/download/bookings-' . date('Y-m-d') . '-' . rand(1000, 99999) . '.xlsx')]), 200);
    }

    /**
     * Show the specified resource.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $booking = $this->booking->with([
            'detail.service', 'schedule_histories.user', 'status_histories.user', 'service_address', 'customer', 'provider', 'zone', 'serviceman.user', 'booking_partial_payments', 'booking_offline_payments'
        ])->where(function ($query) use ($request) {
            return $query->where('provider_id', $request->user()->provider->id)->orWhereNull('provider_id');
        })->where(['id' => $id])->first();

        if (isset($booking)) {
            $offlinePayment = $booking->booking_offline_payments?->first();
            unset($booking->booking_offline_payments);

            if ($offlinePayment) {
                $booking->booking_offline_payment_method = $offlinePayment->method_name;
                $booking->booking_offline_payment = collect($offlinePayment->customer_information)->map(function ($value, $key) {
                    return ["key" => $key, "value" => $value];
                })->values()->all();
            }

            return response()->json(response_formatter(DEFAULT_200, $booking), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }

    /**
     * Show the specified resource.
     * @param Request $request
     * @param string $bookingId
     * @return JsonResponse
     */
    public function requestAccept(Request $request, string $bookingId): JsonResponse
    {
        $booking = $this->booking->where('id', $bookingId)->whereNull('provider_id')->first();
        if (isset($booking)) {

            $provider = $request->user()->provider;

            if ($provider?->is_suspended == 1 && business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values) {
                return response()->json(DEFAULT_SUSPEND_200, 404);
            }

            if ($booking->booking_status == 'canceled') {
                return response()->json(response_formatter(BOOKING_ALREADY_CANCELED_200), 200);
            }

            $nextBookingEligibility = nextBookingEligibility($provider->id);
            if (!$nextBookingEligibility){
                return response()->json(response_formatter(BOOKING_LIMIT_END), 200);
            }

            $booking->provider_id = $request->user()->provider->id;
            $booking->booking_status = 'accepted';

            $bookingStatusHistory = $this->bookingStatusHistory;
            $bookingStatusHistory->booking_id = $bookingId;
            $bookingStatusHistory->changed_by = $request->user()->id;
            $bookingStatusHistory->booking_status = 'accepted';

            DB::transaction(function () use ($bookingStatusHistory, $booking) {
                $booking->save();
                $bookingStatusHistory->save();
            });

            return response()->json(response_formatter(BOOKING_STATUS_UPDATE_SUCCESS_200), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }

    /**
     * Show the specified resource.
     * @param Request $request
     * @param string $bookingId
     * @return JsonResponse
     */
    public function statusUpdate(Request $request, string $bookingId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'booking_status' => 'required|in:' . implode(',', array_column(BOOKING_STATUSES, 'key')),
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $booking = $this->booking->where('id', $bookingId)->where(function ($query) use ($request) {
            return $query->where('provider_id', $request->user()->provider->id)->orWhereNull('provider_id');
        })->first();

        if (isset($booking)) {

            if ($booking->booking_offline_payments->isNotEmpty()) {
                if ($booking->is_paid == 0 && in_array($request->booking_status, ['ongoing', 'completed'])) {
                    return response()->json(response_formatter(UPDATE_FAILED_FOR_OFFLINE_PAYMENT_VERIFICATION_200), 200);
                }
            }

            $evidence_photos = [];
            if ($request['booking_status'] == 'completed') {
                if (business_config('booking_otp', 'booking_setup')?->live_values == 1 && $booking->booking_otp != $request['booking_otp']) {
                    return response()->json(response_formatter(OTP_VERIFICATION_FAIL_403), 200);
                }

                if ($request->has('evidence_photos')) {
                    foreach ($request->evidence_photos as $image) {
                        $imageName = file_uploader('booking/evidence/', 'png', $image);
                        $evidence_photos[] = ['image'=>$imageName, 'storage'=> getDisk()];
                    }
                }

                if ($booking->payment_method == 'offline_payment' && !$booking->is_paid) {
                    return response()->json(response_formatter(UPDATE_FAILED_FOR_OFFLINE_PAYMENT_VERIFICATION_200), 200);
                }
            }

            if ($booking->booking_status == 'canceled') {
                return response()->json(response_formatter(BOOKING_ALREADY_CANCELED_200), 200);
            }

            if ($booking->booking_status == 'ongoing' && $request['booking_status'] == 'canceled') {
                return response()->json(response_formatter(BOOKING_ALREADY_ONGOING), 200);
            }

            if ($booking->booking_status == 'completed' && $request['booking_status'] == 'canceled') {
                return response()->json(response_formatter(BOOKING_ALREADY_COMPLETED), 200);
            }

            if ($booking->payment_method != 'cash_after_service' && $request['booking_status'] == 'canceled' && $booking->additional_charge > 0) {
                return response()->json(response_formatter(BOOKING_ALREADY_EDITED), 200);
            }

            $booking->booking_status = $request['booking_status'];
            $booking->evidence_photos = $evidence_photos;

            $bookingStatusHistory = $this->bookingStatusHistory;
            $bookingStatusHistory->booking_id = $bookingId;
            $bookingStatusHistory->changed_by = $request->user()->id;
            $bookingStatusHistory->booking_status = $request['booking_status'];

            if ($booking->isDirty('booking_status')) {
                DB::transaction(function () use ($bookingStatusHistory, $booking) {
                    $booking->save();
                    $bookingStatusHistory->save();
                });

                return response()->json(response_formatter(BOOKING_STATUS_UPDATE_SUCCESS_200, $booking), 200);
            }
            return response()->json(response_formatter(NO_CHANGES_FOUND), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }


    /**
     * Show the specified resource.
     * @param Request $request
     * @param string $bookingId
     * @return JsonResponse
     */
    public function assignServiceman(Request $request, string $bookingId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'serviceman_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $booking = $this->booking->where('id', $bookingId)->where('provider_id', $request->user()->provider->id)->first();
        if (isset($booking)) {
            $booking->serviceman_id = $request['serviceman_id'];
            $booking->save();
            return response()->json(response_formatter(SERVICEMAN_ASSIGN_SUCCESS_200, $booking), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }


    /**
     * Show the specified resource.
     * @param Request $request
     * @param string $bookingId
     * @return JsonResponse
     */
    public function scheduleUpdate(Request $request, string $bookingId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'schedule' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $booking = $this->booking->where('id', $bookingId)->where(function ($query) use ($request) {
            return $query->where('provider_id', $request->user()->provider->id);
        })->first();

        if (isset($booking)) {
            $booking->service_schedule = $request['schedule'];

            $bookingScheduleHistory = $this->bookingScheduleHistory;
            $bookingScheduleHistory->booking_id = $bookingId;
            $bookingScheduleHistory->changed_by = $request->user()->id;
            $bookingScheduleHistory->schedule = $request['schedule'];

            DB::transaction(function () use ($bookingScheduleHistory, $booking) {
                $booking->save();
                $bookingScheduleHistory->save();
            });

            return response()->json(response_formatter(SERVICE_SCHEDULE_UPDATE_200, $booking), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }

    /**
     * Show the specified resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function notificationSend(Request $request): JsonResponse
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
        $title = get_push_notification_message('otp', 'customer_notification', $booking?->customer?->current_language_key) . ' ' . $booking->booking_otp;

        if ($fcmToken) {
            device_notification($fcmToken, $title, null, null, $booking->id, 'booking', null, $booking?->customer?->id);
            return response()->json(response_formatter(NOTIFICATION_SEND_SUCCESSFULLY_200), 200);

        } else {
            return response()->json(response_formatter(NOTIFICATION_SEND_FAILED_200), 200);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getServiceInfo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'zone_id' => 'required|uuid',
            'service_info' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }


        $data = [];


        foreach (json_decode($request['service_info'], true) as $item) {
            $service = Service::active()
                ->where('id', $item['service_id'])
                ->with(['category.category_discount', 'category.campaign_discount', 'service_discount'])
                ->with(['variations' => fn($query) => $query->where('variant_key', $item['variant_key'])->where('zone_id', $request['zone_id'])])
                ->first();

            if (!isset($service)) return response()->json(response_formatter(DEFAULT_404, $data), 404);

            $quantity = $item['quantity'];
            $variationPrice = $service?->variations[0]?->price;

            $basicDiscount = basic_discount_calculation($service, $variationPrice * $quantity);
            $campaignDiscount = campaign_discount_calculation($service, $variationPrice * $quantity);
            $subTotal = round($variationPrice * $quantity, 2);

            $applicableDiscount = ($campaignDiscount >= $basicDiscount) ? $campaignDiscount : $basicDiscount;

            $tax = round((($variationPrice * $quantity - $applicableDiscount) * $service['tax']) / 100, 2);

            $basicDiscount = $basicDiscount > $campaignDiscount ? $basicDiscount : 0;
            $campaignDiscount = $campaignDiscount >= $basicDiscount ? $campaignDiscount : 0;

            $data[] = collect([
                'service_id' => $service->id,
                'service_name' => $service->name,
                'variant_key' => $service?->variations[0]?->variant_key,
                'quantity' => $item['quantity'],
                'service_cost' => $variationPrice,
                'total_discount_amount' => $basicDiscount + $campaignDiscount,
                'coupon_code' => null,
                'tax_amount' => round($tax, 2),
                'total_cost' => round($subTotal - $basicDiscount - $campaignDiscount + $tax, 2),
                'zone_id' => $request['zone_id']
            ]);
        }

        return response()->json(response_formatter(DEFAULT_200, $data), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateBooking(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|uuid',
            'service_info' => 'required',
            'payment_status' => 'nullable|in:0,1',
            'serviceman_id' => 'nullable',
            'booking_status' => 'nullable|in:' . implode(',', array_column(BOOKING_STATUSES, 'key')),
            'service_schedule' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $booking = $this->booking
            ->with('detail')
            ->where('id', $request['booking_id'])
            ->where(function ($query) use ($request) {
                return $query->where('provider_id', $request->user()->provider->id)->orWhereNull('provider_id');
            })->first();

        if (!is_null($request['payment_status'])) $booking->is_paid = $request['payment_status'];
        if (!is_null($request['serviceman_id'])) $booking->serviceman_id = $request['serviceman_id'];
        if (!is_null($request['booking_status'])) $booking->booking_status = $request['booking_status'];
        if (!is_null($request['service_schedule'])) $booking->service_schedule = $request['service_schedule'];
        $booking->save();

        $providerEditAccess = (boolean)business_config('provider_can_edit_booking', 'provider_config')?->live_values;
        $request['service_info'] = collect(json_decode($request['service_info'], true));
        $serviceInfoValidated = $request['service_info']?->first()['quantity'] ?? null;

        if ($providerEditAccess && $serviceInfoValidated) {
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
        }
        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeService(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|uuid',
            'service_id' => 'required|uuid',
            'variant_key' => 'required',
            'zone_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $booking = $this->booking->with('detail')
            ->where('id', $request['booking_id'])
            ->where(fn($query) => $query->where('provider_id', $request->user()->provider->id)->orWhereNull('provider_id'))
            ->first();

        if ($booking?->detail->count() < 2) {
            return response()->json(response_formatter(DEFAULT_400), 400);
        }

        $this->remove_service_from_booking($request);

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }
}
