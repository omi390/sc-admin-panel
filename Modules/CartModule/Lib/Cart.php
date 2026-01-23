<?php

use Illuminate\Support\Facades\DB;
use Modules\CartModule\Entities\Cart;
use Modules\CartModule\Entities\CartServiceInfo;

if (!function_exists('cart_items')) {
    function cart_items($user_id)
    {
        return Cart::where(['customer_id' => $user_id])->get();
    }
}

if (!function_exists('cart_total')) {
    function cart_total($user_id): float
    {
        return (cart_items($user_id))->sum('total_cost');
    }
}

if (!function_exists('cart_clean')) {
    function cart_clean($user_id)
    {
        Cart::where(['customer_id' => $user_id])->delete();
        return [
            'flag' => 'success'
        ];
    }
}
