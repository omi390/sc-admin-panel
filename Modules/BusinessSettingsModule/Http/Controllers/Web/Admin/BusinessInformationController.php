<?php

namespace Modules\BusinessSettingsModule\Http\Controllers\Web\Admin;

use App\Mail\MaintenanceModeStartEmail;
use App\Traits\ActivationClass;
use App\Traits\FileManagerTrait;
use App\Traits\MaintenanceModeTrait;
use App\Traits\UnloadedHelpers;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Composer\Package\Archiver\ZipArchiver;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Madnest\Madzipper\Facades\Madzipper;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;
use Modules\BusinessSettingsModule\Entities\DataSetting;
use Modules\BusinessSettingsModule\Entities\NotificationSetup;
use Modules\BusinessSettingsModule\Entities\Translation;
use Modules\ProviderManagement\Entities\Provider;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use ZipArchive;

class BusinessInformationController extends Controller
{
    use ActivationClass;
    use FileManagerTrait;
    use AuthorizesRequests;
    use UnloadedHelpers;
    use MaintenanceModeTrait;

    private BusinessSettings $businessSetting;
    private DataSetting $dataSetting;
    private NotificationSetup $notificationSetup;

    public function __construct(BusinessSettings $businessSetting, DataSetting $dataSetting, NotificationSetup $notificationSetup)
    {
        $this->businessSetting = $businessSetting;
        $this->dataSetting = $dataSetting;
        $this->notificationSetup = $notificationSetup;

        if (request()->isMethod('get')) {
            $response = $this->actch();
            $data = json_decode($response->getContent(), true);
            if (!$data['active']) {
                return Redirect::away(base64_decode('aHR0cHM6Ly82YW10ZWNoLmNvbS9zb2Z0d2FyZS1hY3RpdmF0aW9u'))->send();
            }
        }
    }

    public function maintenanceModeSetup(Request $request): RedirectResponse
    {
        $maintenanceMode = $request->has('maintenance_mode') ? 1 : 0;

        $this->businessSetting->updateOrInsert(
            ['key_name' => 'maintenance_mode', 'settings_type' => 'maintenance_mode'],
            ['live_values' => $maintenanceMode]
        );

        $systems = ['mobile_app', 'web_app', 'provider_panel', 'provider_app', 'serviceman_app'];
        $selectedSystems = array_filter($systems, fn($system) => $request->has($system));

        $this->businessSetting->updateOrInsert(
            ['key_name' => 'maintenance_system_setup', 'settings_type' => 'maintenance_mode'],
            ['live_values' => json_encode($selectedSystems)]
        );

        $this->businessSetting->updateOrInsert(
            ['key_name' => 'maintenance_duration_setup', 'settings_type' => 'maintenance_mode'],
            [
                'live_values' => json_encode([
                    'maintenance_duration' => $request['maintenance_duration'],
                    'start_date' => $request['start_date'] ?? null,
                    'end_date' => $request['end_date'] ?? null,
                ]),
            ]
        );

        $this->businessSetting->updateOrInsert(
            ['key_name' => 'maintenance_message_setup', 'settings_type' => 'maintenance_mode'],
            [
                'live_values' => json_encode([
                    'business_number' => $request->has('business_number') ? 1 : 0,
                    'business_email' => $request->has('business_email') ? 1 : 0,
                    'maintenance_message' => $request['maintenance_message'],
                    'message_body' => $request['message_body']
                ]),
            ]
        );

        if ($maintenanceMode) {
            $providers = Provider::OfStatus(1)->OfApproval(1)->get();
            $providers->each(function ($provider) {
                $email = optional($provider)->company_email;
                $providerEmail = isNotificationActive($provider?->id, 'system_update', 'email', 'provider');
                if ($email && $providerEmail) {
                    try {
                        Mail::to($email)->send(new MaintenanceModeStartEmail($provider));
                    } catch (\Exception $exception) {
                    }
                }
            });

            $this->maintenanceModeNotification('provider-admin');
            $this->maintenanceModeNotification('provider-serviceman');
            $this->maintenanceModeNotification('customer');
        }

        $isProvider = 0;
        $selectedMaintenanceSystem = ((business_config('maintenance_system_setup', 'maintenance_mode'))?->live_values) ?? [];
        $selectedMaintenanceDuration = ((business_config('maintenance_duration_setup', 'maintenance_mode'))?->live_values);

        $maintenanceSystem = [];
        foreach ($systems as $system) {
            $maintenanceSystem[$system] = in_array($system, $selectedMaintenanceSystem) ? 1 : 0;
        }

        if (isset($maintenanceSystem['provider_panel']) && $maintenanceSystem['provider_panel'] === 1) {
            $isProvider = 1;
        }

        $maintenance = [
            'status' => $maintenanceMode,
            'start_date' => $request->input('start_date', null),
            'end_date' => $request->input('end_date', null),
            'provider' => $isProvider,
            'maintenance_duration' => $selectedMaintenanceDuration['maintenance_duration'],
        ];

        Cache::put('maintenance', $maintenance, now()->addYears(1));

        Toastr::success(translate('Maintenance mode settings updated!'));
        return back();
    }

