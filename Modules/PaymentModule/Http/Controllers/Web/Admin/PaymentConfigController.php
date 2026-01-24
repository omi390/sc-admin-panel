<?php

namespace Modules\PaymentModule\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;
use Modules\PaymentModule\Entities\Setting;

class PaymentConfigController extends Controller
{
    private Setting $addonSettings;

    public function __construct(Setting $addonSettings)
    {
        $this->addonSettings = $addonSettings;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return RedirectResponse
     */
    public function setPaymentConfig(Request $request): RedirectResponse
    {
        $validation = [
            'gateway' => 'required|in:' . implode(',', array_column(DIGITAL_PAYMENT_METHODS, 'key')),
            'mode' => 'required|in:live,test'
        ];
        $additionalData = [];

        if ($request['gateway'] == 'ssl_commerz') {
            $additionalData = [
                'status' => 'required|in:1,0',
                'store_id' => 'required_if:status,1',
                'store_password' => 'required_if:status,1'
            ];
        } elseif ($request['gateway'] == 'stripe') {
            $additionalData = [
                'status' => 'required|in:1,0',
                'api_key' => 'required_if:status,1',
                'published_key' => 'required_if:status,1',
            ];
        } elseif ($request['gateway'] == 'razor_pay') {
            $additionalData = [
                'status' => 'required|in:1,0',
                'api_key' => 'required_if:status,1',
                'api_secret' => 'required_if:status,1'
            ];
        } elseif ($request['gateway'] == 'senang_pay') {
            $additionalData = [
                'status' => 'required|in:1,0',
                'callback_url' => 'required_if:status,1',
                'secret_key' => 'required_if:status,1',
                'merchant_id' => 'required_if:status,1'
            ];
        } elseif ($request['gateway'] == 'paystack') {
            $additionalData = [
                'status' => 'required|in:1,0',
                'public_key' => 'required_if:status,1',
                'secret_key' => 'required_if:status,1',
                'merchant_email' => 'required_if:status,1'
            ];
        } elseif ($request['gateway'] == 'flutterwave') {
            $additionalData = [
                'status' => 'required|in:1,0',
                'secret_key' => 'required_if:status,1',
                'public_key' => 'required_if:status,1',
                'hash' => 'required_if:status,1'
            ];
        } elseif ($request['gateway'] == 'paytm') {
            $additionalData = [
                'status' => 'required|in:1,0',
                'merchant_key' => 'required_if:status,1',
                'merchant_id' => 'required_if:status,1',
                'merchant_website_link' => 'required_if:status,1'
            ];
        } elseif ($request['gateway'] == 'cash_after_service') {
            $additionalData = [
                'status' => 'required|in:1,0'
            ];
        } elseif ($request['gateway'] == 'digital_payment') {
            $additionalData = [
                'status' => 'required|in:1,0'
            ];
        }
        $validation = $request->validate(array_merge($validation, $additionalData));
        $addonSettings = $this->addonSettings->where('key_name', $request['gateway'])->where('settings_type', 'payment_config')->first();
        $additionalDataImage = $addonSettings['additional_data'] != null ? json_decode($addonSettings['additional_data']) : null;

        if ($request->has('gateway_image')) {
            $gatewayImage = file_uploader('payment_modules/gateway_image/', 'png', $request['gateway_image'], $additionalDataImage != null ? $additionalDataImage->gateway_image : '');
        } else {
            $gatewayImage = $additionalDataImage != null ? $additionalDataImage->gateway_image : '';
        }

        $paymentAdditionalData = [
            'gateway_title' => $request['gateway_title'],
            'gateway_image' => $gatewayImage,
            'storage' => getDisk(),
        ];

        $validator = Validator::make($request->all(), array_merge($validation, $additionalData));


        $this->addonSettings->updateOrCreate(['key_name' => $request['gateway'], 'settings_type' => 'payment_config'], [
            'key_name' => $request['gateway'],
            'live_values' => $validation,
            'test_values' => $validation,
            'settings_type' => 'payment_config',
            'mode' => $request['mode'],
            'is_active' => $request['status'],
            'additional_data' => json_encode($paymentAdditionalData),
        ]);

        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();
    }
}
