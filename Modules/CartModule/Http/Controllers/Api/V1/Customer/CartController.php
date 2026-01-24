<?php

namespace Modules\CartModule\Http\Controllers\Api\V1\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Http\Traits\BookingTrait;
use Modules\CartModule\Entities\Cart;
use Modules\CartModule\Traits\AddedToCartTrait;
use Modules\CartModule\Traits\CartTrait;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ServiceManagement\Entities\Service;
use Modules\ServiceManagement\Entities\Variation;
use Modules\UserManagement\Entities\Guest;
use Modules\UserManagement\Entities\User;
use Ramsey\Uuid\Uuid;

class CartController extends Controller
{
    private Cart $cart;
    private Service $service;
    private Variation $variation;
    private User $user;

    private Booking $booking;
    private Provider $provider;
    private Guest $guest;
    private bool $isCustomerLoggedIn;
    private mixed $customerUserId;

    use CartTrait, AddedToCartTrait;

    public function __construct(Cart $cart, Service $service, Variation $variation, User $user, Provider $provider, Guest $guest, Request $request, Booking $booking)
    {
        $this->cart = $cart;
        $this->service = $service;
        $this->variation = $variation;
        $this->user = $user;
        $this->provider = $provider;
        $this->guest = $guest;
        $this->booking = $booking;

        $this->isCustomerLoggedIn = (bool)auth('api')->user();
        $this->customerUserId = $this->isCustomerLoggedIn ? auth('api')->user()->id : $request['guest_id'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function addToCart(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'provider_id' => 'uuid',
            'service_id' => 'required|uuid',
            'category_id' => 'required|uuid',
            'sub_category_id' => 'required|uuid',
            'variant_key' => 'required',
            'quantity' => 'required|numeric|min:1|max:1000',
            'guest_id' => $this->isCustomerLoggedIn ? 'nullable' : 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $customerUserId = $this->customerUserId;

        $this->addedToCartUpdate($customerUserId, $request['service_id'], !$this->isCustomerLoggedIn);

        if ($request['provider_id']){
            $nextBookingEligibility = nextBookingEligibility($request['provider_id']);
            if (!$nextBookingEligibility){
                return response()->json(response_formatter(BOOKING_ELIGIBILITY_FOR_BOOKING), 400);
            }
        }

        $variation = $this->variation
            ->where(['zone_id' => Config::get('zone_id'), 'service_id' => $request['service_id']])
            ->where(['variant_key' => $request['variant_key']])
            ->first();

        if (isset($variation)) {
            $service = $this->service->find($request['service_id']);

            $checkCart = $this->cart->where([
                'service_id' => $request['service_id'],
                'variant_key' => $request['variant_key'],
                'customer_id' => $customerUserId])->first();
            $cart = $checkCart ?? $this->cart;
            $quantity = $request['quantity'];

            $basicDiscount = basic_discount_calculation($service, $variation->price * $quantity);
            $campaignDiscount = campaign_discount_calculation($service, $variation->price * $quantity);
            $subtotal = round($variation->price * $quantity, 2);

            $applicableDiscount = ($campaignDiscount >= $basicDiscount) ? $campaignDiscount : $basicDiscount;

            $tax = round((($variation->price * $quantity - $applicableDiscount) * $service['tax']) / 100, 2);

            //between normal discount & campaign discount, greater one will be calculated
            $basicDiscount = $basicDiscount > $campaignDiscount ? $basicDiscount : 0;
            $campaignDiscount = $campaignDiscount >= $basicDiscount ? $campaignDiscount : 0;

            $cart->provider_id = $request['provider_id'];
            $cart->customer_id = $customerUserId;
            $cart->service_id = $request['service_id'];
            $cart->category_id = $request['category_id'];
            $cart->sub_category_id = $request['sub_category_id'];
            $cart->variant_key = $request['variant_key'];
            $cart->quantity = $quantity;
            $cart->service_cost = $variation->price;
            $cart->discount_amount = $basicDiscount;
            $cart->campaign_discount = $campaignDiscount;
            $cart->coupon_discount = 0;
            $cart->coupon_code = null;
            $cart->is_guest = !$this->isCustomerLoggedIn;
            $cart->tax_amount = round($tax, 2);
            $cart->total_cost = round($subtotal - $basicDiscount - $campaignDiscount + $tax, 2);
            $cart->save();

            if (!$this->isCustomerLoggedIn) {
                $guest = $this->guest;
                $guest->ip_address = $request->ip();
                $guest->guest_id = $request->guest_id;
                $guest->save();
            }

            return response()->json(response_formatter(DEFAULT_STORE_200), 200);
        }

        return response()->json(response_formatter(DEFAULT_404), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'guest_id' => $this->isCustomerLoggedIn ? 'nullable' : 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $customerUserId = $this->customerUserId;
        $cart = $this->cart
            ->with(['customer', 'provider.owner', 'category', 'sub_category', 'service'])
            ->where(['customer_id' => $customerUserId])
            ->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])
            ->withPath('');

        $walletBalance = $this->user->find($customerUserId)?->wallet_balance ?? 0;

        $additionalCharge = 0;
        if ((business_config('booking_additional_charge', 'booking_setup'))?->live_values) {
            $additionalCharge = (business_config('additional_charge_fee_amount', 'booking_setup'))?->live_values;
        }

        foreach ($cart as $cartItem) {
            if ($cartItem?->provider) {
                $providerId = optional($cartItem->provider)->id;
                $timeSchedule = provider_config('time_schedule', 'service_schedule', $providerId)->live_values ?? '';
                $weekEnds = provider_config('weekends', 'service_schedule', $providerId)->live_values ?? '';
                $weekEnds = json_decode($weekEnds);
                $timeSchedule = json_decode($timeSchedule);
                $cartItem->provider->weekends = $weekEnds ?? [];
                $cartItem->provider->time_schedule = $timeSchedule ?? null;
                $cartItem->provider->nextBookingEligibility = nextBookingEligibility($providerId);
                $cartItem->provider->scheduleBookingEligibility = scheduleBookingEligibility($providerId);

            }
        }

        $totalTax = $cart->sum('tax_amount');
        $totalCost = $cart->sum('total_cost');

        $referralAmount = $this->referralEarningEligiblityCheck($customerUserId, $totalCost - $totalTax);

        $totalCost -= $referralAmount;

        $totalCost += $additionalCharge;

        return response()->json(response_formatter(DEFAULT_200, ['total_cost' => $totalCost, 'referral_amount' => $referralAmount, 'wallet_balance' => with_decimal_point($walletBalance), 'cart' => $cart]), 200);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function updateQty(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'guest_id' => $this->isCustomerLoggedIn ? 'nullable' : 'required|uuid',
            'quantity' => 'required|numeric|min:1|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $this->updateCartQuantity($id, $request['quantity']);

        $customerUserId = $this->customerUserId;
        $cart = $this->cart
            ->with(['customer', 'provider.owner', 'category', 'sub_category', 'service'])
            ->where(['customer_id' => $customerUserId])
            ->latest()
            ->paginate(100, ['*'], 'offset', 1)
            ->withPath('');

        foreach ($cart as $item) {
            if ($item->provider) {
                $providerId = $item->provider->id;
                $timeSchedule = provider_config('time_schedule', 'service_schedule', $providerId)->live_values ?? '';
                $weekEnds = provider_config('weekends', 'service_schedule', $providerId)->live_values ?? '';
                $weekEnds = json_decode($weekEnds);
                $timeSchedule = json_decode($timeSchedule);
                $item->provider->weekends = $weekEnds ?? [];
                $item->provider->time_schedule = $timeSchedule ?? null;
                $item->provider->nextBookingEligibility = nextBookingEligibility($providerId);
                $item->provider->scheduleBookingEligibility = scheduleBookingEligibility($providerId);
            }
        }

        $additionalCharge = 0;
        if ((business_config('booking_additional_charge', 'booking_setup'))?->live_values) {
            $additionalCharge = (business_config('additional_charge_fee_amount', 'booking_setup'))?->live_values;
        }
        $totalCost = $cart->sum('total_cost');
        $totalTax = $cart->sum('tax_amount');

        $referralAmount = $this->referralEarningEligiblityCheck($customerUserId, $totalCost - $totalTax);

        $totalCost -= $referralAmount;
        $totalCost += $additionalCharge;

        $walletBalance = $this->user->find($customerUserId)?->wallet_balance ?? 0;

        return response()->json(response_formatter(DEFAULT_UPDATE_200, ['total_cost' => $totalCost,'referral_amount' => $referralAmount, 'wallet_balance' => with_decimal_point($walletBalance), 'cart' => $cart]), 200);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProvider(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'provider_id' => 'nullable|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $providerId = $request->has('provider_id') ? $request->provider_id : null;

        if ($providerId){
            $nextBookingEligibility = nextBookingEligibility($providerId);
            if (!$nextBookingEligibility){
                return response()->json(response_formatter(BOOKING_ELIGIBILITY_FOR_BOOKING), 400);
            }
        }

        $this->cart
            ->where('customer_id', $this->customerUserId)
            ->update(['provider_id' => $providerId]);

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function remove(Request $request, string $id): JsonResponse
    {
        $cart = $this->cart->where(['id' => $id])->first();

        if (!isset($cart)) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        $this->cart->where('id', $id)->delete();

        return response()->json(response_formatter(DEFAULT_DELETE_200), 200);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function emptyCart(Request $request): JsonResponse
    {
        $cart = $this->cart->where(['customer_id' => $this->customerUserId]);
        if ($cart->count() == 0) return response()->json(response_formatter(DEFAULT_204), 200);
        $cart->delete();

        return response()->json(response_formatter(DEFAULT_DELETE_200), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function rebookAddToCart(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'guest_id' => $this->isCustomerLoggedIn ? 'nullable' : 'required|uuid',
            'booking_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $customerUserId = $this->customerUserId;

        $booking = $this->booking->where('id', $request->booking_id)->first();

        $allItemsInCart = $this->checkIfAllItemsInCart($booking->detail, $customerUserId);
        if ($allItemsInCart) {
            return response()->json(response_formatter(DEFAULT_CART_ALREADY_ADDED_200), 200);
        }

        $provider = $this->provider
            ->where('id', $booking?->provider->id)
            ->ofStatus(1)
            ->when(business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values, function ($query) {
                $query->where('is_suspended', 0);
            })
            ->where('zone_id', $request->header('zoneid'))
            ->whereHas('subscribed_services', function ($query) use ($request, $booking) {
                $query->where('sub_category_id', $booking->sub_category_id)->where('is_subscribed', 1);
            })
            ->first();

        if (!Cart::where('sub_category_id', $booking->sub_category_id)->where('customer_id', $customerUserId)->exists()) {
            Cart::where('customer_id', $customerUserId)->delete();
        }

        foreach ($booking->detail as $detail) {

            $serviceData = $this->service->where('id', $detail->service_id)->active()->first();

            if ($serviceData) {
                $this->addedToCartUpdate($customerUserId, $detail->service_id, !$this->isCustomerLoggedIn);

                $variation = $this->variation
                    ->where(['zone_id' => Config::get('zone_id'), 'service_id' => $detail->service_id])
                    ->where(['variant_key' => $detail->variant_key])
                    ->first();

                if (isset($variation)) {
                    DB::transaction(function () use ($detail, $customerUserId, $variation, $provider, $booking, $request) {
                        $service = $this->service->find($detail->service_id);

                        $checkCart = $this->cart->where([
                            'service_id' => $detail->service_id,
                            'variant_key' => $detail->variant_key,
                            'customer_id' => $customerUserId])->first();

                        $cart = $checkCart ?? new Cart();
                        $quantity = $detail->quantity;

                        //calculation
                        $basicDiscount = basic_discount_calculation($service, $variation->price * $quantity);
                        $campaignDiscount = campaign_discount_calculation($service, $variation->price * $quantity);
                        $subtotal = round($variation->price * $quantity, 2);

                        $applicableDiscount = ($campaignDiscount >= $basicDiscount) ? $campaignDiscount : $basicDiscount;

                        $tax = round((($variation->price * $quantity - $applicableDiscount) * $service['tax']) / 100, 2);

                        //between normal discount & campaign discount, greater one will be calculated
                        $basicDiscount = $basicDiscount > $campaignDiscount ? $basicDiscount : 0;
                        $campaignDiscount = $campaignDiscount >= $basicDiscount ? $campaignDiscount : 0;

                        $cart->provider_id = $provider?->id ?? null;
                        $cart->customer_id = $customerUserId;
                        $cart->service_id = $detail->service_id;
                        $cart->category_id = $booking->category_id;
                        $cart->sub_category_id = $booking->sub_category_id;
                        $cart->variant_key = $detail->variant_key;
                        $cart->quantity = $quantity;
                        $cart->service_cost = $variation->price;
                        $cart->discount_amount = $basicDiscount;
                        $cart->campaign_discount = $campaignDiscount;
                        $cart->coupon_discount = 0;
                        $cart->coupon_code = null;
                        $cart->is_guest = !$this->isCustomerLoggedIn;
                        $cart->tax_amount = round($tax, 2);
                        $cart->total_cost = round($subtotal - $basicDiscount - $campaignDiscount + $tax, 2);
                        $cart->save();

                        if (!$this->isCustomerLoggedIn) {
                            $guest = $this->guest;
                            $guest->ip_address = $request->ip();
                            $guest->guest_id = $request->guest_id;
                            $guest->save();
                        }

                    });
                }

            }
        }

        return response()->json(response_formatter(DEFAULT_CART_STORE_200), 200);
    }

    private function checkIfAllItemsInCart($details, $customerUserId): bool
    {
        foreach ($details as $detail) {
            $checkCart = $this->cart->where([
                'service_id' => $detail->service_id,
                'variant_key' => $detail->variant_key,
                'customer_id' => $customerUserId,
            ])->exists();

            if (!$checkCart) {
                return false;
            }
        }

        return true;
    }
}
