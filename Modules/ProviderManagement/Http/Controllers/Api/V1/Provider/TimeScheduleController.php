<?php

namespace Modules\ProviderManagement\Http\Controllers\Api\V1\Provider;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ProviderManagement\Entities\ProviderSetting;

class TimeScheduleController extends Controller
{

    public function __construct(
        private Provider $provider,
        private ProviderSetting $providerSetting
    )
    {
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function getAvailableTimeSchedule(Request $request): JsonResponse
    {
        $providerId = $request->user()->provider->id;
        $provider = $this->provider->select('service_availability')->find($providerId);
        $timeSchedule = provider_config('time_schedule', 'service_schedule', $providerId)->live_values ?? '';
        $timeSchedule = json_decode($timeSchedule);
        $weekEnds = provider_config('weekends', 'service_schedule', $providerId)->live_values ?? '';
        $weekEnds = json_decode($weekEnds);

        return response()->json(response_formatter(DEFAULT_200, [
            'service_availability' => (int) $provider->service_availability ?? 0,
            'time_schedule' => $timeSchedule ?? null,
            'weekends' => $weekEnds ?? []
        ]), 200);
    }

    /**
     * Show the form for creating a new resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function setAvailableTimeSchedule(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_availability' => 'required|in:0,1',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'weekends' => 'array',
            'weekends.*' => 'in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $provider = $this->provider->find($request->user()->provider->id);
        if (!isset($provider)) return response()->json(response_formatter(DEFAULT_404), 404);

        $provider->service_availability = $request['service_availability'];
        $provider->save();

        $requestData = $request->all();

        $weekend = $requestData['weekends'] ?? [];

        $timeSchedulesData = [
            'start_time' => $requestData['start_time'],
            'end_time' => $requestData['end_time'],
        ];

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


        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }
}
