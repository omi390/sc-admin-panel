<?php

namespace Modules\Auth\Http\Controllers;

use App\CentralLogics\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\CarbonInterval;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Modules\UserManagement\Entities\User;

class LoginController extends Controller
{
    private User $user;
    private array $validation_array = [
        'email_or_phone' => 'required',
        'password' => 'required',
    ];

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->middleware(function ($request, $next) {
            if ($request->user() !== null && in_array($request->user()->user_type, ADMIN_USER_TYPES)) {
                return redirect('admin/dashboard');
            } elseif ($request->user() !== null && in_array($request->user()->user_type, PROVIDER_USER_TYPES)) {
                return redirect('provider/dashboard');
            }
            return $next($request);
        })->except('logout');
    }

    /**
     * Display a listing of the resource.
     * @return Application|Factory|View
     */
    public function loginForm(): Application|Factory|View
    {
        return view('auth::admin-login');
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return RedirectResponse
     */
    public function adminLogin(Request $request): RedirectResponse
    {
        $request->validate($this->validation_array);

        $recaptcha = business_config('recaptcha', 'third_party');

        if (isset($recaptcha) && $recaptcha->is_active) {
            $request->validate([
                'g-recaptcha-response' => [
                    function ($attribute, $value, $fail) use ($recaptcha) {
                        $secret_key = $recaptcha->live_values['secret_key'];

                        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                            'secret' => $secret_key,
                            'response' => $value,
                            'remoteip' => \request()->ip(),
                        ]);

                        if (!$response->successful()) {
                            $fail(translate('ReCaptcha Failed'));
                        }
                    },
                ],
            ]);
        }


        $user = $this->user->where(['phone' => $request['email_or_phone']])
            ->orWhere('email', $request['email_or_phone'])
            ->ofType(ADMIN_USER_TYPES)->first();

        if (isset($user) && Hash::check($request['password'], $user['password'])) {
            if ($user->is_active && $user->roles->count() > 0 && $user->roles[0]->is_active || $user->user_type == 'super-admin') {
                if (auth()->attempt(['email' => $request->email_or_phone, 'password' => $request->password])) {
                    setcookie('admin_logged_in', 'true', time() + (86400 * 30), "/");
                    return redirect()->route('admin.dashboard');
                }
            }

            Toastr::error(translate(ACCOUNT_DISABLED['message']));
            return back();
        }

        Toastr::error(translate(AUTH_LOGIN_401['message']));
        return back();
    }

    /**
     * Display a listing of the resource.
     * @return Application|Factory|View
     */
    public function providerLoginForm(): Application|Factory|View
    {
        return view('auth::provider-login');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Foundation\Application|View|Factory|Redirector|Application|RedirectResponse
     * @throws Exception
     */
    public function providerLogin(Request $request): View|Factory|\Illuminate\Foundation\Application|Redirector|RedirectResponse|Application
    {
        $request->validate($this->validation_array);

        $recaptcha = business_config('recaptcha', 'third_party');

        if (isset($recaptcha) && $recaptcha->is_active) {
            $request->validate([
                'g-recaptcha-response' => [
                    function ($attribute, $value, $fail) use ($recaptcha) {
                        $secret_key = $recaptcha->live_values['secret_key'];

                        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                            'secret' => $secret_key,
                            'response' => $value,
                            'remoteip' => \request()->ip(),
                        ]);

                        if (!$response->successful()) {
                            $fail(translate('ReCaptcha Failed'));
                        }
                    },
                ],
            ]);
        }

        $user = $this->user
            ->with(['provider'])
            ->where(['phone' => $request['email_or_phone']])
            ->orWhere('email', $request['email_or_phone'])
            ->ofType(PROVIDER_USER_TYPES)
            ->first();

        if (!isset($user)) {
            Toastr::error(translate(AUTH_LOGIN_404['message']));
            return back();
        }

        if (!isset($user)) {
            Toastr::error(translate(AUTH_LOGIN_404['message']));
            return redirect(route('provider.auth.login'));
        }

        $temp_block_time = business_config('temporary_login_block_time', 'otp_login_setup')?->live_values ?? 600;

        if ($user->is_temp_blocked) {
            if (isset($user->temp_block_time) && Carbon::parse($user->temp_block_time)->DiffInSeconds() <= $temp_block_time) {
                $time = $temp_block_time - Carbon::parse($user->temp_block_time)->DiffInSeconds();
                Toastr::error(translate('Your account is temporarily blocked. Please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans());
                return redirect(route('provider.auth.login'));
            }

            $user->login_hit_count = 0;
            $user->is_temp_blocked = 0;
            $user->temp_block_time = null;
            $user->save();
        }

        if (!Hash::check($request['password'], $user['password'])) {
            self::update_user_hit_count($user);
            Toastr::error(translate(AUTH_LOGIN_401['message']));
            return redirect(route('provider.auth.login'));
        }

        $phone_verification = login_setup('phone_verification')?->value ?? 0;
        if ($phone_verification && !$user->is_phone_verified) {
            self::update_user_hit_count($user);
            Toastr::error(translate('Verify your account'));
            return view('auth::verification.send-otp', compact('user'));
        }

        $email_verification = login_setup('email_verification')?->value ?? 0;
        if ($email_verification && !$user->is_email_verified) {
            self::update_user_hit_count($user);
            Toastr::error(translate('Verify your account'));
            return view('auth::verification.send-otp', compact('user'));
        }

        if ($user->provider->is_approved == '2') {
            self::update_user_hit_count($user);
            Toastr::error(translate(PROVIDER_ACCOUNT_NOT_APPROVED['message']));
            return redirect(route('provider.auth.login'));
        }

        if ($user->provider->is_approved == '0') {
            self::update_user_hit_count($user);
            Toastr::error(translate(ACCOUNT_REJECTED['message']));
            return redirect(route('provider.auth.login'));
        }

        if (!$user->is_active || !$user->provider->is_active) {
            self::update_user_hit_count($user);
            Toastr::error(translate(ACCOUNT_DISABLED['message']));
            return redirect(route('provider.auth.login'));
        }

        if (isset($user->temp_block_time) && Carbon::parse($user->temp_block_time)->DiffInSeconds() <= $temp_block_time) {
            $time = $temp_block_time - Carbon::parse($user->temp_block_time)->DiffInSeconds();
            Toastr::error(translate('Try_again_after') . ' ' . CarbonInterval::seconds($time)->cascade()->forHumans());
            return redirect()->route('provider.dashboard');
        }

        if (auth()->attempt(['email' => $request->email_or_phone, 'password' => $request->password])) {
            return redirect()->route('provider.dashboard');
        } else {
            Toastr::error(translate(ACCESS_DENIED['message']));
            return back();
        }
    }

    public function update_user_hit_count($user): void
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
     */
    public function customerLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->validation_array);
        if ($validator->fails()) return response()->json(response_formatter(AUTH_LOGIN_403, null, error_processor($validator)), 403);

        $user = $this->user->where(['phone' => $request['email_or_phone']])
            ->orWhere('email', $request['email_or_phone'])
            ->ofType(CUSTOMER_USER_TYPES)
            ->first();

        if (isset($user) && Hash::check($request['password'], $user['password'])) {
            if ($user->is_active) {
                return response()->json(response_formatter(AUTH_LOGIN_200, self::authenticate($user, SERVICEMAN_APP_ACCESS)), 200);
            }
            return response()->json(response_formatter(DEFAULT_USER_DISABLED_401), 401);
        }

        return response()->json(response_formatter(AUTH_LOGIN_401), 401);
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function servicemanLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) return response()->json(response_formatter(AUTH_LOGIN_403, null, error_processor($validator)), 403);

        $user = $this->user->where(['phone' => $request['phone']])->ofType([SERVICEMAN_USER_TYPES])->first();

        if (isset($user) && Hash::check($request['password'], $user['password'])) {
            if ($user->is_active) {
                return response()->json(response_formatter(AUTH_LOGIN_200, self::authenticate($user, SERVICEMAN_APP_ACCESS)), 200);
            }
            return response()->json(response_formatter(DEFAULT_USER_DISABLED_401), 401);
        }

        return response()->json(response_formatter(AUTH_LOGIN_401), 401);
    }


    public function socialCustomerLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'unique_id' => 'required',
            'email' => 'required',
            'medium' => 'required|in:google,facebook',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $client = new Client();
        $token = $request['token'];
        $email = $request['email'];
        $unique_id = $request['unique_id'];

        try {
            if ($request['medium'] == 'google') {
                $res = $client->request('GET', 'https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $token);
                $data = json_decode($res->getBody()->getContents(), true);
            } elseif ($request['medium'] == 'facebook') {
                $res = $client->request('GET', 'https://graph.facebook.com/' . $unique_id . '?access_token=' . $token . '&&fields=name,email');
                $data = json_decode($res->getBody()->getContents(), true);
            }
        } catch (Exception $exception) {
            return response()->json(response_formatter(DEFAULT_401), 200);
        }

        if (strcmp($email, $data['email']) === 0) {
            $user = $this->user->where('email', $request['email'])
                ->ofType(CUSTOMER_USER_TYPES)
                ->first();

            if (!isset($user)) {
                $name = explode(' ', $data['name']);
                if (count($name) > 1) {
                    $fast_name = implode(" ", array_slice($name, 0, -1));
                    $last_name = end($name);
                } else {
                    $fast_name = implode(" ", $name);
                    $last_name = '';
                }

                $user = $this->user;
                $user->first_name = $fast_name;
                $user->last_name = $last_name;
                $user->email = $data['email'];
                $user->phone = null;
                $user->profile_image = 'def.png';
                $user->date_of_birth = date('y-m-d');
                $user->gender = 'others';
                $user->password = bcrypt($request->ip());
                $user->user_type = 'customer';
                $user->is_active = 1;
                $user->save();
            }

            return response()->json(response_formatter(AUTH_LOGIN_200, self::authenticate($user, CUSTOMER_PANEL_ACCESS)), 200);
        }

        return response()->json(response_formatter(DEFAULT_404), 401);
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
     * @return RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        if (auth()->user()) {
            $redirect_route = in_array(auth()->user()->user_type, ADMIN_USER_TYPES) ? 'admin.auth.login' : 'provider.auth.login';

            if (!in_array(auth()->user()->user_type, ADMIN_USER_TYPES)) {
                $request->session()->forget('modalClosed');
            }

            auth()->guard('web')->logout();
            return redirect()->route($redirect_route);
        }

        return redirect()->back();
    }
}
