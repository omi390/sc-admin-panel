<?php

namespace Modules\BookingModule\Http\Controllers\Api\V1\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\BidModule\Entities\PostBid;
use Illuminate\Support\Facades\Validator;
use Modules\UserManagement\Entities\User;
use Modules\BookingModule\Entities\Booking;
use Modules\PaymentModule\Entities\OfflinePayment;
use Modules\BookingModule\Http\Traits\BookingTrait;
use Modules\CustomerModule\Traits\CustomerAddressTrait;
use Modules\BookingModule\Entities\BookingStatusHistory;
use Modules\BidModule\Http\Controllers\APi\V1\Customer\PostBidController;

class BookingController extends Controller
{
    use BookingTrait, CustomerAddressTrait;

    private Booking $booking;
    private BookingStatusHistory $bookingStatusHistory;

    protected OfflinePayment $offlinePayment;
    private bool $isCustomerLoggedIn;
    private mixed $customerUserId;

    public function __construct(Booking $booking, BookingStatusHistory $bookingStatusHistory, Request $request, OfflinePayment $offlinePayment)
    {
        $this->booking = $booking;
        $this->bookingStatusHistory = $bookingStatusHistory;
        $this->offlinePayment = $offlinePayment;

        $this->isCustomerLoggedIn = (bool)auth('api')->user();
        $this->customerUserId = $this->isCustomerLoggedIn ? auth('api')->user()->id : $request['guest_id'];
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function placeRequest(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:' . implode(',', array_column(PAYMENT_METHODS, 'key')),
            'zone_id' => 'required|uuid',
            'service_schedule' => 'required|date',
            'service_address_id' => is_null($request['service_address']) ? 'required' : 'nullable',

            'post_id' => 'nullable|uuid',
            'provider_id' => 'nullable|uuid',

            'guest_id' => $this->isCustomerLoggedIn ? 'nullable' : 'required|uuid',
            'offline_payment_id' => 'required_if:payment_method,offline_payment',
            'customer_information' => 'required_if:payment_method,offline_payment',
            'service_address' => is_null($request['service_address_id']) ? [
                'required',
                'json',
                function ($attribute, $value, $fail) {
                    $decoded = json_decode($value, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $fail($attribute . ' must be a valid JSON string.');
                        return;
                    }

                    if (is_null($decoded['lat']) || $decoded['lat'] == '') $fail($attribute . ' must contain "lat" properties.');
                    if (is_null($decoded['lon']) || $decoded['lon'] == '') $fail($attribute . ' must contain "lon" properties.');
                    if (is_null($decoded['address']) || $decoded['address'] == '') $fail($attribute . ' must contain "address" properties.');
                    if (is_null($decoded['contact_person_name']) || $decoded['contact_person_name'] == '') $fail($attribute . ' must contain "contact_person_name" properties.');
                    if (is_null($decoded['contact_person_number']) || $decoded['contact_person_number'] == '') $fail($attribute . ' must contain "contact_person_number" properties.');
                    if (is_null($decoded['address_label']) || $decoded['address_label'] == '') $fail($attribute . ' must contain "address_label" properties.');
                },
            ] : '',

            'is_partial' => 'nullable|in:0,1'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        if ($request['payment_method'] == 'offline_payment') {
            $offlinePaymentData = $this->offlinePayment->find($request['offline_payment_id']);
            $fields = array_column($offlinePaymentData->customer_information, 'field_name');
            $customerInformation = (array)json_decode(base64_decode($request['customer_information']))[0];

            foreach ($fields as $field) {
                if (!key_exists($field, $customerInformation)) {
                    return response()->json(response_formatter(DEFAULT_400, $fields, null), 400);
                }
            }
        }

        $customerUserId = $this->customerUserId;
        if (is_null($request['service_address_id'])) {
            $request['service_address_id'] = $this->add_address(json_decode($request['service_address']), null, !$this->isCustomerLoggedIn);
        }

        $minimumBookingAmount = (float)(business_config('min_booking_amount', 'booking_setup'))?->live_values;
        $totalBookingAmount = cart_total($customerUserId) + getServiceFee();

        if (!isset($request['post_id']) && $minimumBookingAmount > 0 && $totalBookingAmount < $minimumBookingAmount) {
            return response()->json(response_formatter(MINIMUM_BOOKING_AMOUNT_200), 200);
        }

        if ($request['payment_method'] == 'wallet_payment') {
            if (!isset($request['post_id'])) {
                $response = $this->placeBookingRequest($customerUserId, $request, 'wallet_payment');
            } else {
                $postBid = PostBid::with(['post'])
                    ->where('post_id', $request['post_id'])
                    ->where('provider_id', $request['provider_id'])
                    ->first();

                $data = [
                    'payment_method' => $request['payment_method'],
                    'zone_id' => $request['zone_id'],
                    'service_tax' => $postBid?->post?->service?->tax,
                    'provider_id' => $postBid->provider_id,
                    'price' => $postBid->offered_price,
                    'service_schedule' => !is_null($request['booking_schedule']) ? $request['booking_schedule'] : $postBid->post->booking_schedule,
                    'service_id' => $postBid->post->service_id,
                    'category_id' => $postBid->post->category_id,
                    'sub_category_id' => $postBid->post->category_id,
                    'service_address_id' => !is_null($request['service_address_id']) ? $request['service_address_id'] : $postBid->post->service_address_id,
                    'is_partial' => $request['is_partial']
                ];

                $user = User::find($customerUserId);
                $tax = !is_null($data['service_tax']) ? round((($data['price'] * $data['service_tax']) / 100) * 1, 2) : 0;
                if (isset($user) && $user->wallet_balance < ($postBid->offered_price + $tax)) {
                    return response()->json(response_formatter(INSUFFICIENT_WALLET_BALANCE_400), 400);
                }

                $response = $this->placeBookingRequestForBidding($customerUserId, $request, 'wallet_payment', $data);

                if ($response['flag'] == 'success') {
                    PostBidController::acceptPostBidOffer($postBid->id, $response['booking_id']);
                }
            }

        } elseif ($request['payment_method'] == 'offline_payment') {
            if (!isset($request['post_id'])) {
                $response = $this->placeBookingRequest($customerUserId, $request, 'offline-payment', !$this->isCustomerLoggedIn);
            } else {
                $postBid = PostBid::with(['post'])
                    ->where('post_id', $request['post_id'])
                    ->where('provider_id', $request['provider_id'])
                    ->first();

                $data = [
                    'payment_method' => $request['payment_method'],
                    'zone_id' => $request['zone_id'],
                    'service_tax' => $postBid?->post?->service?->tax,
                    'provider_id' => $postBid->provider_id,
                    'price' => $postBid->offered_price,
                    'service_schedule' => !is_null($request['booking_schedule']) ? $request['booking_schedule'] : $postBid->post->booking_schedule,
                    'service_id' => $postBid->post->service_id,
                    'category_id' => $postBid->post->category_id,
                    'sub_category_id' => $postBid->post->category_id,
                    'service_address_id' => !is_null($request['service_address_id']) ? $request['service_address_id'] : $postBid->post->service_address_id,
                    'is_partial' => $request['is_partial']
                ];

                $response = $this->placeBookingRequestForBidding($customerUserId, $request, 'offline_payment', $data);

                if ($response['flag'] == 'success') {
                    PostBidController::acceptPostBidOffer($postBid->id, $response['booking_id']);
                }
            }
        } else {
            $response = $this->placeBookingRequest($customerUserId, $request, 'cash-payment', !$this->isCustomerLoggedIn);
        }

        if ($response['flag'] == 'success') {
            return response()->json(response_formatter(BOOKING_PLACE_SUCCESS_200, $response), 200);
        } else {
            return response()->json(response_formatter(BOOKING_PLACE_FAIL_200), 200);
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
            'booking_status' => 'required|in:all,' . implode(',', array_column(BOOKING_STATUSES, 'key')),
            'string' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $bookings = $this->booking
            ->with(['customer'])
            ->where(['customer_id' => $request->user()->id])
            ->search(base64_decode($request['string']), ['readable_id'])
            ->when($request['booking_status'] != 'all', function ($query) use ($request) {
                return $query->ofBookingStatus($request['booking_status']);
            })
            ->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])
            ->withPath('');

        return response()->json(response_formatter(DEFAULT_200, $bookings), 200);
    }

