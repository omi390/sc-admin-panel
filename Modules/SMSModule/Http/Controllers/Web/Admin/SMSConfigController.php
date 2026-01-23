<?php

namespace Modules\SMSModule\Http\Controllers\Web\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\View\View;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\Factory;
use Modules\PaymentModule\Entities\Setting;
use Illuminate\Contracts\Foundation\Application;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;

class SMSConfigController extends Controller
{
    private Setting $addonSettings;
    private BusinessSettings $businessSetting;

    public function __construct(Setting $addonSettings, BusinessSettings $businessSetting)
    {
        $this->addonSettings = $addonSettings;
        $this->businessSetting = $businessSetting;
    }

    /**
     * Display a listing of the resource.
     * @return Application|Factory|View
     */
    public function smsConfigGet(): View|Factory|Application
    {
        $publishedStatus = 0; // Set a default value
        $paymentPublishedStatus = config('get_payment_publish_status');
        if (isset($paymentPublishedStatus[0]['is_published'])) {
            $publishedStatus = $paymentPublishedStatus[0]['is_published'];
        }

        $routes = config('addon_admin_routes');
        $desiredName = 'sms_setup';
        $paymentUrl = '';

        foreach ($routes as $routeArray) {
            foreach ($routeArray as $route) {
                if ($route['name'] === $desiredName) {
                    $paymentUrl = $route['url'];
                    break 2;
                }
            }
        }
        $dataValues = $this->addonSettings
        ->whereIn('settings_type', ['sms_config'])
        ->whereIn('key_name', array_column(SMS_GATEWAY, 'key'))
        ->get();
        return view('smsmodule::admin.sms-config', compact('dataValues', 'publishedStatus', 'paymentUrl'));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return RedirectResponse
     */
    public function smsConfigSet(Request $request): RedirectResponse
    {
        $validation = [
            'gateway' => 'required|in:releans,twilio,nexmo,2factor,msg91',
            'mode' => 'required|in:live,test'
        ];

        $additionalData = [];

        if ($request['gateway'] == 'releans') {
            $additionalData = [
                'status' => 'required|in:1,0',
                'api_key' => 'required_if:status,1',
                'from' => 'required_if:status,1',
                'otp_template' => 'required_if:status,1'
            ];
        } elseif ($request['gateway'] == 'twilio') {
            $additionalData = [
                'status' => 'required|in:1,0',
                'sid' => 'required_if:status,1',
                'messaging_service_sid' => 'required_if:status,1',
                'token' => 'required_if:status,1',
                'from' => 'required_if:status,1',
                'otp_template' => 'required_if:status,1'
            ];
        } elseif ($request['gateway'] == 'nexmo') {
            $additionalData = [
                'status' => 'required|in:1,0',
                'api_key' => 'required_if:status,1',
                'api_secret' => 'required_if:status,1',
                'token' => 'required_if:status,1',
                'from' => 'required_if:status,1',
                'otp_template' => 'required_if:status,1'
            ];
        } elseif ($request['gateway'] == '2factor') {
            $additionalData = [
                'status' => 'required|in:1,0',
                'api_key' => 'required_if:status,1'
            ];
        } elseif ($request['gateway'] == 'msg91') {
            $additionalData = [
                'status' => 'required|in:1,0',
                'template_id' => 'required_if:status,1',
                'auth_key' => 'required_if:status,1',
            ];
        }
        $validation = $request->validate(array_merge($validation, $additionalData));

        $this->addonSettings->updateOrCreate(['key_name' => $request['gateway'], 'settings_type' => 'sms_config'], [
            'key_name' => $request['gateway'],
            'live_values' => $validation,
            'test_values' => $validation,
            'settings_type' => 'sms_config',
            'mode' => $request['mode'],
            'is_active' => $request['status'],
        ]);

        if ($request['status'] == 1) {
            foreach (['releans', 'twilio', 'nexmo', '2factor', 'msg91'] as $gateway) {
                if ($request['gateway'] != $gateway) {
                    $keep = $this->addonSettings->where(['key_name' => $gateway, 'settings_type' => 'sms_config'])->first();
                    if (isset($keep)) {
                        $hold = $keep->live_values;
                        $hold['status'] = 0;
                        $this->addonSettings->where(['key_name' => $gateway, 'settings_type' => 'sms_config'])->update([
                            'live_values' => $hold,
                            'test_values' => $hold,
                            'is_active' => 0,
                        ]);
                    }
                }
            }

            $setting = $this->businessSetting->where(['key_name' => 'firebase_otp_verification', 'settings_type' => 'third_party'])->first();

            if ($setting) {
                $liveValues = $setting->live_values;
                $liveValues['status'] = '0';

                $this->businessSetting->where('id', $setting->id)->update([
                    'live_values' => json_encode($liveValues),
                    'is_active' => 0,
                ]);
            }

        }
        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();
    }
}
