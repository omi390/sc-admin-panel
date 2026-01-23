<?php

namespace Modules\Auth\Http\Controllers\Api\V1;

use Exception;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use Carbon\CarbonInterval;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\BusinessSettingsModule\Entities\LoginSetup;
use Modules\CustomerModule\Traits\CustomerTrait;
use Modules\UserManagement\Entities\User;

class LoginController extends Controller
{
    private User $user;
    private LoginSetup $loginSetup;
    use CustomerTrait;

    private array $validation_array = [
        'email_or_phone' => 'required',
        'password' => 'required',
    ];

    public function __construct(User $user, LoginSetup $loginSetup)
    {
        $this->user = $user;
        $this->loginSetup = $loginSetup;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function adminLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->validation_array);
        if ($validator->fails()) return response()->json(response_formatter(AUTH_LOGIN_403, null, error_processor($validator)), 403);

        $user = $this->user->where(['phone' => $request['email_or_phone']])
            ->orWhere('email', $request['email_or_phone'])
            ->ofType(ADMIN_USER_TYPES)->first();

        if (isset($user) && Hash::check($request['password'], $user['password'])) {
            if ($user->is_active && $user->roles->count() > 0 && $user->roles[0]->is_active || $user->user_type == 'super-admin') {
                return response()->json(response_formatter(AUTH_LOGIN_200, self::authenticate($user, ADMIN_PANEL_ACCESS)), 200);
            }
            return response()->json(response_formatter(ACCOUNT_DISABLED), 401);
        }
        return response()->json(response_formatter(AUTH_LOGIN_401), 401);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function providerLogin(Request $request)
    {
        $type = $request['type'];
        $validator = Validator::make($request->all(), $this->validation_array);
        if ($validator->fails()) return response()->json(response_formatter(AUTH_LOGIN_403, null, error_processor($validator)), 403);

        $user = $this->user->with('provider')
            ->where(['phone' => $request['email_or_phone']])
            ->orWhere('email', $request['email_or_phone'])
            ->ofType(['provider-admin'])->first();

        if (!isset($user)) {
            return response()->json(response_formatter(AUTH_LOGIN_404), 404);
        }

        $tempBlockTime = business_config('temporary_login_block_time', 'otp_login_setup')?->live_values ?? 600;

        if ($user->is_temp_blocked) {
            if (isset($user->temp_block_time) && Carbon::parse($user->temp_block_time)->DiffInSeconds() <= $tempBlockTime) {
                $time = $tempBlockTime - Carbon::parse($user->temp_block_time)->DiffInSeconds();
                return response()->json(response_formatter([
                    "response_code" => "auth_login_401",
                    "message" => translate('Your account is temporarily blocked. Please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans(),
                ]), 401);
            }

            $user->login_hit_count = 0;
            $user->is_temp_blocked = 0;
            $user->temp_block_time = null;
            $user->save();
        }

        $phoneVerification = login_setup('phone_verification')?->value ?? 0;
        if ($type == 'phone' && $phoneVerification && !$user->is_phone_verified) {
            self::updateUserHitCount($user);
            return response()->json(response_formatter(UNVERIFIED_PHONE), 401);
        }

        $emailVerification = login_setup('email_verification')?->value ?? 0;
        if ($type == 'email' && $emailVerification && !$user->is_email_verified) {
            self::updateUserHitCount($user);
            return response()->json(response_formatter(UNVERIFIED_EMAIL), 401);
        }

        if (!Hash::check($request['password'], $user['password'])) {
            self::updateUserHitCount($user);
            return response()->json(response_formatter(AUTH_LOGIN_401), 401);
        }

        if ($user->provider->is_approved == '2') {
            self::updateUserHitCount($user);
            return response()->json(response_formatter(PROVIDER_ACCOUNT_NOT_APPROVED), 401);
        }

        if (!$user->is_active) {
            self::updateUserHitCount($user);
            return response()->json(response_formatter(ACCOUNT_DISABLED), 401);
        }

        if ($user){
            $access = mobileAppCheck($user, 'mobile_app');
            if (!$access){
                return response()->json(response_formatter(SECTION_NOT_INCLUDE), 401);
            }
        }

        if (isset($user->temp_block_time) && Carbon::parse($user->temp_block_time)->DiffInSeconds() <= $tempBlockTime) {
            $time = $tempBlockTime - Carbon::parse($user->temp_block_time)->DiffInSeconds();
            return response()->json(response_formatter([
                "response_code" => "auth_login_401",
                "message" => translate('Try_again_after') . ' ' . CarbonInterval::seconds($time)->cascade()->forHumans()
            ]), 401);
        }

        return response()->json(response_formatter(AUTH_LOGIN_200, self::authenticate($user, PROVIDER_PANEL_ACCESS)), 200);
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function customerLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'guest_id' => 'required|uuid',
            'type' => 'required|in:phone,email'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }


        $type = $request['type'];
        $validator = Validator::make($request->all(), $this->validation_array);
        if ($validator->fails()) return response()->json(response_formatter(AUTH_LOGIN_403, null, error_processor($validator)), 403);

        $user = $this->user
            ->where(['phone' => $request['email_or_phone']])
            ->orWhere('email', $request['email_or_phone'])
            ->ofType(CUSTOMER_USER_TYPES)
            ->first();

        if (!isset($user)) {
            return response()->json(response_formatter(AUTH_LOGIN_404), 404);
        }

        $tempBlockTime = business_config('temporary_login_block_time', 'otp_login_setup')?->live_values ?? 600; // seconds

        if ($user->is_temp_blocked) {
            if (isset($user->temp_block_time) && Carbon::parse($user->temp_block_time)->DiffInSeconds() <= $tempBlockTime) {
                $time = $tempBlockTime - Carbon::parse($user->temp_block_time)->DiffInSeconds();
                return response()->json(response_formatter([
                    "response_code" => "auth_login_401",
                    "message" => translate('Your account is temporarily blocked. Please try again after ') . CarbonInterval::seconds($time)->cascade()->forHumans(),
                ]), 401);
            }

            $user->login_hit_count = 0;
            $user->is_temp_blocked = 0;
            $user->temp_block_time = null;
            $user->save();
        }

        if (!Hash::check($request['password'], $user['password'])) {
            self::updateUserHitCount($user);
            return response()->json(response_formatter(AUTH_LOGIN_401), 401);
        }

        $phoneVerification = login_setup('phone_verification')?->value ?? 0;
        if ($type == 'phone' && $phoneVerification && !$user->is_phone_verified) {
            self::updateUserHitCount($user);
            return response()->json(response_formatter(UNVERIFIED_PHONE), 401);
        }

        $emailVerification = login_setup('email_verification')?->value ?? 0;
        if ($type == 'email' && $emailVerification && !$user->is_email_verified) {
            self::updateUserHitCount($user);
            return response()->json(response_formatter(UNVERIFIED_EMAIL), 401);
        }

        if (!$user->is_active) {
            self::updateUserHitCount($user);
            return response()->json(response_formatter(ACCOUNT_DISABLED), 401);
        }

        if (isset($user->temp_block_time) && Carbon::parse($user->temp_block_time)->DiffInSeconds() <= $tempBlockTime) {
            $time = $tempBlockTime - Carbon::parse($user->temp_block_time)->DiffInSeconds();
            return response()->json(response_formatter([
                "response_code" => "auth_login_401",
                "message" => translate('Try_again_after') . ' ' . CarbonInterval::seconds($time)->cascade()->forHumans()
            ]), 401);
        }

        $this->updateAddressAndCartUser($user->id, $request['guest_id']);
        return response()->json(response_formatter(AUTH_LOGIN_200, self::authenticate($user, CUSTOMER_PANEL_ACCESS)), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function customerLogOut(Request $request): JsonResponse
    {
        if (!auth()->user()) {
            return response()->json(response_formatter(ACCESS_DENIED), 200);
        }

        $request->user()->token()->revoke();
        return response()->json(response_formatter(AUTH_LOGOUT_200), 200);
    }

    public function updateUserHitCount($user): void
    {
        $max_login_hit = business_config('maximum_login_hit', 'otp_login_setup')?->live_values ?? 5;

        $user->login_hit_count += 1;
        if ($user->login_hit_count >= $max_login_hit) {
            $user->is_temp_blocked = 1;
            $user->temp_block_time = now();
        }
        $user->save();
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function servicemanLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) return response()->json(response_formatter(AUTH_LOGIN_403, null, error_processor($validator)), 403);

        $user = $this->user
            ->where(['phone' => $request['phone']])
            ->ofType([SERVICEMAN_USER_TYPES])
            ->first();

        if (!isset($user)) {
            return response()->json(response_formatter(AUTH_LOGIN_404), 404);
        }

        $temp_block_time = business_config('temporary_login_block_time', 'otp_login_setup')?->live_values ?? 600; // seconds

        if ($user->is_temp_blocked) {
            if (isset($user->temp_block_time) && Carbon::parse($user->temp_block_time)->DiffInSeconds() <= $temp_block_time) {
                $time = $temp_block_time - Carbon::parse($user->temp_block_time)->DiffInSeconds();
                return response()->json(response_formatter([
                    "response_code" => "auth_login_401",
                    "message" => translate('Your account is temporarily blocked. Please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans(),
                ]), 401);
            }

            $user->login_hit_count = 0;
            $user->is_temp_blocked = 0;
            $user->temp_block_time = null;
            $user->save();
        }

        if (!Hash::check($request['password'], $user['password'])) {
            self::updateUserHitCount($user);
            return response()->json(response_formatter(AUTH_LOGIN_401), 401);
        }

        if (!$user->is_active) {
            self::updateUserHitCount($user);
            return response()->json(response_formatter(ACCOUNT_DISABLED_SERVICEMAN), 401);
        }

        if (isset($user->temp_block_time) && Carbon::parse($user->temp_block_time)->DiffInSeconds() <= $temp_block_time) {
            $time = $temp_block_time - Carbon::parse($user->temp_block_time)->DiffInSeconds();
            return response()->json(response_formatter([
                "response_code" => "auth_login_401",
                "message" => translate('Try_again_after') . ' ' . CarbonInterval::seconds($time)->cascade()->forHumans()
            ]), 401);
        }

        return response()->json(response_formatter(AUTH_LOGIN_200, self::authenticate($user, SERVICEMAN_APP_ACCESS)), 200);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function customerSocialLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'unique_id' => $request['medium'] != 'google' ? 'required' : 'nullable', //facebook, apple
            'email' => 'required_if:medium,google,facebook',
            'medium' => 'required|in:google,facebook,apple',
            'guest_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $client = new Client();
        $token = $request['token'];
        $email = $request['email'];
        $unique_id = $request['unique_id'];
        $appleEmail = null;

        try {
            if ($request['medium'] == 'google') {
                $res = $client->request('GET', 'https://www.googleapis.com/oauth2/v3/userinfo?access_token=' . $token);
                $data = json_decode($res->getBody()->getContents(), true);
            } elseif ($request['medium'] == 'facebook') {
                $res = $client->request('GET', 'https://graph.facebook.com/' . $unique_id . '?access_token=' . $token . '&&fields=name,email');
                $data = json_decode($res->getBody()->getContents(), true);
            } elseif ($request['medium'] == 'apple') {
                $apple_login = (business_config('apple_login', 'third_party'))->live_values;
                $teamId = $apple_login['team_id'];
                $keyId = $apple_login['key_id'];
                $sub = $apple_login['client_id'];
                $aud = 'https://appleid.apple.com';
                $iat = strtotime('now');
                $exp = strtotime('+60days');
                $keyContent = file_get_contents('storage/app/public/apple-login/' . $apple_login['service_file']);
                $token = JWT::encode([
                    'iss' => $teamId,
                    'iat' => $iat,
                    'exp' => $exp,
                    'aud' => $aud,
                    'sub' => $sub,
                ], $keyContent, 'ES256', $keyId);

                $redirect_uri = $apple_login['redirect_url'] ?? 'www.example.com/apple-callback';

                $res = Http::asForm()->post('https://appleid.apple.com/auth/token', [
                    'grant_type' => 'authorization_code',
                    'code' => $unique_id,
                    'redirect_uri' => $redirect_uri,
                    'client_id' => $sub,
                    'client_secret' => $token,
                ]);

                $claims = explode('.', $res['id_token'])[1];
                $data = json_decode(base64_decode($claims), true);
            }
        } catch (Exception $exception) {
            return response()->json(response_formatter(DEFAULT_401), 200);
        }

        if (!isset($claims)) {
            if (strcmp($email, $data['email']) != 0 && (!isset($data['id']) && !isset($data['kid']))) {
                return response()->json(['error' => translate('messages.email_does_not_match')], 403);
            }
        }

        if ($request['medium'] == 'apple'){
            $appleEmail = $data['email'];
        }

        $user = $this->user->where('email', $data['email'])
            ->ofType(CUSTOMER_USER_TYPES)
            ->first();

        $temporaryToken = Str::random(40);

        if (!$user){
            return response()->json(response_formatter(AUTH_LOGIN_200, ['temporary_token' => $temporaryToken, 'status' => false, 'email' => $appleEmail], 200));
        }

        if ($user->is_email_verified == 1){
            return response()->json(response_formatter(AUTH_LOGIN_200, self::authenticate($user, CUSTOMER_PANEL_ACCESS)), 200);
        }else{
            return response()->json(response_formatter(AUTH_LOGIN_200, ['user' => $user, 'status' => false], 200));
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function existingAccountCheck(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'user_response' => 'required|in:0,1',
            'medium' => 'required|in:google,facebook,apple',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $user = $this->user->where('email', $request['email'])->first();

        $temporaryToken = Str::random(40);
        if (!$user) {
            return response()->json(response_formatter(AUTH_LOGIN_200, ['temporary_token' => $temporaryToken, 'status' => false], 200));
        }

        if ($request['user_response'] == 1) {
            $user->is_email_verified = 1;
            $user->save();

            return response()->json(response_formatter(AUTH_LOGIN_200, self::authenticate($user, CUSTOMER_PANEL_ACCESS)), 200);
        }

        $user->email = null;
        $user->is_email_verified = 0;
        $user->save();

        return response()->json(response_formatter(AUTH_LOGIN_200, ['temporary_token' => $temporaryToken, 'status' => false], 200));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function registrationWithSocialMedia(Request $request): JsonResponse
    {

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $existingUser = $this->user->where(function ($query) use ($request) {
            $query->where('phone', $request['phone'])
                ->orWhere('email', $request['email']);
        })->first();

        if ($existingUser) {
            if ($existingUser->phone === $request['phone']) {
                return response()->json(response_formatter(ALREADY_USE_NUMBER_ANOTHER_ACCOUNT), 403);
            }

            if ($existingUser->email === $request['email']) {
                return response()->json(response_formatter(ALREADY_USE_EMAIL_ANOTHER_ACCOUNT), 403);
            }
        }

        $temporaryToken = Str::random(40);

        $user = $this->user->create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => bcrypt(rand(11111111, 99999999)),
            'language_code' => $request->header('X-localization') ?? 'en',
            'is_email_verified' => 1,
            'is_active' => 1,
        ]);

        $phoneVerificationStatus = (int) $this->loginSetup->where(['key' => 'phone_verification'])?->first()->value ?? 0;
        if ($phoneVerificationStatus){
            return response()->json(response_formatter(AUTH_LOGIN_200, ['temporary_token' => $temporaryToken, 'status' => false], 200));
        }

        return response()->json(response_formatter(AUTH_LOGIN_200, self::authenticate($user, CUSTOMER_PANEL_ACCESS)), 200);
    }

    /**
     * Show the form for creating a new resource.
     * @param $user
     * @param $access_type
     * @return array
     */
    protected function authenticate($user, $access_type): array
    {
        return ['token' => $user->createToken($access_type)->accessToken, 'is_active' => $user['is_active']];
    }

    /**
     * Show the form for creating a new resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        if ($request->user() !== null) {
            $request->user()->token()->revoke();
        }
        return response()->json(response_formatter(AUTH_LOGOUT_200), 200);
    }
}
