<?php

namespace Modules\CustomerModule\Traits;

use Modules\CartModule\Entities\Cart;
use Modules\CartModule\Traits\CartTrait;
use Modules\UserManagement\Entities\UserAddress;
use Illuminate\Support\Facades\DB;

trait CustomerTrait
{
    use CartTrait;
    /**
     * @param $customerUserId
     * @param $guest_id
     * @return void
     */
    public function updateAddressAndCartUser($customerUserId, $guest_id): void
    {
        DB::transaction(function () use ($customerUserId, $guest_id) {
            $loggedUserCarts = Cart::where('customer_id', $customerUserId)->get();
            $guestCarts = Cart::where('customer_id', $guest_id)->get();

            if (count($loggedUserCarts) > 0 && count($guestCarts) >0) {
                $guestCartSubCategoryId = Cart::where('customer_id', $guest_id)->first()?->sub_category_id;
                foreach ($loggedUserCarts as $cart) {
                    $guest_cart = $guestCarts->where('variant_key', $cart->variant_key)->first();

                    if ($cart->sub_category_id == $guestCartSubCategoryId) {
                        $quantity = $cart->quantity + $guest_cart?->quantity ?? 0;
                        $this->updateCartQuantity($cart->id, $quantity);
                    }
                    Cart::where('variant_key', $cart->variant_key)->delete();
                }
            }

            Cart::where('customer_id', $guest_id)->update(['customer_id' => $customerUserId]);
            UserAddress::where('user_id', $guest_id)->update(['user_id' => $customerUserId]);
        });
    }

}
