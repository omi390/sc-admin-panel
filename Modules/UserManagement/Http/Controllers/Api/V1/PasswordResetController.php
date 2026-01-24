<?php

namespace Modules\UserManagement\Http\Controllers\Api\V1;

use Carbon\CarbonInterval;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Modules\SMSModule\Lib\SMS_gateway;
use Modules\UserManagement\Emails\OTPMail;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Entities\UserVerification;
use Modules\PaymentModule\Traits\SmsGateway;

class PasswordResetController extends Controller
{
    public function __construct(
        private User             $user,
        private UserVerification $userVerification
    )
    {
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function check(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'identity' => 'required|max:255',
            'identity_type' => 'required|in:phone,email'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $user = $this->user->where($request['identity_type'], $request['identity'])->first();

        if (!isset($user))
            return response()->json(response_formatter(AUTH_LOGIN_404), 404);


        //resend time check
        $userVerification = $this->userVerification->where('identity', $request['identity'])->first();
        $otpResendTime = business_config('otp_resend_time', 'otp_login_setup')?->live_values;
        if (isset($userVerification) && Carbon::parse($userVerification->created_at)->DiffInSeconds() < $otpResendTime) {
            $time = $otpResendTime - Carbon::parse($userVerification->created_at)->DiffInSeconds();

            return response()->json(response_formatter([
                'response_code' => translate('auth_login_401'),
                'message' => translate('Please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans(),
            ]), 401);
        }

        $otp = env('APP_ENV') != 'live' ? '123456' : rand(100000, 999999);
        $this->userVerification->updateOrCreate([
            'identity' => $request['identity'],
            'identity_type' => $request['identity_type']
        ], [
            'identity' => $request['identity'],
            'identity_type' => $request['identity_type'],
            'user_id' => null,
            'otp' => $otp,
            'expires_at' => now()->addMinute(3),
        ]);

        $response = 'error';
        //send otp
        if ($request['identity_type'] == 'phone') {
            $phonePermission = isNotificationActive(null, 'verification', 'sms', 'user');
            if ($phonePermission) {
                $publishedStatus = 0;
                $paymentPublishedStatus = config('get_payment_publish_status');
                if (isset($paymentPublishedStatus[0]['is_published'])) {
                    $publishedStatus = $paymentPublishedStatus[0]['is_published'];
                }
                if ($publishedStatus == 1) {
                    $response = SmsGateway::send($request['identity'], $otp);
                } else {
                    $response = SMS_gateway::send($request['identity'], $otp);
                }
            }

        } else if ($request['identity_type'] == 'email') {
            $emailPermission = isNotificationActive(null, 'verification', 'email', 'user');
            if ($emailPermission) {
                try {
                    Mail::to($request['identity'])->send(new OTPMail($otp));
                    $response = 'success';
                } catch (\Exception $exception) {
                    $response = 'error';
                }
            }
        } else {
            $response = 'error';
        }


        if ($response == 'success')
            return response()->json(response_formatter(DEFAULT_SENT_OTP_200), 200);
        else
            return response()->json(response_formatter(DEFAULT_SENT_OTP_FAILED_200), 200);
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function verify(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'identity' => 'required',
            'identity_type' => 'required',
            'otp' => 'required|max:6'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $maxOtpHit = business_config('maximum_otp_hit', 'otp_login_setup')->test_values ?? 5;
        $maxOtpHitTime = business_config('otp_resend_time', 'otp_login_setup')->test_values ?? 60;// seconds
        $tempBlockTime = business_config('temporary_otp_block_time', 'otp_login_setup')->test_values ?? 600; // seconds

        $verify = $this->userVerification->where(['identity' => $request['identity'], 'otp' => $request['otp']])->first();

        if (isset($verify)) {
            if (isset($verify->temp_block_time) && Carbon::parse($verify->temp_block_time)->DiffInSeconds() <= $tempBlockTime) {
                $time = $tempBlockTime - Carbon::parse($verify->temp_block_time)->DiffInSeconds();
                return response()->json(response_formatter([
                    'response_code' => translate('auth_login_401'),
                    'message' => translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans()
                ]), 403);

            }

            $this->userVerification->where(['identity' => $request['identity']])->delete();
            return response()->json(response_formatter(OTP_VERIFICATION_SUCCESS_200), 200);
        } else {
            $verificationData = $this->userVerification->where('identity', $request['identity'])->first();

            if (isset($verificationData)) {
                if (isset($verificationData->temp_block_time) && Carbon::parse($verificationData->temp_block_time)->DiffInSeconds() <= $tempBlockTime) {
                    $time = $tempBlockTime - Carbon::parse($verificationData->temp_block_time)->DiffInSeconds();
                    return response()->json(response_formatter([
                        'response_code' => translate('auth_login_401'),
                        'message' => translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans()
                    ]), 403);
                }

                if ($verificationData->is_temp_blocked == 1 && Carbon::parse($verificationData->updated_at)->DiffInSeconds() >= $maxOtpHitTime) {

                    $userVerify = $this->userVerification->where(['identity' => $request['identity']])->first();
                    if (!isset($userVerify)) {
                        $userVerify = $this->userVerification;
                    }
                    $userVerify->hit_count = 0;
                    $userVerify->is_temp_blocked = 0;
                    $userVerify->temp_block_time = null;
                    $userVerify->save();
                }


                if ($verificationData->hit_count >= $maxOtpHit && Carbon::parse($verificationData->updated_at)->DiffInSeconds() < $maxOtpHitTime && $verificationData->is_temp_blocked == 0) {

                    $userVerify = $this->userVerification->where(['identity' => $request['identity']])->first();
                    if (!isset($userVerify)) {
                        $userVerify = $this->userVerification;
                    }
                    $userVerify->is_temp_blocked = 1;
                    $userVerify->temp_block_time = now();
                    $userVerify->save();

                    $time = $tempBlockTime - Carbon::parse($verificationData->temp_block_time)->DiffInSeconds();
                    return response()->json(response_formatter([
                        'response_code' => translate('auth_login_401'),
                        'message' => translate('Too_many_attempts. please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans()
                    ]), 403);
                }

            }

            $userVerify = $this->userVerification->where(['identity' => $request['identity']])->first();
            if (!isset($userVerify)) {
                $userVerify = $this->userVerification;
            }
            $userVerify->hit_count += 1;
            $userVerify->temp_block_time = null;
            $userVerify->save();
        }

        return response()->json(response_formatter(OTP_VERIFICATION_FAIL_403), 403);
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'identity' => 'required',
            'identity_type' => 'required|in:email,phone',
            'otp' => 'required|max:6',

            'password' => 'required',
            'confirm_password' => 'required|same:confirm_password'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $userVerification = $this->userVerification
            ->where('identity_type', $request['identity_type'])
            ->where('identity', $request['identity'])
            ->where(['otp' => $request['otp']])
            ->where('expires_at', '>', now())
            ->first();

        if (isset($userVerification)) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        $this->user->where($request['identity_type'], $request['identity'])
            ->update([
                'password' => bcrypt(str_replace(' ', '', $request['password']))
            ]);

        $this->userVerification
            ->where('identity_type', $request['identity_type'])
            ->where('identity', $request['identity'])
            ->where(['otp' => $request['otp']])->delete();

        return response()->json(response_formatter(DEFAULT_PASSWORD_RESET_200), 200);
    }
}
