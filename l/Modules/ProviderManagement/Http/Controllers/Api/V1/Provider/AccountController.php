<?php

namespace Modules\ProviderManagement\Http\Controllers\Api\V1\Provider;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;
use Modules\BusinessSettingsModule\Entities\PackageSubscriber;
use Modules\BusinessSettingsModule\Entities\SubscriptionPackage;
use Modules\ProviderManagement\Entities\Provider;
use Modules\TransactionModule\Entities\Account;
use Modules\TransactionModule\Entities\Transaction;
use Modules\UserManagement\Entities\User;

class AccountController extends Controller
{
    private Provider $provider;
    private Account $account;
    private BusinessSettings $business_settings;
    private PackageSubscriber $packageSubscriber;
    private SubscriptionPackage $subscriptionPackage;
    private Transaction $transaction;

    public function __construct(Transaction $transaction, Provider $provider, Account $account, BusinessSettings $business_settings, PackageSubscriber $packageSubscriber, SubscriptionPackage $subscriptionPackage)
    {
        $this->provider = $provider;
        $this->account = $account;
        $this->business_settings = $business_settings;
        $this->packageSubscriber = $packageSubscriber;
        $this->subscriptionPackage = $subscriptionPackage;
        $this->transaction = $transaction;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function overview(Request $request): JsonResponse
    {
        $vat   = (int)((business_config('subscription_vat', 'subscription_Setting'))->live_values ?? 0);
        $provider = $this->provider->with('owner.account')->where('user_id', $request->user()->id)->first();
        $limitStatus = provider_warning_amount_calculate($provider->owner->account->account_payable,$provider->owner->account->account_receivable);
        $provider['cash_limit_status'] = $limitStatus == false ? 'available' : $limitStatus;
        $bookingOverview = DB::table('bookings')->where('provider_id', $request->user()->provider->id)
            ->select('booking_status', DB::raw('count(*) as total'))
            ->groupBy('booking_status')
            ->get();

        $promotionalCosts = $this->business_settings->where('settings_type', 'promotional_setup')->get();
        $promotionalCostPercentage = [];

        $data = $promotionalCosts->where('key_name', 'discount_cost_bearer')->first()->live_values;
        $promotionalCostPercentage['discount'] = $data['provider_percentage'];

        $data = $promotionalCosts->where('key_name', 'campaign_cost_bearer')->first()->live_values;
        $promotionalCostPercentage['campaign'] = $data['provider_percentage'];

        $data = $promotionalCosts->where('key_name', 'coupon_cost_bearer')->first()->live_values;
        $promotionalCostPercentage['coupon'] = $data['provider_percentage'];

        $transactionsCount = $this->transaction
            ->whereIn('trx_type', ['subscription_purchase', 'subscription_renew', 'subscription_shift', 'subscription_refund'])
            ->where('from_user_id', $provider->id)
            ->orWhere('to_user_id', $provider->id)->count();
        $packageSubscriber = $this->packageSubscriber->where('provider_id', $provider->id)
            ->with('feature', 'limits', 'package', 'payment')
            ->first();

        $formattedPackage = null;
        $renewal = null;
        if ($packageSubscriber) {
            $formattedPackage = apiPackageSubscriber($packageSubscriber, PACKAGE_FEATURES);

            $renewal = $this->subscriptionPackage->where('id', $packageSubscriber?->subscription_package_id)->first();
        }

        $totalSubscription = 0;
        $status = 'commission_base';

        if (is_array($formattedPackage) || is_object($formattedPackage)) {
            $numberOfUses = $formattedPackage['number_of_uses'] ?? ($formattedPackage->number_of_uses ?? 0);
            $totalSubscription = $numberOfUses;
            $status = $numberOfUses < 0 ? 'commission_base' : 'subscription_base';
        }

        $packageInfo = [
            'total_subscription' => $transactionsCount,
            'status' => $status,
            'subscribed_package_details' => $formattedPackage,
            'renewal_package_details' => $renewal,
            'applicable_vat' => $vat
        ];

        return response()->json(response_formatter(DEFAULT_200, [
            'provider_info' => $provider,
            'booking_overview' => $bookingOverview,
            'promotional_cost_percentage' => $promotionalCostPercentage,
            'subscription_info' => $packageInfo
        ]), 200);
    }

    /**
     * Show the form for editing the specified resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function accountEdit(Request $request): JsonResponse
    {
        $provider = $this->provider->with('owner')->find($request->user()->id);
        if (isset($provider)) {
            return response()->json(response_formatter(DEFAULT_200, $provider), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function accountUpdate(Request $request): JsonResponse
    {
        $provider = $this->provider->with('owner')->find($request->user()->id);
        $validator = Validator::make($request->all(), [
            'contact_person_name' => 'required',
            'contact_person_phone' => 'required',
            'contact_person_email' => 'required',

            'password' => 'string|min:8',
            'confirm_password' => 'same:password',
            'account_first_name' => 'required',
            'account_last_name' => 'required',
            'account_phone' => 'required',

            'company_name' => 'required',
            'company_phone' => 'required|unique:providers,company_phone,' . $provider->id . ',id',
            'company_address' => 'required',
            'logo' => 'image|mimes:jpeg,jpg,png,gif|max:10000',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        //email & phone check
        if (User::where('phone', $request['account_phone'])->where('id', '!=', $provider->user_id)->exists()) {
            return response()->json(response_formatter(DEFAULT_400, null, [["error_code"=>"account_phone","message"=>translate('Phone already taken')]]), 400);
        }

        $provider->company_name = $request->company_name;
        $provider->company_phone = $request->company_phone;
        if ($request->has('logo')) {
            $provider->logo = file_uploader('provider/logo/', 'png', $request->file('logo'));
        }
        $provider->company_address = $request->company_address;
        $provider->contact_person_name = $request->contact_person_name;
        $provider->contact_person_phone = $request->contact_person_phone;
        $provider->contact_person_email = $request->contact_person_email;

        $owner = $provider->owner()->first();
        $owner->first_name = $request->account_first_name;
        $owner->last_name = $request->account_last_name;
        $owner->phone = $request->account_phone;
        if ($request->has('password')) {
            $owner->password = bcrypt($request->password);
        }
        $owner->user_type = 'provider-admin';

        DB::transaction(function () use ($provider, $owner, $request) {
            $owner->save();
            $provider->save();
        });

        return response()->json(response_formatter(PROVIDER_STORE_200), 200);
    }

    /**
     * Show the form for editing the specified resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function commissionInfo(Request $request): JsonResponse
    {
        $provider = $this->provider->with('owner')->where('user_id',$request->user()->id)->first();
        if (isset($provider)) {
            return response()->json(response_formatter(DEFAULT_200, [
                'commission_status' => $provider['commission_status'],
                'commission_percentage' => $provider['commission_percentage']
            ]), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }
}