    /**
     * Show the specified resource.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $booking = $this->booking->where(['customer_id' => $request->user()->id])->with([
            'detail.service', 'schedule_histories.user', 'status_histories.user', 'service_address', 'customer', 'provider', 'zone', 'serviceman.user', 'booking_partial_payments'
        ])->where(['id' => $id])->first();

        if (isset($booking)) {
            $offlinePayment = $booking->booking_offline_payments?->first()?->customer_information;
            unset($booking->booking_offline_payments);

            if ($offlinePayment) {
                $booking->booking_offline_payment = collect($offlinePayment)->map(function ($value, $key) {
                    return ["key" => $key, "value" => $value];
                })->values()->all();
            }

            if (isset($booking->provider)){
                $booking->provider->chatEligibility = chatEligibility($booking->provider_id);
            }

            return response()->json(response_formatter(DEFAULT_200, $booking), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }

    /**
     * Show the specified resource.
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function track(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $booking = $this->booking
            ->with(['detail.service', 'schedule_histories.user', 'status_histories.user', 'service_address', 'customer', 'provider', 'zone', 'serviceman.user'])
            ->where(['readable_id' => $id])
            ->whereHas('service_address', fn($query) => $query->where('contact_person_number', $request['phone']))
            ->first();

        if (isset($booking)) return response()->json(response_formatter(DEFAULT_200, $booking), 200);

        return response()->json(response_formatter(DEFAULT_404, $booking), 404);
    }

    /**
     * Show the specified resource.
     * @param Request $request
     * @param string $booking_id
     * @return JsonResponse
     */
    public function statusUpdate(Request $request, string $booking_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'booking_status' => 'required|in:canceled',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $booking = $this->booking->where('id', $booking_id)->where('customer_id', $request->user()->id)->first();

        if (isset($booking)) {

            if($booking->booking_status == 'accepted' && $request['booking_status'] == 'canceled'){
                return response()->json(response_formatter(BOOKING_ALREADY_ACCEPTED), 200);
            }

            if($booking->booking_status == 'ongoing' && $request['booking_status'] == 'canceled'){
                return response()->json(response_formatter(BOOKING_ALREADY_ONGOING), 200);
            }

            if($booking->booking_status == 'completed' && $request['booking_status'] == 'canceled'){
                return response()->json(response_formatter(BOOKING_ALREADY_COMPLETED), 200);
            }

            $booking->booking_status = $request['booking_status'];

            $bookingStatusHistory = $this->bookingStatusHistory;
            $bookingStatusHistory->booking_id = $booking_id;
            $bookingStatusHistory->changed_by = $request->user()->id;
            $bookingStatusHistory->booking_status = $request['booking_status'];

            DB::transaction(function () use ($bookingStatusHistory, $booking) {
                $booking->save();
                $bookingStatusHistory->save();
            });

            return response()->json(response_formatter(BOOKING_STATUS_UPDATE_SUCCESS_200, $booking), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }


}
