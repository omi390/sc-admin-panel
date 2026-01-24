<?php

namespace Modules\BusinessSettingsModule\Http\Controllers\Web\Admin;

use App\CentralLogics\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;
use Modules\BusinessSettingsModule\Entities\DataSetting;
use Modules\BusinessSettingsModule\Entities\LoginSetup;
use Modules\PaymentModule\Entities\Setting;
use Ramsey\Uuid\Nonstandard\Uuid;

class LoginSetupController extends Controller
{
    private BusinessSettings $businessSetting;
    private LoginSetup $loginSetup;

    public function __construct(BusinessSettings $businessSetting, LoginSetup $loginSetup)
    {
        $this->businessSetting = $businessSetting;
        $this->loginSetup = $loginSetup;
    }

    /**
     * @param Request $request
     * @return View|Application|Factory|\Illuminate\Contracts\Foundation\Application
     */
    public function loginSetup(Request $request): Application|Factory|View|\Illuminate\Contracts\Foundation\Application
    {
        $webPage = $request->has('web_page') ? $request['web_page'] : 'customer_login';

        if ($webPage == 'customer_login') {
            $loginOptionsValue = $this->loginSetup->where(['key' => 'login_options'])?->first()?->value;
            $loginOptions = json_decode($loginOptionsValue);

            $socialMediaLoginValue = $this->loginSetup->where(['key' => 'social_media_for_login'])?->first()?->value;
            $socialMediaLoginOptions = json_decode($socialMediaLoginValue);

            $emailVerification = (int)$this->loginSetup->where(['key' => 'email_verification'])?->first()?->value ?? 0;
            $phoneVerification = (int)$this->loginSetup->where(['key' => 'phone_verification'])?->first()?->value ?? 0;

            return view('businesssettingsmodule::admin.login-setup', compact('emailVerification', 'phoneVerification', 'loginOptions', 'socialMediaLoginOptions', 'webPage'));
        }elseif ($webPage == 'admin_provider_login'){
            $dataValues = $this->businessSetting->where('settings_type', 'otp_login_setup')->get();
            return view('businesssettingsmodule::admin.login-setup', compact('webPage', 'dataValues'));
        }
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function loginSetupUpdate(Request $request): RedirectResponse
    {
        $this->loginSetup->updateOrInsert(['key' => 'login_options'], [
                'id' => Uuid::uuid4(),
                'value' => json_encode([
                    'manual_login' => $request->has('manual_login') ? 1: 0,
                    'otp_login' => $request->has('otp_login') ? 1: 0,
                    'social_media_login' => $request->has('social_media_login') ? 1: 0,
                ])
            ]
        );
        $this->loginSetup->updateOrInsert(['key' => 'social_media_for_login'], [
                'id' => Uuid::uuid4(),
                'value' => json_encode([
                    'google' => $request->has('google') ? 1: 0,
                    'facebook' => $request->has('facebook') ? 1: 0,
                    'apple' => $request->has('apple') ? 1: 0,
                ])
            ]
        );
        $this->loginSetup->updateOrInsert(['key' => 'email_verification'], [
                'id' => Uuid::uuid4(),
                'value' => $request->has('email_verification') ? 1: 0,
            ]
        );
        $this->loginSetup->updateOrInsert(['key' => 'phone_verification'], [
                'id' => Uuid::uuid4(),
                'value' => $request->has('phone_verification') ? 1: 0,
            ]
        );

        Toastr::success(translate('Settings updated!'));
        return back();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function checkActiveSMSGateway(Request $request): JsonResponse
    {

        $dataValues = Setting::where('settings_type', 'sms_config')->get();
        $count = 0;
        foreach ($dataValues as $gateway) {
            $status = $gateway?->live_values['status'] ?? 0;
            if ($status == 1) {
                $count += 1;
            }
        }

        $firebaseOTPVerification = business_config('firebase_otp_verification','third_party');
        $firebaseOTPVerificationStatus = $firebaseOTPVerification ? $firebaseOTPVerification->is_active : 0;
        if ($firebaseOTPVerificationStatus == 1){
            $count++;
        }

        return response()->json($count);
    }

    public function checkActiveSocialMedia(Request $request)
    {
        $appleLogin = business_config('apple_login', 'third_party')?->live_values ?? [];
        $apple = $appleLogin['status'];
        return response()->json([
            'apple' => $apple,
        ]);
    }

    public function otpLoginInformationSet(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'temporary_login_block_time' => 'required',
            'maximum_login_hit' => 'required',
            'temporary_otp_block_time' => 'required',
            'maximum_otp_hit' => 'required',
            'otp_resend_time' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        foreach ($validator->validated() as $key => $value) {
            $this->businessSetting->updateOrCreate(['key_name' => $key], [
                'key_name' => $key,
                'live_values' => $value,
                'test_values' => $value,
                'settings_type' => 'otp_login_setup',
                'mode' => 'live',
                'is_active' => 1,
            ]);
        }

        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();
    }
}
