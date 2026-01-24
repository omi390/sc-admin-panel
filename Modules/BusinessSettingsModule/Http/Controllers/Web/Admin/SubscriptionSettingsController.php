<?php

namespace Modules\BusinessSettingsModule\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SubscriptionSettingsController extends Controller
{
    private BusinessSettings $businessSetting;
    use AuthorizesRequests;

    public function __construct(BusinessSettings $businessSetting)
    {
        $this->businessSetting = $businessSetting;
    }

    /**
     * @return Application|Factory|View|\Illuminate\Foundation\Application
     * @throws AuthorizationException
     */
    public function settings(): \Illuminate\Foundation\Application|View|Factory|Application
    {
        $this->authorize('subscription_settings_view');
        $subscriptionSettings = $this->businessSetting->where('settings_type', 'subscription_Setting')->get();

        $freeTrialSetting = $subscriptionSettings->where('key_name', 'free_trial_period')->first();
        $freeTrialTypeSetting = $subscriptionSettings->where('key_name', 'free_trial_type')->first();

        $data['freeTrialStatus'] = $freeTrialSetting->is_active ?? 0;
        $data['freeTrialPeriod'] = $freeTrialSetting->live_values ?? 0;
        $data['freeTrialType'] = $freeTrialTypeSetting->live_values ?? 'day';

        if ($data['freeTrialType'] == 'month') {
            $data['freeTrialPeriod'] = (int) floor($data['freeTrialPeriod'] / 30);
        } elseif ($data['freeTrialType'] == 'year') {
            $data['freeTrialPeriod'] = Carbon::now()->addDays($data['freeTrialPeriod'])->diffInYears(Carbon::now());
        }

        $data['deadlineWarning'] = $subscriptionSettings->where('key_name', 'deadline_warning')->first()->live_values ?? 0;
        $data['deadlineWarningMessage'] = $subscriptionSettings->where('key_name', 'deadline_warning_message')->first()->live_values ?? '';
        $data['usageTime'] = $subscriptionSettings->where('key_name', 'usage_time')->first()->live_values ?? 0;
        $data['subscriptionVat'] = $subscriptionSettings->where('key_name', 'subscription_vat')->first()->live_values ?? 0;

        return view('businesssettingsmodule::admin.subscription-package.setting', $data);
    }

    /**
     * @return \Illuminate\Foundation\Application|Application|Redirector|RedirectResponse
     * @throws ValidationException|AuthorizationException
     */
    public function settingsStore(Request $request): JsonResponse|Redirector|RedirectResponse|Application
    {
        $this->authorize('subscription_settings_update');

        $validator = null;

        if ($request['trial'] == 'trial') {
            $validator = Validator::make($request->all(), [
                'free_trial_period' => 'required|integer|gte:1|lte:99999999999',
                'free_trial_type' => 'required|in:day,month,year',
            ]);
        } elseif ($request['warning'] == 'warning') {
            $validator = Validator::make($request->all(), [
                'deadline_warning' => 'required|integer|gte:1|lte:99999999999',
                'deadline_warning_message' => 'required|string',
            ]);
        } elseif ($request['return'] == 'return') {
            $validator = Validator::make($request->all(), [
                'usage_time' => 'required|integer|gte:1|lte:100',
            ]);
        } elseif ($request['vat'] == 'vat') {
            $validator = Validator::make($request->all(), [
                'subscription_vat' => 'required|integer|gte:1|lte:1000',
            ]);
        }

        if ($validator && $validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if ($validator) {
            $validatedData = $validator->validated();

            if (isset($validatedData['free_trial_type'])) {
                switch ($validatedData['free_trial_type']) {
                    case 'month':
                        $validatedData['free_trial_period'] = $validatedData['free_trial_period'] * 30;
                        break;
                    case 'year':
                        $validatedData['free_trial_period'] = $validatedData['free_trial_period'] * 365;
                        break;
                }
            }

            foreach ($validatedData as $key => $value) {
                $this->businessSetting->updateOrCreate(
                    ['key_name' => $key],
                    [
                        'key_name' => $key,
                        'live_values' => $value,
                        'test_values' => $value,
                        'settings_type' => 'subscription_Setting',
                        'mode' => 'live',
                        'is_active' => 1,
                    ]
                );
            }
        }
        if ($request->key){
            $freeTrial = $this->businessSetting->where('key_name', $request->key)->first();
            $this->businessSetting->where('key_name', $request->key)->update(['is_active' => !$freeTrial->is_active]);

            if ($freeTrial->is_active){
                Toastr::success(translate(DEFAULT_STATUS_UPDATE_200['message']));
                return back();
            }
        }

        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();
    }

}
