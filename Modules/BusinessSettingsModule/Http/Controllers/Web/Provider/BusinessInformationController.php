<?php

namespace Modules\BusinessSettingsModule\Http\Controllers\Web\Provider;

use App\Traits\ActivationClass;
use App\Traits\FileManagerTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ProviderManagement\Entities\ProviderSetting;

class BusinessInformationController extends Controller
{
    use ActivationClass;
    use FileManagerTrait;

    private BusinessSettings $businessSetting;
    private ProviderSetting $providerSetting;
    private Provider $provider;

    public function __construct(BusinessSettings $businessSetting, ProviderSetting $providerSetting, Provider $provider)
    {
        $this->businessSetting = $businessSetting;
        $this->providerSetting = $providerSetting;
        $this->provider = $provider;

        if (request()->isMethod('get')) {
            $response = $this->actch();
            $data = json_decode($response->getContent(), true);
            if (!$data['active']) {
                return Redirect::away(base64_decode('aHR0cHM6Ly82YW10ZWNoLmNvbS9zb2Z0d2FyZS1hY3RpdmF0aW9u'))->send();
            }
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function businessInformationGet(Request $request): Factory|View|Application
    {
        if ($this->providerSetting->where(['key_name' => 'provider_serviceman_can_edit_booking', 'settings_type' => 'serviceman_config', 'provider_id' => auth()->user()->provider->id])->first() == false) {
            $this->providerSetting->updateOrCreate(['key_name' => 'provider_serviceman_can_edit_booking', 'settings_type' => 'serviceman_config'], [
                'provider_id' => auth()->user()->provider->id,
                'live_values' => 0,
                'test_values' => 0
            ]);
        }

        if ($this->providerSetting->where(['key_name' => 'provider_serviceman_can_cancel_booking', 'settings_type' => 'serviceman_config', 'provider_id' => auth()->user()->provider->id])->first() == false) {
            $this->providerSetting->updateOrCreate(['key_name' => 'provider_serviceman_can_cancel_booking', 'settings_type' => 'serviceman_config'], [
                'provider_id' => auth()->user()->provider->id,
                'live_values' => 0,
                'test_values' => 0
            ]);
        }

        $providerId = auth()->user()->provider->id;

        $dataValues = $this->providerSetting->where('settings_type', 'serviceman_config')->get();
        $webPage = $request->has('web_page') ? $request['web_page'] : 'service_availability';

        $timeSchedule = provider_config('time_schedule', 'service_schedule', $providerId)->live_values ?? '';
        $timeSchedule = json_decode($timeSchedule, true);
        $weekEnds = provider_config('weekends', 'service_schedule', $providerId)->live_values ?? '';
        $weekEnds = json_decode($weekEnds);

        return view('businesssettingsmodule::provider.business', compact('dataValues', 'providerId', 'webPage', 'timeSchedule', 'weekEnds'));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     * @throws ValidationException
     */
    public function businessInformationSet(Request $request): JsonResponse|RedirectResponse
    {
        collect(['provider_serviceman_can_edit_booking', 'provider_serviceman_can_cancel_booking'])->each(fn($item, $key) => $request[$item] = $request->has($item) ? (int)$request[$item] : 0);
        $validator = Validator::make($request->all(), [
            'provider_serviceman_can_edit_booking' => 'required|in:0,1',
            'provider_serviceman_can_cancel_booking' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        foreach ($validator->validated() as $key => $value) {
            $this->providerSetting->updateOrCreate(['key_name' => $key, 'provider_id' => auth()->user()->provider->id, 'settings_type' => 'serviceman_config'], [
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
     * Update resource.
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function availabilityStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_availability' => 'in:1,0',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $provider = Provider::where('user_id', $request->user()->id)->first();

        if ($provider){
            $provider->service_availability = $request->service_availability;
            $provider->save();
            return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
        }

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

    public function availabilitySchedule(Request $request): RedirectResponse
    {
       $request->validate([
           'start_time' => 'nullable|date_format:H:i',
           'end_time' => 'nullable|date_format:H:i|after:start_time',
           'day' => 'array',
           'day.*' => 'in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
        ]);
        $requestData = $request->all();

        $timeSchedulesData = [
            'start_time' => $requestData['start_time'],
            'end_time' => $requestData['end_time'],
        ];

        $weekend = $requestData['day'] ?? [];

        $this->providerSetting::updateOrCreate(
            [
                'key_name' => 'time_schedule',
                'settings_type' => 'service_schedule',
                'provider_id' => $request->user()->provider->id,
            ],
            [
                'live_values' => json_encode($timeSchedulesData),
                'test_values' => json_encode($timeSchedulesData),
            ]
        );

        $this->providerSetting::updateOrCreate(
            [
                'key_name' => 'weekends',
                'settings_type' => 'service_schedule',
                'provider_id' => $request->user()->provider->id,
            ],
            [
                'live_values' => isset($weekend) ? json_encode($weekend) : null,
                'test_values' => isset($weekend) ? json_encode($weekend) : null,
            ]
        );

        Toastr::success(translate('successfully updated'));
        return back();
    }

}