    public function maintenanceModeStatusUpdate(Request $request): JsonResponse
    {
        $maintenance = $this->businessSetting->where('key_name', 'maintenance_mode')->where('settings_type', 'maintenance_mode')->first();
        $newStatus = !$maintenance->live_values;

        $this->businessSetting->where('key_name', 'maintenance_mode')->where('settings_type', 'maintenance_mode')->update(['live_values' => $newStatus]);
        Cache::forget('maintenance');

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    public function ajaxCurrencyChange(Request $request): JsonResponse
    {
        $currency_check = checkCurrency($request['currency']);
        if( $currency_check !== true ){
            return response()->json(['data'=> translate($currency_check) ],200);
        }

        return response()->json([],200);
    }

    /**
     * Display a listing of the resource.
     */
    public function businessInformationGet(Request $request)
    {
        $this->authorize('business_view');
        $config = 0;
        $webPage = $request->has('web_page') ? $request['web_page'] : 'business_setup';
        $businessLogoFullPath = '';
        $businessFaviconFullPath = '';

        if ($webPage == 'business_setup') {
            $dataValues = $this->businessSetting->where('settings_type', 'business_information')->get();
            $businessLogoFullPath = getBusinessSettingsImageFullPath(key: 'business_logo', settingType: 'business_information', path: 'business/',  defaultPath : 'public/assets/admin-module/img/media/banner-upload-file.png');
            $businessFaviconFullPath  = getBusinessSettingsImageFullPath(key: 'business_favicon', settingType: 'business_information', path: 'business/',  defaultPath : 'public/assets/admin-module/img/media/upload-file.png');
        } elseif ($webPage == 'service_setup') {
            $dataValues = $this->businessSetting->where('settings_type', 'service_setup')->get();
        } elseif ($webPage == 'providers') {
            $dataValues = $this->businessSetting->where('settings_type', 'provider_config')->get();
        } elseif ($webPage == 'customers') {
            $dataValues = $this->businessSetting->where('settings_type', 'customer_config')->get();
        } elseif ($webPage == 'servicemen') {
            $dataValues = $this->businessSetting->where('settings_type', 'serviceman_config')->get();
        } elseif ($webPage == 'promotional_setup') {
            $dataValues = $this->businessSetting->where('settings_type', 'promotional_setup')->get();
        } elseif ($webPage == 'bookings') {
            $dataValues = $this->businessSetting->whereIn('settings_type', ['booking_setup', 'bidding_system'])->get();
        }

        return view('businesssettingsmodule::admin.business', compact('dataValues', 'webPage', 'businessLogoFullPath', 'businessFaviconFullPath','config'));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException|AuthorizationException
     */
    public function businessInformationSet(Request $request): JsonResponse
    {
        $this->authorize('business_update');
        if (!$request->has('phone_number_visibility_for_chatting')) {
            $request['phone_number_visibility_for_chatting'] = '0';
        }
        if (!$request->has('direct_provider_booking')) {
            $request['direct_provider_booking'] = '0';
        }
        if (!$request->has('booking_notification')) {
            $request['booking_notification'] = '0';
        }

        $validator = Validator::make($request->all(), [
            'business_name' => 'required',
            'business_phone' => 'required',
            'business_email' => 'required',
            'business_address' => 'required',
            'country_code' => 'required',
            'language_code' => 'array',
            'currency_code' => 'required',
            'currency_symbol_position' => 'required',
            'currency_decimal_point' => 'required',
            'time_zone' => 'required',
            'time_format' => '',
            'business_favicon' => 'image|mimes:jpeg,jpg,png,gif|max:10000',
            'business_logo' => 'image|mimes:jpeg,jpg,png,gif|max:10000',
            'default_commission' => 'required',
            'pagination_limit' => 'required',
            'footer_text' => 'required',
            'cookies_text' => 'required',
            'minimum_withdraw_amount' => 'required|numeric|gte:0',
            'maximum_withdraw_amount' => 'required|numeric|gt:minimum_withdraw_amount',
            'phone_number_visibility_for_chatting' => 'required|in:0,1',
            'direct_provider_booking' => 'required|in:0,1',
            'booking_notification_type' => 'required',
            'booking_notification' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        foreach ($validator->validated() as $key => $value) {

            if ($key == 'business_logo') {
                $file = $this->businessSetting->where('key_name', 'business_logo')->first();
                $value = file_uploader('business/', 'png', $request->file('business_logo'), !empty($file->live_values) ? $file->live_values : '');

                $storageType = getDisk();
                if($value && $storageType != 'public'){
                    saveBusinessImageDataToStorage(model: $file, modelColumn : 'business_logo', storageType : $storageType);
                }
            }
            if ($key == 'business_favicon') {
                $file = $this->businessSetting->where('key_name', 'business_favicon')->first();
                $value = file_uploader('business/', 'png', $request->file('business_favicon'), !empty($file->live_values) ? $file->live_values : '');

                $storageType = getDisk();
                if($value && $storageType != 'public'){
                    saveBusinessImageDataToStorage(model: $file, modelColumn : 'business_favicon', storageType : $storageType);
                }
            }

            $this->businessSetting->updateOrCreate(['key_name' => $key], [
                'key_name' => $key,
                'live_values' => $value,
                'test_values' => $value,
                'settings_type' => 'business_information',
                'mode' => 'live',
                'is_active' => 1,
            ]);
        }

        session()->forget('pagination_limit');

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

    /**
     * Display a listing of the resource.
     * @return Application|Factory|\Illuminate\Foundation\Application|View
     */
    public function notificationChannel(Request $request): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $searchTerm = $request->input('search');
        $notificationType = $request->get('notification_type', 'user');
        $notificationSetup = $this->notificationSetup
            ->where('user_type', $notificationType)
            ->where(function($query) use ($searchTerm) {
                $query->where('title', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('sub_title', 'LIKE', "%{$searchTerm}%");
            })
            ->get();
        return view('businesssettingsmodule::admin.notification-channel', compact('notificationSetup', 'notificationType', 'searchTerm'));
    }


    public function updateStatus(Request $request)
    {
        $notification = $this->notificationSetup->where('id', $request->id)->first();

        if ($notification) {
            $data = json_decode($notification->value);

            if ($request->has('email')) {
                $data->email = $request->email;
            }

            if ($request->has('notification')) {
                $data->notification = $request->notification;
            }

            if ($request->has('sms')) {
                $data->sms = $request->sms;
            }

            $notification->value = json_encode($data);
            $notification->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException|AuthorizationException
     */
    public function setBiddingSystem(Request $request): JsonResponse
    {
        $this->authorize('business_update');
        if (!$request->has('bidding_status')) {
            $request['bidding_status'] = '0';
        }
        if (!$request->has('bid_offers_visibility_for_providers')) {
            $request['bid_offers_visibility_for_providers'] = '0';
        }

        $validator = Validator::make($request->all(), [
            'bidding_status' => 'required|in:0,1',
            'bidding_post_validity' => 'required',
            'bid_offers_visibility_for_providers' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        foreach ($validator->validated() as $key => $value) {
            $this->businessSetting->updateOrCreate(['key_name' => $key], [
                'key_name' => $key,
                'live_values' => $value,
                'test_values' => $value,
                'settings_type' => 'bidding_system',
                'mode' => 'live',
                'is_active' => 1,
            ]);
        }

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException|AuthorizationException
     */
    public function bookingSetupSet(Request $request): JsonResponse
    {
        $this->authorize('business_update');
        collect(['booking_otp', 'service_complete_photo_evidence', 'bidding_status', 'bid_offers_visibility_for_providers', 'booking_additional_charge', 'instant_booking', 'schedule_booking_time_restriction', 'schedule_booking'])
            ->each(fn($item, $key) => $request[$item] = $request->has($item) ? (int)$request[$item] : 0);

        $validator = Validator::make($request->all(), [
            'booking_otp' => 'required|in:0,1',
            'booking_additional_charge' => 'required|in:0,1',
            'service_complete_photo_evidence' => 'required|in:0,1',
            'min_booking_amount' => 'required|numeric|gte:0',
            'max_booking_amount' => 'required|numeric|gt:min_booking_amount',
            'additional_charge_label_name' => 'required|string',
            'additional_charge_fee_amount' => 'required|numeric|min:0',
            'instant_booking' => 'required|in:0,1',
            'schedule_booking_time_restriction' => 'required|in:0,1',
            'schedule_booking' => 'required|in:0,1',
            'advanced_booking_restriction_value' => 'required',
            'advanced_booking_restriction_type' => 'required|in:day,hour',

            //bidding
            'bidding_post_validity' => 'required|numeric|gt:0',
            'bid_offers_visibility_for_providers' => 'required|in:0,1',
            'bidding_status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        foreach ($validator->validated() as $key => $value) {
            $settings_type = in_array($key, ['bidding_post_validity', 'bid_offers_visibility_for_providers', 'bidding_status']) ? 'bidding_system' : 'booking_setup';

            $this->businessSetting->updateOrCreate(['key_name' => $key], [
                'key_name' => $key,
                'live_values' => $value,
                'test_values' => $value,
                'settings_type' => $settings_type,
                'mode' => 'live',
                'is_active' => 1,
            ]);
        }

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     * @throws ValidationException|AuthorizationException
     */
    public function serviceSetup(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('business_update');
        collect([
            'cash_after_service',
            'digital_payment', 'partial_payment', 'offline_payment', 'guest_checkout'
        ])->each(fn($item, $key) => $request[$item] = $request->has($item) ? (int)$request[$item] : 0);
        $validator = Validator::make($request->all(), [
            'cash_after_service' => 'required|in:1,0',
            'digital_payment' => 'required|in:1,0',
            'partial_payment' => 'required|in:1,0',
            'partial_payment_combinator' => 'required|in:digital_payment,cash_after_service,offline_payment,all',
            'offline_payment' => 'required|in:1,0',
            'guest_checkout' => 'required|in:1,0',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        foreach ($validator->validated() as $key => $value) {
            $this->businessSetting->updateOrCreate(['key_name' => $key], [
                'key_name' => $key,
                'live_values' => $value,
                'test_values' => $value,
                'settings_type' => 'service_setup',
                'mode' => 'live',
                'is_active' => 1,
            ]);
        }

        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();
    }

    /**
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     * @throws ValidationException|AuthorizationException
     */
    public function servicemen(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('business_update');
        collect(['serviceman_can_edit_booking', 'serviceman_can_cancel_booking'])->each(fn($item, $key) => $request[$item] = $request->has($item) ? (int)$request[$item] : 0);
        $validator = Validator::make($request->all(), [
            'serviceman_can_edit_booking' => 'required|in:0,1',
            'serviceman_can_cancel_booking' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        foreach ($validator->validated() as $key => $value) {
            $this->businessSetting->updateOrCreate(['key_name' => $key], [
                'key_name' => $key,
                'live_values' => $value,
                'test_values' => $value,
                'settings_type' => 'serviceman_config',
                'mode' => 'live',
                'is_active' => 1,
            ]);
        }

        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();
    }

    /**
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     * @throws ValidationException|AuthorizationException
     */
    public function customerSetup(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('business_update');
        collect(['customer_wallet', 'customer_loyalty_point', 'customer_referral_earning', 'add_to_fund_wallet', 'referral_based_new_user_discount'])->each(fn($item, $key) => $request[$item] = $request->has($item) ? (int)$request[$item] : 0);
        $validator = $request->validate([
            'customer_wallet' => 'required|in:0,1',
            'add_to_fund_wallet' => 'required|in:0,1',
            'customer_loyalty_point' => 'required|in:0,1',
            'customer_referral_earning' => 'required|in:0,1',
            'referral_based_new_user_discount' => 'required|in:0,1',
            'loyalty_point_value_per_currency_unit' => 'required',
            'loyalty_point_percentage_per_booking' => 'required',
            'min_loyalty_point_to_transfer' => 'required',
            'referral_value_per_currency_unit' => 'required',
            'referral_discount_type' => 'required|in:flat,percentage',
            'referral_discount_amount' => 'required|numeric|min:1',
            'referral_discount_validity_type' => 'required|in:day,month',
            'referral_discount_validity' => 'required|min:1',
        ]);

        foreach ($validator as $key => $value) {
            $this->businessSetting->updateOrCreate(['key_name' => $key], [
                'key_name' => $key,
                'live_values' => $value,
                'test_values' => $value,
                'settings_type' => 'customer_config',
                'mode' => 'live',
                'is_active' => 1,
            ]);
        }

        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();
    }

    /**
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     * @throws ValidationException|AuthorizationException
     */
    public function providerSetup(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('business_update');
        collect(['provider_can_cancel_booking', 'provider_can_edit_booking', 'provider_self_registration', 'suspend_on_exceed_cash_limit_provider', 'provider_self_delete', 'provider_subscription', 'provider_commision', 'provider_can_reply_review'])->each(fn($item, $key) => $request[$item] = $request->has($item) ? (int)$request[$item] : 0);

        $validated = $request->validate([
            'provider_can_cancel_booking' => 'required|in:0,1',
            'provider_can_edit_booking' => 'required|in:0,1',
            'provider_self_registration' => 'required|in:0,1',
            'provider_self_delete' => 'required|in:0,1',
            'max_cash_in_hand_limit_provider' => 'required',
            'min_payable_amount' => 'required',
            'suspend_on_exceed_cash_limit_provider' => 'required|in:0,1',
            'provider_commision' => 'required|in:0,1',
            'provider_subscription' => 'required|in:0,1',
            'provider_can_reply_review' => 'required|in:0,1',
        ]);

        $maxCash = $request->input('max_cash_in_hand_limit_provider');
        $minPayable = $request->input('min_payable_amount');

        if ($minPayable > $maxCash) {
            return redirect()->back()->withErrors(['min_payable_amount' => 'The min payable amount must be less than the max cash in hand limit for the provider.'])->withInput();
        }

        $oldMaximumLimitAmount = $this->businessSetting->where('key_name', 'max_cash_in_hand_limit_provider')->where('settings_type', 'provider_config')?->first()?->live_values;

        foreach ($validated as $key => $value) {
            $this->businessSetting->updateOrCreate(['key_name' => $key], [
                'key_name' => $key,
                'live_values' => $value,
                'test_values' => $value,
                'settings_type' => 'provider_config',
                'mode' => 'live',
                'is_active' => 1,
            ]);
        }

        $currentMaxLimitAmount = $this->businessSetting->where('key_name', 'max_cash_in_hand_limit_provider')->where('settings_type', 'provider_config')->first()->live_values;
        $providers = Provider::ofApproval(1)->ofStatus(1)->get();

        if($oldMaximumLimitAmount && $oldMaximumLimitAmount != $currentMaxLimitAmount){
            foreach ($providers as $provider){
                if ($provider){
                    $payable = $provider?->owner?->account?->account_payable;
                    $receivable = $provider?->owner?->account?->account_receivable;
                    if ($payable > $receivable) {
                        $cash_in_hand = $payable - $receivable;
                        if ($cash_in_hand >= $currentMaxLimitAmount){
                            $provider->is_suspended = 1;
                            $provider->save();
                        }else{
                            $provider->is_suspended = 0;
                            $provider->save();
                        }
                    }elseif($payable <= $receivable){
                        $provider->is_suspended = 0;
                        $provider->save();
                    }
                }
            }
        }

        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();
    }


    /**
     * Update resource.
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException|AuthorizationException
     */
    public function updateActionStatus(Request $request): JsonResponse
    {
        $this->authorize('business_update');
        $request[$request['key']] = $request['value'];

        $validator = Validator::make($request->all(), [
            'schedule_booking' => 'in:1,0',
            'provider_can_cancel_booking' => 'in:1,0',
            'serviceman_can_cancel_booking' => 'in:1,0',
            'admin_order_notification' => 'in:1,0',
            'provider_self_registration' => 'in:1,0',
            'guest_checkout' => 'in:1,0',
            'booking_additional_charge' => 'in:1,0',

            //bidding
            'bidding_status' => 'in:0,1',

            //payment
            'cash_after_service' => 'in:0,1',
            'digital_payment' => 'in:0,1',
            'wallet_payment' => 'in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        foreach ($validator->validated() as $key => $value) {
            if ($key != 'phone_verification' && $key != 'email_verification') {
                $this->businessSetting->updateOrCreate(['key_name' => $key, 'settings_type' => $request['settings_type']], [
                    'key_name' => $key,
                    'live_values' => $value,
                    'test_values' => $value,
                    'is_active' => $value,
                    'settings_type' => $request['settings_type'],
                    'mode' => 'live',
                ]);
            } else {
                if ($key == 'phone_verification') {
                    $this->businessSetting->updateOrCreate(['key_name' => $key, 'settings_type' => $request['settings_type']], [
                        'key_name' => $key,
                        'live_values' => $value,
                        'test_values' => $value,
                        'is_active' => $value,
                        'settings_type' => $request['settings_type'],
                        'mode' => 'live',
                    ]);
                    if ($value == 1) {
                        $this->businessSetting->updateOrCreate(['key_name' => 'email_verification', 'settings_type' => $request['settings_type']], [
                            'key_name' => 'email_verification',
                            'live_values' => (int)!$value,
                            'test_values' => (int)!$value,
                            'is_active' => (int)!$value,
                            'settings_type' => $request['settings_type'],
                            'mode' => 'live',
                        ]);
                    }
                } else if ($key == 'email_verification') {
                    $this->businessSetting->updateOrCreate(['key_name' => $key, 'settings_type' => $request['settings_type']], [
                        'key_name' => $key,
                        'live_values' => $value,
                        'test_values' => $value,
                        'is_active' => $value,
                        'settings_type' => $request['settings_type'],
                        'mode' => 'live',
                    ]);
                    if ($value == 1) {
                        $this->businessSetting->updateOrCreate(['key_name' => 'phone_verification', 'settings_type' => $request['settings_type']], [
                            'key_name' => 'phone_verification',
                            'live_values' => (int)!$value,
                            'test_values' => (int)!$value,
                            'is_active' => (int)!$value,
                            'settings_type' => $request['settings_type'],
                            'mode' => 'live',
                        ]);
                    }
                }
            }
        }

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException|AuthorizationException
     */
    public function promotionSetupSet(Request $request): RedirectResponse
    {
        $this->authorize('business_update');
        $request->validate([
            'bearer' => 'required|in:admin,provider,both',
        ]);

        if ($request['bearer'] != 'both' && $request['bearer'] == 'admin') {
            $request['admin_percentage'] = 100;
            $request['provider_percentage'] = 0;
        }
        if ($request['bearer'] != 'both' && $request['bearer'] == 'provider') {
            $request['admin_percentage'] = 0;
            $request['provider_percentage'] = 100;
        }

        $validator = Validator::make($request->all(), [
            'bearer' => 'in:admin,provider,both',
            'admin_percentage' => $request['bearer'] == 'both' ? 'min:1|max:99' : '',
            'provider_percentage' => $request['bearer'] == 'both' ? 'min:1|max:99' : '',
            'type' => 'in:discount,campaign,coupon',
        ]);

        if ($validator->fails()) {
            Toastr::error(translate(DEFAULT_FAIL_200['message']));
            return back();
        }


        $this->businessSetting->updateOrCreate(['key_name' => $request['type'] . '_cost_bearer', 'settings_type' => 'promotional_setup'], [
            'key_name' => $request['type'] . '_cost_bearer',
            'live_values' => $validator->validated(),
            'test_values' => $validator->validated(),
            'is_active' => 1,
            'settings_type' => 'promotional_setup',
            'mode' => 'live',
        ]);

        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function pagesSetupGet(Request $request): View|Factory|Application
    {
        $this->authorize('page_view');
        $webPage = $request->has('web_page') ? $request['web_page'] : 'about_us';
        $dataValues = $this->dataSetting->where('type', 'pages_setup')->withoutGlobalScope('translate')->with('translations')->orderBy('key')->get();
        return view('businesssettingsmodule::admin.page-settings', compact('dataValues', 'webPage'));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     * @throws AuthorizationException
     */
    public function pagesSetupSet(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('page_update');
        $request->validate([
            'page_name' => 'required|in:about_us,privacy_policy,terms_and_conditions,refund_policy,cancellation_policy',
            'page_content.0' => 'required',
        ], [
            'page_content.0.required' => 'The default content is required.',
        ]);

        $businessData = $this->dataSetting->updateOrCreate(['key' => $request['page_name'], 'type' => 'pages_setup'], [
            'key' => $request['page_name'],
            'value' => $request->page_content[array_search('default', $request->lang)],
            'type' => 'pages_setup',
            'is_active' => $request['is_active'] ?? 0,
        ]);

        $defaultLanguage = str_replace('_', '-', app()->getLocale());

        foreach ($request->lang as $index => $key) {
            if ($defaultLanguage == $key && !($request->page_content[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\DataSetting',
                            'translationable_id' => $businessData->id,
                            'locale' => $key,
                            'key' => $businessData->key],
                        ['value' => $businessData->page_content]
                    );
                }
            } else {
                if ($request->page_content[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\DataSetting',
                            'translationable_id' => $businessData->id,
                            'locale' => $key,
                            'key' => $businessData->key],
                        ['value' => $request->page_content[$index]]
                    );
                }
            }
        }

        if (in_array($request['page_name'], ['privacy_policy', 'terms_and_conditions'])) {
            $message = translate('page_information_has_been_updated') . '!';

            $customerTncCheck = isNotificationActive(null, 'terms_&_conditions_update', 'notification', 'user');
            $providerTncCheck = isNotificationActive(null, 'terms_&_conditions_update', 'notification', 'provider');
            $servicemanTncCheck = isNotificationActive(null, 'terms_&_conditions_update', 'notification', 'serviceman');
            if ($request['page_name'] == 'terms_and_conditions' && $request['is_active'] == 1) {
                if ($customerTncCheck) {
                    topic_notification('customer', translate($request['page_name']), $message, 'def.png', null, $request['page_name']);
                }
                if ($providerTncCheck) {
                    topic_notification('provider-admin', translate($request['page_name']), $message, 'def.png', null, $request['page_name']);
                }
                if ($servicemanTncCheck) {
                    topic_notification('provider-serviceman', translate($request['page_name']), $message, 'def.png', null, $request['page_name']);
                }
            }


            $customerCheck = isNotificationActive(null, 'privacy_policy_update', 'notification', 'user');
            $providerCheck = isNotificationActive(null, 'privacy_policy_update', 'notification', 'provider');
            $servicemanCheck = isNotificationActive(null, 'privacy_policy_update', 'notification', 'serviceman');
            if ($request['page_name'] == 'privacy_policy' && $request['is_active'] == 1) {
                if ($customerCheck) {
                    topic_notification('customer', translate($request['page_name']), $message, 'def.png', null, $request['page_name']);
                }
                if ($providerCheck) {
                    topic_notification('provider-admin', translate($request['page_name']), $message, 'def.png', null, $request['page_name']);
                }
                if ($servicemanCheck) {
                    topic_notification('provider-serviceman', translate($request['page_name']), $message, 'def.png', null, $request['page_name']);
                }
            }
        }

        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();
    }


    public function gallerySetupGet(Request $request, $path = 'cHVibGlj')
    {
        $this->authorize('gallery_view');

        $storage = $request->has('storage') ? $request->storage : 'local';

        if ($storage == 's3' && getDisk()=='s3'){
            try {
                Storage::disk('s3')->exists($path);
            } catch (\Exception $e){
                Toastr::error(translate('something_went_wrong'));
                return back();
            }

            $path = $path == "cHVibGlj"? "":$path;
            $directory = base64_decode($path).'/';
            $s3 = Storage::disk('s3');
            $file = $directory == '/'?[]:$s3->allFiles($directory);
            $directories = $s3->allDirectories($directory);

        }else{
            $storage = 'local';
            $file = Storage::files(base64_decode($path));
            $directories = Storage::directories(base64_decode($path));
        }

        $folders = $this->format_file_and_folders($directories, 'folder');
        $files = $this->format_file_and_folders($file, 'file');
        $data = array_merge($folders, $files);
        $folderPath = $path;
        return view('businesssettingsmodule::admin.gallery-settings', compact('data', 'folderPath', 'storage'));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function galleryImageUpload(Request $request): RedirectResponse
    {
        $this->authorize('gallery_add');

        if (env('APP_MODE') == 'demo') {
            Toastr::info(translate('upload_option_is_disable_for_demo'));
            return back();
        }

        $request->validate([
            'images' => 'required_without:file',
            'images.*' => 'max:10000',
            'file' => 'required_without:images|mimes:zip',
            'path' => 'required_if:disk,local',
        ]);

        $disk = $request->disk;

        if($disk == 's3' && !$request->path){
            Toastr::warning(translate('To_upload_file_on_s3_bucket_go_to_a_specific_folder'));
            return back();
        }

        if ($request->hasfile('images')) {
            $images = $request->file('images');

            foreach ($images as $image) {
                $name = $image->getClientOriginalName();
                if ($disk === 'local') {
                    Storage::disk($disk)->put($request->path . '/' . $name, file_get_contents($image));
                } elseif ($disk === 's3') {
                    Storage::disk($disk)->putFileAs($request->path, $image, $name);
                }
            }
        }
        if ($request->hasfile('file')) {
            $file = $request->file('file');
            $name = $file->getClientOriginalName();


            if ($disk === 's3') {
                $zipContents = file_get_contents($file->path());
                $zip = new ZipArchive;
                if ($zip->open($file->path()) === true) {
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $stat = $zip->statIndex($i);

                        if (!$stat['name'] || $this->shouldSkip($stat['name'])) {
                            continue; // Skip directories and unwanted files
                        }

                        $filename = $stat['name'];
                        $fileContent = $zip->getFromIndex($i);
                        $format = pathinfo($filename, PATHINFO_EXTENSION);

                        $imageName = Carbon::now()->toDateString() . "-" . uniqid() . "." . $format;

                        $s3 = Storage::disk('s3');
                        $s3Path = $request->path . '/' . $imageName;
                        $s3->put($s3Path, $fileContent, 'public');
                    }
                    $zip->close();
                }
            }else{
                Madzipper::make($file)->extractTo('storage/app/' . $request->path);
            }

            //Madzipper::make($file)->extractTo('storage/app/' . $request->path);
        }
        Toastr::success(translate('uploaded_successfully'));
        return back()->with('success', translate('uploaded_successfully'));
    }

    private function shouldSkip($filename) {
        // Add conditions to skip files here
        $skipFiles = [
            '__MACOSX/', // Skip macOS metadata files
            '.DS_Store', // Skip .DS_Store files
            'Thumbs.db', // Skip Thumbs.db files (Windows)
            // Add more conditions as needed
        ];

        foreach ($skipFiles as $skipFile) {
            if (strpos($filename, $skipFile) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Display a listing of the resource.
     * @param $file_path
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function galleryImageRemove($file_path)
    {
        $this->authorize('gallery_delete');
        Storage::disk('local')->delete(base64_decode($file_path));
        Storage::disk('s3')->delete(base64_decode($file_path));
        Toastr::success(translate('image_deleted_successfully'));
        return back()->with('success', translate('image_deleted_successfully'));
    }

    /**
     * Display a listing of the resource.
     * @param $file_name
     * @return StreamedResponse
     */
    public function galleryImageDownload(Request $request, $file_name): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $storage = $request->has('storage') ? $request->storage : 'local';
        return Storage::disk($storage)->download(base64_decode($file_name));

    }

    /**
     * @param Request $request
     * @return BinaryFileResponse|RedirectResponse
     * @throws AuthorizationException
     */
    public function downloadPublicDirectory(Request $request): BinaryFileResponse|RedirectResponse
    {
        $this->authorize('gallery_export');

        if (!class_exists('ZipArchive')) {
            Toastr::error(translate('The ZipArchive class is not available'));
            return back();
        }

        if (!extension_loaded('zip')) {
            Toastr::error(translate('The zip extension is not enabled'));
            return back();
        }

        $zipFileName = sys_get_temp_dir() . '/public.zip';
        $zip = new ZipArchive();

        $storage = $request->has('storage') && $request->storage == 's3' ? 's3' : 'public';

        if ($zip->open($zipFileName, ZipArchive::CREATE) === true) {
            $files = Storage::disk($storage)->allFiles();

            foreach ($files as $file) {
                if ($storage === 's3') {
                    $fileContent = Storage::disk('s3')->get($file);
                    $zip->addFromString($file, $fileContent);
                } else {
                    $filePath = storage_path('app/public/' . $file);
                    if (file_exists($filePath)) {
                        $zip->addFile($filePath, $file);
                    }
                }
            }

            $zip->close();

            $response = new BinaryFileResponse($zipFileName);
            $response->deleteFileAfterSend(true);
            return $response;
        } else {
            Toastr::error(translate('Failed to create zip archive'));
            return back();
        }
    }

    /**
     * Display a listing of the resource.
     * @return Application|Factory|View
     */
    public function getDatabaseBackup(): View|Factory|Application
    {
//        $this->authorize('backup_view');
        if (!File::exists(storage_path('backup'))) {
            File::makeDirectory(storage_path('backup'), 0777, true);
        }
        $files = File::files('storage/backup');

        $fileNames = [];
        foreach ($files as $file) {
            $fileNames[] = [
                'name' => $file->getFilename(),
                'size' => number_format($file->getSize() / 1048576, 2) . ' MB',
                'last_modified' => date('M d, Y', $file->getMTime())
            ];
        }

        return view('businesssettingsmodule::admin.database-backup', compact('fileNames'));
    }

    /**
     * Display a listing of the resource.
     * @param $file_name
     * @return RedirectResponse
     */
    public function deleteDatabaseBackup($file_name): RedirectResponse
    {
        $this->authorize('backup_delete');
        $file = storage_path('backup/' . $file_name);
        if (File::exists($file)) {
            File::delete($file);
        }
        Toastr::success(translate(DEFAULT_DELETE_200['message']));
        return back();
    }

    /**
     * Backup of the resource.
     */
    public function backupDatabase(Request $request): BinaryFileResponse|RedirectResponse
    {
        $this->authorize('backup_add');
        //take backup
        Artisan::call('database:backup');

        //move file
        if (!File::exists(storage_path('backup'))) {
            File::makeDirectory(storage_path('backup'), 0777, true);
        }
        $sqlFileName = 'database_backup_' . date("Y-m-d_H-i") . '.sql';

        $file = base_path($sqlFileName);
        $destination = storage_path('backup/' . $sqlFileName);
        File::move($file, $destination);

        Toastr::success(translate('Database backup has been completed successfully'));

        if ($request->query('download', false)) {
            return response()->download($destination);
        }
        return back();
    }

    /**
     * Restore the resource.
     */
    public function restoreDatabaseBackup($file_name): RedirectResponse
    {
        $this->authorize('backup_add');
        $file = storage_path('backup/' . $file_name);
        if (!File::exists($file)) {
            Toastr::error(translate('File does not exists'));
            return back();
        }

        try {
            //make a backup first
            Artisan::call('database:backup');

            //move file
            if (!File::exists(storage_path('backup'))) {
                File::makeDirectory(storage_path('backup'), 0777, true);
            }
            $sqlFileName = 'database_backup_' . date("Y-m-d_H-i") . '.sql';

            $file = base_path($sqlFileName);
            $destination = storage_path('backup/' . $sqlFileName);
            File::move($file, $destination);

            //restore operation
            //db operations
            Artisan::call('db:wipe');
            DB::unprepared(file_get_contents($file));

            Toastr::success(translate('Database restored successfully'));
            return back();

        } catch (\Exception $exception) {
            Toastr::success(translate('Database restored failed'));
            return back();
        }

    }

    /**
     * Display a listing of the resource.
     * @param $file_name
     * @return BinaryFileResponse | RedirectResponse
     */
    public function download($file_name): BinaryFileResponse|RedirectResponse
    {
        $this->authorize('backup_export');
        $file = storage_path('backup/' . $file_name);
        if (File::exists($file)) {
            return response()->download($file);
        }

        Toastr::error(translate('File does not exists'));
        return back();
    }


    /**
     * @param Request $request
     * @return BinaryFileResponse|RedirectResponse
     */
    public function updateBinaryPath(Request $request): BinaryFileResponse|RedirectResponse
    {
        if ($request->has('binary_path') && !is_null($request['binary_path'])) {
            $this->setEnvironmentValue('DUMP_BINARY_PATH', $request['binary_path']);
        }

        Toastr::success(translate('DUMP_BINARY_PATH updated'));
        return back();
    }

}
