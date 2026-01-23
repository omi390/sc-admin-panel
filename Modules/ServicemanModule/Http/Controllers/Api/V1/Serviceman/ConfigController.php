<?php

namespace Modules\ServicemanModule\Http\Controllers\Api\V1\Serviceman;

use App\Traits\MaintenanceModeTrait;
use DateTimeZone;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Modules\BusinessSettingsModule\Entities\LoginSetup;
use Modules\PaymentModule\Entities\Setting;
use Modules\ZoneManagement\Entities\Zone;
use Stevebauman\Location\Facades\Location;

class ConfigController extends Controller
{
    use MaintenanceModeTrait;
    private $google_map;
    private $google_map_base_api;

    function __construct()
    {
        $this->google_map = business_config('google_map', 'third_party');
        $this->google_map_base_api = 'https://maps.googleapis.com/maps/api';
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function configuration(Request $request): JsonResponse
    {
        $location = Location::get($request->ip());

        $advancedBooking =  [
            'advanced_booking_restriction_value' => (int) business_config('advanced_booking_restriction_value', 'booking_setup')?->live_values,
            'advanced_booking_restriction_type' => business_config('advanced_booking_restriction_type', 'booking_setup')?->live_values,
        ];

        $countryData = business_config('system_language', 'business_information')?->live_values;

        $country = [];

        foreach ($countryData as $item) {
            if ($item['status'] == 1) {
                $country[] = $item;
            }
        }

        $dataValues = Setting::where('settings_type', 'sms_config')->get();
        $count = 0;
        foreach ($dataValues as $gateway) {
            $status = $gateway?->live_values['status'] ?? 0;
            if ($status == 1) {
                $count = 1;
            }
        }
        $emailConfig = business_config('email_config_status', 'email_config')?->live_values;
        $firebaseOtpConfig = business_config('firebase_otp_verification', 'third_party');
        $firebaseOtpStatus =(int) $firebaseOtpConfig?->live_values['status'] ?? null;

        if ($firebaseOtpStatus == 1){
            $count = 1;
        }

        $forgotPasswordVerificationMethod = [
            'phone' => $count,
            'email' => $emailConfig
        ];

        return response()->json(response_formatter(DEFAULT_200, [
            'maintenance' => $this->checkMaintenanceMode(),
            'currency_symbol_position' => (business_config('currency_symbol_position', 'business_information'))->live_values ?? null,
            'serviceman_can_cancel_booking' => (int)(business_config('serviceman_can_cancel_booking', 'serviceman_config'))?->live_values,
            'serviceman_can_edit_booking' => (int)(business_config('serviceman_can_edit_booking', 'serviceman_config'))?->live_values,
            'business_name' => (business_config('business_name', 'business_information'))->live_values ?? null,
            'logo_full_path' => getBusinessSettingsImageFullPath(key: 'business_logo', settingType: 'business_information', path: 'business/',  defaultPath : 'public/assets/admin-module/img/media/banner-upload-file.png'),
            'favicon_full_path' =>  getBusinessSettingsImageFullPath(key: 'business_favicon', settingType: 'business_information', path: 'business/',  defaultPath : 'public/assets/admin-module/img/media/upload-file.png'),
            'country_code' => (business_config('country_code', 'business_information'))->live_values ?? null,
            'business_address' => (business_config('business_address', 'business_information'))->live_values ?? null,
            'business_phone' => (business_config('business_phone', 'business_information'))->live_values ?? null,
            'business_email' => (business_config('business_email', 'business_information'))->live_values ?? null,
            'base_url' => rtrim(url('/'), '/') . '/api/v1/',
            'currency_decimal_point' => (business_config('currency_decimal_point', 'business_information'))->live_values ?? null,
            'currency_code' => (business_config('currency_code', 'business_information'))->live_values ?? null,
            'currency_symbol' => currency_symbol() ?? '',
            'about_us' => route('about-us'),
            'privacy_policy' => route('privacy-policy'),
            'terms_and_conditions' => (business_config('terms_and_conditions', 'pages_setup'))->is_active ? route('terms-and-conditions') : "",
            'refund_policy' => (business_config('refund_policy', 'pages_setup'))->is_active ? route('refund-policy') : "",
            'cancellation_policy' => (business_config('cancellation_policy', 'pages_setup'))->is_active ? route('cancellation-policy') : "",
            'default_location' => ['default' => [
                'lat' => $location->latitude ?? null,
                'lon' => $location->longitude ?? null
            ]],
            'sms_verification' => (business_config('sms_verification', 'service_setup'))->live_values ?? null,
            'pagination_limit' => (int)pagination_limit(),
            'time_format' => (business_config('time_format', 'business_information'))->live_values ?? '24h',
            'footer_text' => (business_config('footer_text', 'business_information'))->live_values ?? null,
            'min_versions' => json_decode((business_config('serviceman_app_settings', 'app_settings'))->live_values ?? null),
            'phone_verification' => (int)((login_setup('phone_verification'))->value ?? 0),
            'email_verification' => (int)((login_setup('email_verification'))->value ?? 0),
            'otp_resend_time' => (int)(business_config('otp_resend_time', 'otp_login_setup'))?->live_values ?? null,
            'booking_otp_verification' => (int)(business_config('booking_otp', 'booking_setup'))->live_values ?? null,
            'service_complete_photo_evidence' => (int)(business_config('service_complete_photo_evidence', 'booking_setup'))?->live_values ?? null,
            'booking_additional_charge' => (int)(business_config('booking_additional_charge', 'booking_setup'))?->live_values ?? null,
            'additional_charge_label_name' => (string)(business_config('additional_charge_label_name', 'booking_setup'))?->live_values ?? null,
            'additional_charge_fee_amount' => (int)(business_config('additional_charge_fee_amount', 'booking_setup'))?->live_values ?? null,
            'system_language' => $country,
            'instant_booking' => (int) business_config('instant_booking', 'booking_setup')?->live_values,
            'schedule_booking' => (int) business_config('schedule_booking', 'booking_setup')?->live_values,
            'schedule_booking_time_restriction' => (int) business_config('schedule_booking_time_restriction', 'booking_setup')?->live_values,
            'advanced_booking' => $advancedBooking,
            'firebase_otp_verification' => $firebaseOtpStatus,
            'forgot_password_verification_method' => $forgotPasswordVerificationMethod,
            'app_environment' => env('APP_ENV'),
        ]), 200);
    }

    public function getZone(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'lng' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $point = new Point($request->lat, $request->lng);
        $zone = Zone::contains('coordinates', $point)->ofStatus(1)->latest()->first();

        if ($zone) {
            return response()->json(response_formatter(DEFAULT_200, $zone), 200);
        }

        return response()->json(response_formatter(DEFAULT_204), 200);
    }

    public function placeApiAutocomplete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'search_text' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }
        $response = Http::get($this->google_map_base_api . '/place/autocomplete/json?input=' . $request['search_text'] . '&key=' . $this->google_map->live_values['map_api_key_server']);
        return response()->json(response_formatter(DEFAULT_200, $response->json()), 200);
    }

    public function distanceApi(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'origin_lat' => 'required',
            'origin_lng' => 'required',
            'destination_lat' => 'required',
            'destination_lng' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $request['origin_lat'] . ',' . $request['origin_lng'] . '&destinations=' . $request['destination_lat'] . ',' . $request['destination_lng'] . '&key=' . $this->google_map->live_values['map_api_key_server']);

        return response()->json(response_formatter(DEFAULT_200, $response->json()), 200);
    }

    public function placeApiDetails(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'placeid' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json?placeid=' . $request['placeid'] . '&key=' . $this->google_map->live_values['map_api_key_server']);

        return response()->json(response_formatter(DEFAULT_200, $response->json()), 200);
    }

    public function geocodeApi(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'lng' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $request->lat . ',' . $request->lng . '&key=' . $this->google_map->live_values['map_api_key_server']);
        return response()->json(response_formatter(DEFAULT_200, $response->json()), 200);
    }

    public function getRoutes(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'origin_latitude' => 'required',
            'origin_longitude' => 'required',
            'destination_latitude' => 'required',
            'destination_longitude' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $distance = get_distance(
            [$request['origin_latitude'], $request['origin_longitude']],
            [$request['destination_latitude'], $request['destination_longitude']]
        );
        $distance = ($distance) ? number_format($distance, 2) . ' km' : null;

        return response()->json(response_formatter(DEFAULT_200, $distance), 200);
    }
}
