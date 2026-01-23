<?php

namespace Modules\Auth\Http\Controllers;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Modules\BusinessSettingsModule\Entities\PackageSubscriber;
use Modules\BusinessSettingsModule\Entities\PackageSubscriberFeature;
use Modules\BusinessSettingsModule\Entities\PackageSubscriberLimit;
use Modules\BusinessSettingsModule\Entities\SubscriptionPackage;
use Modules\BusinessSettingsModule\Entities\SubscriptionPackageFeature;
use Modules\BusinessSettingsModule\Entities\SubscriptionPackageLimit;
use Modules\PaymentModule\Traits\SubscriptionTrait;
use Modules\ProviderManagement\Emails\NewJoiningRequestMail;
use Modules\ProviderManagement\Entities\Provider;
use Modules\UserManagement\Entities\Serviceman;
use Modules\UserManagement\Entities\User;
use Modules\ZoneManagement\Entities\Zone;
use Carbon\Carbon;

class RegisterController extends Controller
{
    protected Provider $provider;
    protected User $owner;
    protected User $user;
    protected Serviceman $serviceman;
    protected Zone $zone;
    private SubscriptionPackage $subscriptionPackage;
    private PackageSubscriber $packageSubscriber;

    use SubscriptionTrait;

    public function __construct(Provider $provider, User $owner, User $user, Serviceman $serviceman, Zone $zone, SubscriptionPackage $subscriptionPackage, PackageSubscriber $packageSubscriber,)
    {
        $this->provider = $provider;
        $this->owner = $owner;
        $this->user = $user;
        $this->serviceman = $serviceman;
        $this->zone = $zone;
        $this->subscriptionPackage = $subscriptionPackage;
        $this->packageSubscriber = $packageSubscriber;
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function customerRegister(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'password' => 'required|min:8',
            'gender' => 'in:male,female,others',
            'confirm_password' => 'required|same:password',
            'profile_image' => 'image|mimes:jpeg,jpg,png,gif|max:10000',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 403);
        }

        if (User::where('email', $request['email'])->exists()) {
            return response()->json(response_formatter(DEFAULT_400, null, [["error_code" => "account_email", "message" => translate('Email already taken')]]), 400);
        }
        if (User::where('phone', $request['phone'])->exists()) {
            return response()->json(response_formatter(DEFAULT_400, null, [["error_code" => "account_phone", "message" => translate('Phone already taken')]]), 400);
        }

        $user = $this->user;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->profile_image = $request->has('profile_image') ? file_uploader('user/profile_image/', 'png', $request->profile_image) : 'default.png';
        $user->date_of_birth = $request->date_of_birth;
        $user->gender = $request->gender ?? 'male';
        $user->password = bcrypt($request->password);
        $user->user_type = 'customer';
        $user->is_active = 1;
        $user->save();

