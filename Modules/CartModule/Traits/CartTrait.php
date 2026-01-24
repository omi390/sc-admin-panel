<?php

namespace Modules\CartModule\Traits;

use Illuminate\Support\Carbon;
use Modules\BookingModule\Entities\Booking;
use Modules\CartModule\Entities\Cart;
use Modules\ServiceManagement\Entities\Service;
use Modules\UserManagement\Entities\User;

trait CartTrait
{
    /**
     * @param $cartId
     * @param $quantity
     * @return bool
     */
    public function updateCartQuantity($cartId, $quantity): bool
    {
        $cart = Cart::find($cartId);
        $service = Service::with(['service_discount', 'campaign_discount'])->find($cart['service_id']);

        if (!isset($cart) || !isset($service)) return false;

        $basicDiscount = basic_discount_calculation($service, $cart->service_cost * $quantity);
        $campaignDiscount = campaign_discount_calculation($service, $cart->service_cost * $quantity);
        $subtotal = round($cart->service_cost * $quantity, 2);

        $applicableDiscount = ($campaignDiscount >= $basicDiscount) ? $campaignDiscount : $basicDiscount;
        $tax = round(((($cart->service_cost*$quantity - $applicableDiscount) * $service['tax']) / 100), 2);

        //between normal discount & campaign discount, greater one will be calculated
        $basicDiscount = $basicDiscount > $campaignDiscount ? $basicDiscount : 0;
        $campaignDiscount = $campaignDiscount >= $basicDiscount ? $campaignDiscount : 0;

        $cart->quantity = $quantity;
        $cart->discount_amount = $basicDiscount;
        $cart->campaign_discount = $campaignDiscount;
        $cart->coupon_discount = 0;
        $cart->coupon_code = null;
        $cart->tax_amount = round($tax, 2);
        $cart->total_cost = round($subtotal - $basicDiscount - $campaignDiscount + $tax, 2);
        $cart->save();

        return true;
    }

    /**
     * @param $userId
     * @param $totalAmount
     * @return false|void
     */

    private function referralEarningEligiblityCheck($userId, $totalAmount)
    {
        $isFirstBooking = Booking::where('customer_id', $userId)->count('id');
        if ($isFirstBooking > 0) return 0;

        $referredByUser = User::find($userId)->referred_by_user ?? null;
        if (is_null($referredByUser)) return 0;

        $newUserDiscount = business_config('referral_based_new_user_discount', 'customer_config')->live_values ?? 0;
        $discountType = business_config('referral_discount_type', 'customer_config')->live_values ?? 0;
        $discount = business_config('referral_discount_amount', 'customer_config')->live_values ?? 0;
        $validityType = business_config('referral_discount_validity_type', 'customer_config')->live_values ?? 0;
        $validity = business_config('referral_discount_validity', 'customer_config')->live_values ?? 0;
        $customerReferralEarning = business_config('customer_referral_earning', 'customer_config')->live_values ?? 0;
        $amount = 0;

        if ($newUserDiscount && $customerReferralEarning) {
            $todayDate = Carbon::now();
            $user = User::where('id', $userId)->first();

            if ($validityType === 'day') {
                $validityEndDate = $user->created_at->addDays($validity);
            } elseif ($validityType === 'month') {
                $validityEndDate = $user->created_at->addMonths($validity);
            } else {
                return 0;
            }

            if ($todayDate <= $validityEndDate) {
                if ($discountType == 'flat') {
                    $amount = $discount;
                } elseif ($discountType == 'percentage') {
                    $amount = $totalAmount * ($discount / 100);
                }
                return $amount;
            } else {
                return 0;
            }
        }
        return 0;
    }

}
