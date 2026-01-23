<?php

namespace Modules\Auth\Http\Controllers\Api\V1;

use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Modules\BusinessSettingsModule\Entities\SubscriptionPackage;
use Modules\PaymentModule\Traits\SubscriptionTrait;
use Modules\PromotionManagement\Entities\PushNotification;
use Modules\PromotionManagement\Entities\PushNotificationUser;
use Modules\ProviderManagement\Emails\NewJoiningRequestMail;
use Modules\ProviderManagement\Entities\Provider;
use Modules\UserManagement\Entities\Serviceman;
use Modules\UserManagement\Entities\User;

class RegisterController extends Controller
{
    protected Provider $provider;
    protected User $owner;
    protected User $user;
    protected Serviceman $serviceman;
    private SubscriptionPackage $subscriptionPackage;

    use SubscriptionTrait;

    public function __construct(Provider $provider, User $owner, User $user, Serviceman $serviceman, SubscriptionPackage $subscriptionPackage)
    {
        $this->provider = $provider;
        $this->owner = $owner;
        $this->user = $user;
        $this->serviceman = $serviceman;
        $this->subscriptionPackage = $subscriptionPackage;
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
            return response()->json(response_formatter(DEFAULT_400, null, [["error_code" => "email", "message" => translate('Email already taken')]]), 400);
        }
        if (User::where('phone', $request['phone'])->exists()) {
            return response()->json(response_formatter(DEFAULT_400, null, [["error_code" => "phone", "message" => translate('Phone already taken')]]), 400);
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

        if ($request->has('referral_code')) {
            $customerReferralEarning = business_config('customer_referral_earning', 'customer_config')->live_values ?? 0;
            $amount = business_config('referral_value_per_currency_unit', 'customer_config')->live_values ?? 0;
            $userWhoRerreded = User::where('ref_code', $request['referral_code'])->first();

            if (is_null($userWhoRerreded)) {
                return response()->json(response_formatter(REFERRAL_CODE_INVALID_400), 404);
            }

            if ($customerReferralEarning == 1 && isset($userWhoRerreded)){

                referralEarningTransactionDuringRegistration($userWhoRerreded, $amount);

                $userRefund  = isNotificationActive(null, 'refer_earn', 'notification', 'user');
                $title = get_push_notification_message('referral_code_used', 'customer_notification', $user?->current_language_key);
                if ($title && $userWhoRerreded->fcm_token && $userRefund) {
                    device_notification($userWhoRerreded->fcm_token, $title, null, null, null, 'general', null, $userWhoRerreded->id);
                }

                $pushNotification = new PushNotification();
                $pushNotification->title = translate('Your Referral Code Has Been Used!');
                $pushNotification->description = translate("Congratulations! Your referral code was used by a new user. Get ready to earn rewards when they complete their first booking.");
                $pushNotification->to_users = ['customer'];
                $pushNotification->zone_ids = [config('zone_id') == null ? $request['zone_id'] : config('zone_id')];
                $pushNotification->is_active = 1;
                $pushNotification->cover_image = asset('/public/assets/admin/img/referral_2.png');
                $pushNotification->save();

                $pushNotificationUser = new PushNotificationUser();
                $pushNotificationUser->push_notification_id = $pushNotification->id;
                $pushNotificationUser->user_id = $userWhoRerreded->id;
                $pushNotificationUser->save();
            }
        }

        $user->referred_by = $userWhoRerreded->id ?? null;
        $user->save();

        return response()->json(response_formatter(REGISTRATION_200), 200);
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

            'account_first_name' => 'nullable|max:191',
            'account_last_name' => 'nullable|max:191',
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

            'latitude' => 'required',
            'longitude' => 'required',
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

        if ($request->choose_business_plan == 'subscription_base'){
            $package = $this->subscriptionPackage->where('id',$request->selected_package_id)->ofStatus(1)->first();
            $vatPercentage      = (int)((business_config('subscription_vat', 'subscription_Setting'))->live_values ?? 0);
            if (!$package){
                return response()->json(response_formatter(DEFAULT_400, null, [["error_code" => "package", "message" => translate('Please Select valid plan')]]), 400);
            }

            $id                 = $package->id;
            $price              = $package->price;
            $name               = $package->name;
            $vatAmount          = $package->price * ($vatPercentage / 100);
            $vatWithPrice       = $price + $vatAmount;
        }

        $identityImages = [];
        foreach ($request->identity_images as $image) {
            $imageName = file_uploader('provider/identity/', 'png', $image);
            $identityImages[] = ['image'=>$imageName, 'storage'=> getDisk()];
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
        $provider->coordinates = ['latitude' => $request['latitude'], 'longitude' => $request['longitude']];

        $owner = $this->owner;
        $owner->first_name = $request->account_first_name;
        $owner->last_name = $request->account_last_name;
        $owner->email = $request->account_email;
        $owner->phone = $request->account_phone;
        $owner->identification_number = $request->identity_number;
        $owner->identification_type = $request->identity_type;
        $owner->identification_image = $identityImages;
        $owner->password = bcrypt($request->password);
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
            $provider_id = $provider->id;
            if ($request->free_trial_or_payment == 'free_trial') {
                $result = $this->handleFreeTrialPackageSubscription($id, $provider_id, $price, $name);
                if (!$result){
                    return response()->json(response_formatter(DEFAULT_FAIL_200), 400);
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
                return response()->json(response_formatter(PROVIDER_STORE_200, $paymentUrl), 200);
            }
        }

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

}