        return response()->json(response_formatter(REGISTRATION_200), 200);
    }


    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Application|Factory|View
     */
    public function providerSelfRegisterForm(Request $request): Application|Factory|View
    {
        $zones = $this->zone->get();
        $digitalPayment = (int)((business_config('digital_payment', 'service_setup'))->live_values ?? null);
        $commission = (int)((business_config('provider_commision', 'provider_config'))->live_values ?? null);
        $subscription = (int)((business_config('provider_subscription', 'provider_config'))->live_values ?? null);
        $duration = (int)((business_config('free_trial_period', 'subscription_Setting'))->live_values ?? null);
        $freeTrialStatus = (int)((business_config('free_trial_period', 'subscription_Setting'))->is_active ?? 0);
        $subscriptionPackages = $this->subscriptionPackage->OfStatus(1)->with('subscriptionPackageFeature', 'subscriptionPackageLimit')->get();
        $formattedPackages = $subscriptionPackages->map(function ($subscriptionPackage) {
            return formatSubscriptionPackage($subscriptionPackage, PACKAGE_FEATURES);
        });
        // dd($formattedPackages);

        $isPublished = 0;
        try {
            $fullData = include('Modules/Gateways/Addon/info.php');
            $isPublished = $fullData['is_published'] == 1 ? 1 : 0;
        } catch (\Exception $exception) {
        }

        $paymentGateways = collect($this->getPaymentMethods())
            ->filter(function ($query) use ($isPublished) {
                if (!$isPublished) {
                    return in_array($query['gateway'], array_column(PAYMENT_METHODS, 'key'));
                } else return $query;
            })->map(function ($query) {
                $query['label'] = ucwords(str_replace('_', ' ', $query['gateway']));
                return $query;
            })->values();
        return view('auth::provider-register', compact('zones','commission','subscription','formattedPackages','paymentGateways', 'duration', 'freeTrialStatus', 'digitalPayment'));
    }


    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return RedirectResponse
     */
    public function providerSelfRegister(Request $request)
    {
        $request->validate([
            // 'contact_person_name' => 'required',
            // 'contact_person_phone' => 'required',
            // 'contact_person_email' => 'required',

            'zone_id' => 'required|uuid',
            // 'password' => 'required|min:8',
            // 'confirm_password' => 'required|same:password',

            'company_name' => 'required',
            'company_phone' => 'required|max:255',
            'company_address' => 'required',
            'company_email' => 'required|email',
            'logo' => 'required|image|mimes:jpeg,jpg,png,gif|max:10000',

            // 'identity_type' => 'required|in:passport,driving_license,nid,trade_license',
            // 'identity_number' => 'required',
            // 'identity_images' => 'required|array',
            // 'identity_images.*' => 'image|mimes:jpeg,jpg,png,gif|max:2048',

            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        if (User::where('email', $request['company_email'])->exists()) {
            Toastr::error(translate('Email already taken'));
            return back();
        }
        if (User::where('phone', $request['company_phone'])->exists()) {
            Toastr::error(translate('Phone already taken'));
            return back();
        }

        if ($request->choose_business_plan == 'subscription_base'){
            $package = $this->subscriptionPackage->where('id',$request->selected_package_id)->ofStatus(1)->first();
            $vatPercentage      = (int)((business_config('subscription_vat', 'subscription_Setting'))->live_values ?? 0);
            if (!$package){
                Toastr::error(translate('Please Select valid plan'));
                return back();
            }

            $id                 = $package?->id;
            $price              = $package?->price;
            $name               = $package?->name;
            $vatAmount          = $package?->price * ($vatPercentage / 100);
            $vatWithPrice       = $price + $vatAmount;
        }

        $identity_images = [];
        foreach ($request->identity_images as $image) {
            $imageName = file_uploader('provider/identity/', 'png', $image);
            $identity_images[] = ['image'=>$imageName, 'storage'=> getDisk()];
        }

        $provider = $this->provider;
        $provider->company_name = $request->company_name;
        $provider->company_phone = $request->company_phone;
        $provider->company_email = $request->company_email;
        $provider->logo = file_uploader('provider/logo/', 'png', $request->file('logo'));
        $provider->company_address = $request->company_address;
        $provider->contact_person_name = $request->company_name;
        $provider->contact_person_phone =$request->company_phone;
        $provider->contact_person_email = $request->company_email;
        // $provider->contact_person_name = $request->contact_person_name;
        // $provider->contact_person_phone = $request->contact_person_phone;
        // $provider->contact_person_email = $request->contact_person_email;
        $provider->is_approved = 2;
        $provider->is_active = 0;
        $provider->zone_id = $request['zone_id'];
        $provider->coordinates = ['latitude' => $request['latitude'], 'longitude' => $request['longitude']];


        $owner = $this->owner;
        $owner->email = $request->company_email;
        $owner->phone = $request->company_phone;
        // $owner->identification_number = null;
        // $owner->identification_type = null;
        // $owner->identification_image = [];
        // $owner->password = bcrypt($request->password);
        $owner->user_type = 'provider-admin';
        $owner->is_active = 0;

        DB::transaction(function () use ($provider, $owner, $request) {
            $owner->save();
            $provider->user_id = $owner->id;
            $provider->save();
        });

        try {
            Mail::to(User::where('user_type', 'super-admin')->value('email'))->send(new NewJoiningRequestMail($provider));
        } catch (\Exception $exception) {
            info($exception);
        }

        if ($request->choose_business_plan == 'subscription_base') {
            $provider_id = $provider?->id;
            if ($request->free_trial_or_payment == 'free_trial') {
                $result = $this->handleFreeTrialPackageSubscription($id, $provider_id, $price, $name);
                if (!$result) {
                    Toastr::error(translate('Something error'));
                    return back();
                }
            }elseif ($request->free_trial_or_payment == 'payment') {
                $paymentUrl = url('payment/subscription') . '?' .
                    'provider_id=' . $provider_id . '&' .
                    'access_token=' . base64_encode($owner->id) . '&' .
                    'package_id=' . $id . '&' .
                    'amount=' . $vatWithPrice . '&' .
                    'name=' . $name . '&' .
                    'package_status=' . 'subscription_purchase' . '&' .
                    http_build_query($request->all());
                return redirect($paymentUrl);
            }
        }

        $user = $owner;
        $phone_verification = login_setup('phone_verification')?->value ?? 0;
        if ($phone_verification && !$user->is_phone_verified) {
            Toastr::error(translate('Verify your account'));
            return view('auth::verification.send-otp', compact('user'));
        }

        $email_verification = login_setup('email_verification')?->value ?? 0;
        if ($email_verification && !$user->is_email_verified) {
            Toastr::error(translate('Verify your account'));
            return view('auth::verification.send-otp', compact('user'));
        }

        Toastr::success(translate(PROVIDER_REGISTERED_200['message']));
        return redirect(route('provider.auth.login'));

        Toastr::success(translate(PROVIDER_REGISTERED_200['message']));
        return redirect(route('provider.auth.login'));
    }


    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function providerRegister(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'contact_person_name' => 'required',
            'contact_person_phone' => 'required',
            'contact_person_email' => 'required',

            'account_first_name' => 'required',
            'account_last_name' => 'required',
            'zone_id' => 'required|uuid',
            'account_email' => 'required|email',
            'account_phone' => 'required',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password',

            'company_name' => 'required',
            'company_phone' => 'required',
            'company_address' => 'required',
            'company_email' => 'required|email',
            'logo' => 'required|image|mimes:jpeg,jpg,png,gif|max:10000',

            'identity_type' => 'required|in:passport,driving_license,nid,trade_license,company_id',
            'identity_number' => 'required',
            'identity_images' => 'required|array',
            'identity_images.*' => 'image|mimes:jpeg,jpg,png,gif',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        if (User::where('email', $request['account_email'])->exists()) {
            return response()->json(response_formatter(DEFAULT_400, null, [["error_code" => "account_email", "message" => translate('Email already taken')]]), 400);
        }
        if (User::where('phone', $request['account_phone'])->exists()) {
            return response()->json(response_formatter(DEFAULT_400, null, [["error_code" => "account_phone", "message" => translate('Phone already taken')]]), 400);
        }

        $identity_images = [];
        foreach ($request->identity_images as $image) {
            $imageName = file_uploader('provider/identity/', 'png', $image);
            $identity_images[] = ['image'=>$imageName, 'storage'=> getDisk()];
        }

        $provider = $this->provider;
        $provider->company_name = $request->company_name;
        $provider->company_phone = $request->company_phone;
        $provider->company_email = $request->company_email;
        $provider->logo = file_uploader('provider/logo/', 'png', $request->file('logo'));
        $provider->company_address = $request->company_address;

        $provider->contact_person_name = $request->contact_person_name;
        $provider->contact_person_phone = $request->contact_person_phone;
        $provider->contact_person_email = $request->contact_person_email;
        $provider->is_approved = 2;
        $provider->is_active = 0;
        $provider->zone_id = $request['zone_id'];

        $owner = $this->owner;
        $owner->first_name = $request->account_first_name;
        $owner->last_name = $request->account_last_name;
        $owner->email = $request->account_email;
        $owner->phone = $request->account_phone;
        $owner->identification_number = $request->identity_number;
        $owner->identification_type = $request->identity_type;
        $owner->identification_image = $identity_images;
        $owner->password = bcrypt($request->password);
        $owner->user_type = 'provider-admin';
        $owner->is_active = 0;

        DB::transaction(function () use ($provider, $owner, $request) {
            $owner->save();
            $provider->user_id = $owner->id;
            $provider->save();
        });

        return response()->json(response_formatter(PROVIDER_STORE_200), 200);
    }


    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function user_verification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'identity' => 'required',
            'otp' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $data = DB::table('user_verifications')
            ->where('identity', $request['identity'])
            ->where(['otp' => $request['otp']])->first();

        if (isset($data)) {
            $this->user->whereIn('user_type', CUSTOMER_USER_TYPES)
                ->where('phone', $request['identity'])
                ->update([
                    'is_phone_verified' => 1
                ]);

            DB::table('user_verifications')
                ->where('identity', $request['identity'])
                ->where(['otp' => $request['otp']])->delete();

            return response()->json(response_formatter(DEFAULT_VERIFIED_200), 200);
        }

        return response()->json(response_formatter(DEFAULT_404), 200);
    }

    private function getPaymentMethods(): array
    {
        // Check if the addon_settings table exists
        if (!Schema::hasTable('addon_settings')) {
            return [];
        }

        $methods = DB::table('addon_settings')->where('settings_type', 'payment_config')->get();
        $env = env('APP_ENV') == 'live' ? 'live' : 'test';
        $credentials = $env . '_values';

        $data = [];
        foreach ($methods as $method) {
            $credentialsData = json_decode($method->$credentials);
            $additional_data = json_decode($method->additional_data);
            if ($credentialsData->status == 1) {
                $data[] = [
                    'gateway' => $method->key_name,
                    'gateway_image' => $additional_data?->gateway_image
                ];
            }
        }
        return $data;
    }

}
