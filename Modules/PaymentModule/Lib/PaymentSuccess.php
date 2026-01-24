<?php

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Modules\BookingModule\Http\Traits\BookingTrait;
use Modules\CartModule\Entities\Cart;
use Modules\CartModule\Entities\CartServiceInfo;
use Modules\PaymentModule\Lib\PaymentResponse;
use Modules\PaymentModule\Traits\PaymentHelperTrait;
use Modules\UserManagement\Entities\User;

if (!function_exists('digital_payment_success')) {
    /**
     * @param $data
     * @return void
     */
    function digital_payment_success($data): void
    {
        PaymentResponse::success($data);
    }
}

if (!function_exists('digital_payment_fail')) {
    /**
     * @param $data
     * @return void
     */
    function digital_payment_fail($data): void
    {
        //
    }
}

if (!function_exists('subscription_success')) {
    /**
     * @param $data
     * @return void
     */
    function subscription_success($data): void
    {
        $additional_data = json_decode($data['additional_data'], true);
        $packageStatus = collect([
            'package_status' => $additional_data['package_status'] ?? null,
        ]);

        if ($packageStatus['package_status'] == 'subscription_purchase'){
            PaymentResponse::purchaseSubscriptionSuccess($data);
        }elseif ($packageStatus['package_status'] == 'subscription_renew'){
            PaymentResponse::renewSubscriptionSuccess($data);
        }elseif ($packageStatus['package_status'] == 'subscription_shift'){
            PaymentResponse::shiftSubscriptionSuccess($data);
        }elseif ($packageStatus['package_status'] == 'business_plan_change'){
            PaymentResponse::businessPlanChangeSuccess($data);
        }
    }
}

if (!function_exists('subscription_fail')) {
    /**
     * @param $data
     * @return void
     */
    function subscription_fail($data): void
    {
        $additional_data = json_decode($data['additional_data'], true);
        $packageStatus = collect([
            'package_status' => $additional_data['package_status'] ?? null,
        ]);

        if ($packageStatus['package_status'] == 'subscription_purchase') {
            PaymentResponse::purchaseSubscriptionFailed($data);
        }
    }
}

