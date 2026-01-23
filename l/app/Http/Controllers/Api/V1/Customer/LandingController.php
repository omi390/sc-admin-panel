<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;
use Modules\BusinessSettingsModule\Entities\DataSetting;
use Modules\BusinessSettingsModule\Entities\LandingPageTestimonial;

class LandingController extends Controller
{

    private BusinessSettings $business_setting;
    private DataSetting $dataSetting;
    private LandingPageTestimonial $testimonial;

    public function __construct(BusinessSettings $business_setting, DataSetting $dataSetting, LandingPageTestimonial $testimonial)
    {
        $this->business_setting = $business_setting;
        $this->dataSetting = $dataSetting;
        $this->testimonial = $testimonial;
    }

    public function index()
    {
        $data = [];

        $data = [
            'top_image_1' =>  getBusinessSettingsImageFullPath(key: 'top_image_1', settingType: 'landing_images', path: 'landing-page/', defaultPath: 'public/assets/placeholder.png'),
            'top_image_2' =>  getBusinessSettingsImageFullPath(key: 'top_image_2', settingType: 'landing_images', path: 'landing-page/', defaultPath: 'public/assets/placeholder.png'),
            'top_image_3' =>  getBusinessSettingsImageFullPath(key: 'top_image_3', settingType: 'landing_images', path: 'landing-page/', defaultPath: 'public/assets/placeholder.png'),
            'top_image_4' =>  getBusinessSettingsImageFullPath(key: 'top_image_4', settingType: 'landing_images', path: 'landing-page/', defaultPath: 'public/assets/placeholder.png'),
            'support_section_image' =>  getBusinessSettingsImageFullPath(key: 'support_section_image', settingType: 'landing_web_app_image', path: 'landing-page/web/', defaultPath: 'public/assets/placeholder.png'),
            'download_section_image' =>  getBusinessSettingsImageFullPath(key: 'download_section_image', settingType: 'landing_web_app_image', path: 'landing-page/web/', defaultPath: 'public/assets/placeholder.png'),
            'feature_section_image' =>  getBusinessSettingsImageFullPath(key: 'feature_section_image', settingType: 'landing_web_app_image', path: 'landing-page/web/', defaultPath: 'public/assets/placeholder.png'),
        ];


        $valuess = $this->dataSetting->where('type', 'landing_web_app')->get();
        $data['text_content'] = $valuess;

        $values = $this->testimonial::all();
        $data['testimonial'] = $values ?? null;

        $values = $this->business_setting->where('key_name', 'social_media')->first();
        $data['social_media'] = isset($values) && !is_null($values->live_values) ? $values->live_values : null;

        return response()->json(response_formatter(DEFAULT_200, $data), 200);
    }

}
