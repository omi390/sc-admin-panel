<?php

namespace Modules\BusinessSettingsModule\Http\Controllers\Web\Admin;

use App\Traits\ActivationClass;
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
use Modules\BusinessSettingsModule\Entities\DataSetting;
use Modules\BusinessSettingsModule\Entities\LandingPageFeature;
use Modules\BusinessSettingsModule\Entities\LandingPageSpeciality;
use Modules\BusinessSettingsModule\Entities\LandingPageTestimonial;
use Modules\BusinessSettingsModule\Entities\Translation;
use Ramsey\Uuid\Uuid;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class LandingPageController extends Controller
{
    use ActivationClass;

    private BusinessSettings $business_setting;
    private LandingPageFeature $feature;
    private LandingPageSpeciality $speciality;
    private LandingPageTestimonial $testimonial;
    private DataSetting $dataSetting;

    use AuthorizesRequests;

    public function __construct(BusinessSettings $business_setting, LandingPageFeature $feature, LandingPageSpeciality $speciality, LandingPageTestimonial $testimonial, DataSetting $dataSetting)
    {
        $this->business_setting = $business_setting;
        $this->feature = $feature;
        $this->speciality = $speciality;
        $this->testimonial = $testimonial;
        $this->dataSetting = $dataSetting;

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
    public function getLandingInformation(Request $request): Factory|View|Application
    {
        $this->authorize('landing_view');
        $webPage = $request->has('web_page') ? $request['web_page'] : 'text_setup';
        $dataValues = [];
        if ($request['web_page'] != 'text_setup' && $request['web_page'] != 'web_app') {
            $dataValues = $this->business_setting->where('settings_type', 'landing_' . $webPage)->with('translations')->get();
        } else {
            $dataValues = $this->dataSetting->where('type', 'landing_' . $webPage)->withoutGlobalScope('translate')->with('translations')->get();
        }

        $features = $this->feature->all();
        $specialities = $this->speciality->all();
        $testimonials = $this->testimonial->all();
        return view('businesssettingsmodule::admin.landing-page', compact('dataValues', 'webPage', 'features', 'specialities', 'testimonials'));
    }

    /**
     * Display a listing of the resource.
     */
    public function setLandingInformation(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('landing_update');
        $validator = Validator::make($request->all(), [
            'top_title.0' => 'string',
            'top_description.0' => 'string',
            'top_sub_title.0' => 'string',
            'mid_title.0' => 'string',
            'about_us_title.0' => 'string',
            'about_us_description.0' => 'string',
            'registration_title.0' => 'string',
            'registration_description.0' => 'string',
            'bottom_title.0' => 'string',
            'bottom_description.0' => 'string',

            'app_url_playstore' => 'string',
            'app_url_appstore' => 'string',
            'web_url' => 'string',

            'meta_title' => 'string',
            'meta_description' => 'string',
            'meta_image' => 'image',

            'header_background' => 'string',
            'body_background' => 'string',
            'footer_background' => 'string',

            'web_top_title.0' => 'string',
            'web_top_description.0' => 'string',
            'web_mid_title.0' => 'string',
            'mid_sub_title_1.0' => 'string',
            'mid_sub_description_1.0' => 'string',
            'mid_sub_title_2.0' => 'string',
            'mid_sub_description_2.0' => 'string',
            'mid_sub_title_3.0' => 'string',
            'mid_sub_description_3.0' => 'string',
            'download_section_title.0' => 'string',
            'download_section_description.0' => 'string',
            'web_bottom_title.0' => 'string',
            'testimonial_title.0' => 'string',

            'media' => 'in:facebook,instagram,linkedin,twitter,youtube',
            'link' => '',
        ],
            [
                'top_title.0.string' => translate('top_title should be a string'),
                'top_description.0.string' => translate('top_description should be a string'),
                'top_sub_title.0.string' => translate('top_sub_title should be a string'),
                'mid_title.0.string' => translate('mid_title should be a string'),
                'about_us_title.0.string' => translate('about_us_title should be a string'),
                'about_us_description.0.string' => translate('about_us_description should be a string'),
                'registration_title.0.string' => translate('registration_title should be a string'),
                'registration_description.0.string' => translate('registration_description should be a string'),
                'bottom_title.0.string' => translate('bottom_title should be a string'),
                'bottom_description.0.string' => translate('bottom_description should be a string'),
                'web_top_title.0.string' => translate('web_top_title should be a string'),
                'web_top_description.0.string' => translate('web_top_description should be a string'),
                'web_mid_title.0.string' => translate('web_mid_title should be a string'),
                'mid_sub_title_1.0.string' => translate('mid_sub_title_1 should be a string'),
                'mid_sub_description_1.0.string' => translate('mid_sub_description_1.0 should be a string'),
                'mid_sub_title_2.0.string' => translate('mid_sub_title_2 should be a string'),
                'mid_sub_description_2.0.string' => translate('mid_sub_description_2 should be a string'),
                'mid_sub_title_3.0.string' => translate('mid_sub_title_3 should be a string'),
                'mid_sub_description_3.0.string' => translate('mid_sub_description_3 should be a string'),
                'download_section_title.0.string' => translate('download_section_title should be a string'),
                'download_section_description.0.string' => translate('download_section_description should be a string'),
                'web_bottom_title.0.string' => translate('web_bottom_title should be a string'),
                'testimonial_title.0.string' => translate('testimonial_title should be a string'),
            ]
        );

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 200);
        }
        $array = [];

        if ($request['web_page'] == 'images') {
            $keys = ['top_image_1', 'top_image_2', 'top_image_3', 'top_image_4', 'about_us_image', 'service_section_image', 'provider_section_image'];
            $image = 'def.png';
            $imageKey = '';
            foreach ($keys as $key) {
                if ($request->has($key)) {
                    $value = $this->business_setting->where('key_name', $key)->first();
                    if (isset($value)) {
                        file_remover('landing-page/', $value['live_values']);
                    }
                    $image = file_uploader('landing-page/', 'png', $request->file($key));
                    $imageKey = $key;

                    //for s3
                    $storageType = getDisk();
                    if($image && $storageType != 'public'){
                        saveBusinessImageDataToStorage(model: $value, modelColumn : $key, storageType : $storageType);
                    }
                }
            }
            $page = $request['web_page'];
            $filter = $request->except(['_method', '_token', 'web_page', $imageKey]);
            $filter[$imageKey] = $image;
        } elseif ($request['web_page'] == 'web_app_image') {
            $keys = ['support_section_image', 'download_section_image', 'feature_section_image'];
            $image = 'def.png';
            $imageKey = '';
            foreach ($keys as $key) {
                if ($request->has($key)) {
                    $value = $this->business_setting->where('key_name', $key)->first();
                    if (isset($value)) {
                        file_remover('landing-page/web/', $value['live_values']);
                    }
                    $image = file_uploader('landing-page/web/', 'png', $request->file($key));
                    $imageKey = $key;

                    $storageType = getDisk();
                    if($image && $storageType != 'public'){
                        saveBusinessImageDataToStorage(model: $value, modelColumn : $key, storageType : $storageType);
                    }
                }
            }
            $page = $request['web_page'];
            $filter = $request->except(['_method', '_token', 'web_page', $imageKey]);
            $filter[$imageKey] = $image;
        } elseif ($request['web_page'] == 'social_media') {
            $data = $this->business_setting->where('settings_type', 'landing_social_media')->first();
            if (isset($data)) {
                $array = $data['live_values'];
            }

            if (isset($array)) {
                $found = false;
                foreach ($array as &$item) {
                    if ($item['media'] === $request['media']) {
                        $item['link'] = $request['link'];
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $array[] = [
                        'id' => Uuid::uuid4(),
                        'media' => $request['media'],
                        'link' => $request['link']
                    ];
                }
            } else {
                $array[] = [
                    'id' => Uuid::uuid4(),
                    'media' => $request['media'],
                    'link' => $request['link']
                ];
            }

            $request['social_media'] = $array;
            $page = $request['web_page'];
            $filter = $request->except(['_method', '_token', 'media', 'link', 'web_page']);
        } elseif ($request['web_page'] == 'text_setup') {
            $page = $request['web_page'];

            $textKeys = [
                'top_title',
                'top_description',
                'top_sub_title',
                'mid_title',
                'about_us_title',
                'about_us_description',
                'registration_title',
                'registration_description',
                'bottom_title',
            ];

            foreach ($textKeys as $key) {
                $textData[$key] = $request->$key[array_search('default', $request->lang)];
            }
        } elseif ($request['web_page'] == 'web_app') {
            $page = $request['web_page'];

            $webKeys = [
                'web_top_title',
                'web_top_description',
                'web_mid_title',
                'mid_sub_title_1',
                'mid_sub_description_1',
                'mid_sub_title_2',
                'mid_sub_description_2',
                'mid_sub_title_3',
                'mid_sub_description_3',
                'download_section_title',
                'download_section_description',
                'web_bottom_title',
                'testimonial_title',
            ];

            foreach ($webKeys as $key) {
                $textData[$key] = $request->$key[array_search('default', $request->lang)];
            }
        } else {
            $page = $request['web_page'];
            $filter = $validator->validated();
        }

        $defaultLanguage = str_replace('_', '-', app()->getLocale());


        if ($request['web_page'] != 'text_setup' && $request['web_page'] != 'web_app') {
            foreach ($filter as $key => $value) {
                if ($key == 'meta_image') {
                    $value = $this->business_setting->where('key_name', $key)->first();
                    if (isset($value)) {
                        file_remover('landing-page/meta/', $value['live_values']);
                    }
                    $image = file_uploader('landing-page/meta/', 'png', $request->file('meta_image'));

                    $storageType = getDisk();
                    if($image && $storageType != 'public'){
                        saveBusinessImageDataToStorage(model: $value, modelColumn : 'meta_image', storageType : $storageType);
                    }
                }

                $business_data = $this->business_setting->updateOrCreate(['key_name' => $key], [
                    'key_name' => $key,
                    'live_values' => $key == 'meta_image' ? $image : $value,
                    'test_values' => $key == 'meta_image' ? $image : $value,
                    'settings_type' => 'landing_' . $page,
                    'mode' => 'live',
                    'is_active' => is_null($request[$key . '_is_active']) && $request[$key . '_is_active'] == 0 ? 0 : 1,
                ]);
            }
        } else {
            foreach ($textData as $key => $value) {
                $businessSettingRow = $this->dataSetting->updateOrCreate(['key' => $key, 'type' => 'landing_' . $page], [
                    'key' => $key,
                    'value' => $value,
                    'type' => 'landing_' . $page,
                    'is_active' => is_null($request[$key . '_is_active']) && $request[$key . '_is_active'] == 0 ? 0 : 1,
                ]);

                if ($request['web_page'] == 'text_setup') {
                    foreach ($request->lang as $index => $key_name) {
                        if ($defaultLanguage == $key_name && !($request[$key][$index])) {
                            if ($key_name != 'default') {
                                Translation::updateOrInsert(
                                    [
                                        'translationable_type' => 'Modules\BusinessSettingsModule\Entities\BusinessSettings',
                                        'translationable_id' => $businessSettingRow->id,
                                        'locale' => $key_name,
                                        'key' => $businessSettingRow->key],
                                    ['value' => $businessSettingRow[$key]]
                                );
                            }
                        } else {
                            if ($request[$key][$index] && $key_name != 'default') {
                                Translation::updateOrInsert(
                                    [
                                        'translationable_type' => 'Modules\BusinessSettingsModule\Entities\DataSetting',
                                        'translationable_id' => $businessSettingRow->id,
                                        'locale' => $key_name,
                                        'key' => $businessSettingRow->key],
                                    ['value' => $request[$key][$index]]
                                );
                            }
                        }
                    }

                } elseif ($request['web_page'] == 'web_app') {
                    foreach ($request->lang as $index => $key_name) {
                        if ($defaultLanguage == $key_name && !($request[$key][$index])) {
                            if ($key_name != 'default') {
                                Translation::updateOrInsert(
                                    [
                                        'translationable_type' => 'Modules\BusinessSettingsModule\Entities\DataSetting',
                                        'translationable_id' => $businessSettingRow->id,
                                        'locale' => $key_name,
                                        'key' => $businessSettingRow->key],
                                    ['value' => $businessSettingRow[$key]]
                                );
                            }
                        } else {
                            if ($request[$key][$index] && $key_name != 'default') {
                                Translation::updateOrInsert(
                                    [
                                        'translationable_type' => 'Modules\BusinessSettingsModule\Entities\DataSetting',
                                        'translationable_id' => $businessSettingRow->id,
                                        'locale' => $key_name,
                                        'key' => $businessSettingRow->key],
                                    ['value' => $request[$key][$index]]
                                );
                            }
                        }
                    }
                }
            }
        }


        if ($request->ajax()) {
            return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
        }
        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();
    }


    /**
     * Display a listing of the resource.
     * @param $page
     * @param $id
     * @return RedirectResponse
     */
    public function deleteLandingInformation($page, $id): RedirectResponse
    {
        $this->authorize('landing_delete');
        $array = [];
        if ($page == 'social_media') {
            $data = $this->business_setting->where('settings_type', 'landing_social_media')->first();
            foreach ($data->live_values as $value) {
                if ($value['id'] != $id) {
                    $array[] = $value;
                }
            }
        }

        $this->business_setting->updateOrCreate(['key_name' => $page], [
            'key_name' => $page,
            'live_values' => $array,
            'test_values' => $array,
            'settings_type' => 'landing_' . $page,
            'mode' => 'live',
            'is_active' => 1,
        ]);

        Toastr::success(translate(DEFAULT_DELETE_200['message']));
        return back();
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function setServiceSetup(Request $request): JsonResponse
    {
        $request[$request['key']] = $request['value'];

        $validator = Validator::make($request->all(), [
            'schedule_booking' => 'in:1,0',
            'provider_can_cancel_booking' => 'in:1,0',
            'serviceman_can_cancel_booking' => 'in:1,0',
            'admin_order_notification' => 'in:1,0',
            'sms_verification' => 'in:1,0',
            'email_verification' => 'in:1,0',
            'provider_self_registration' => 'in:1,0'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        foreach ($validator->validated() as $key => $value) {
            $this->business_setting->updateOrCreate(['key_name' => $key, 'settings_type' => 'service_setup'], [
                'key_name' => $key,
                'live_values' => $value,
                'test_values' => $value,
                'is_active' => $value,
                'settings_type' => 'service_setup',
                'mode' => 'live',
            ]);
        }

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Application|Factory|View
     */
    public function getPagesSetup(Request $request): View|Factory|Application
    {
        $webPage = $request->has('web_page') ? $request['web_page'] : 'about_us';
        return view('businesssettingsmodule::admin.page-settings', compact('webPage'));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function setPagesSetup(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page_name' => 'required|in:about_us,privacy_policy,terms_and_conditions,refund_policy,cancellation_policy',
            'page_content' => ''
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $this->business_setting->updateOrCreate(['key_name' => $request['page_name'], 'settings_type' => 'pages_setup'], [
            'key_name' => $request['page_name'],
            'live_values' => $request['page_content'],
            'test_values' => null,
            'settings_type' => 'pages_setup',
            'mode' => 'live',
            'is_active' => $request['is_active'] ?? 0,
        ]);

        if (in_array($request['page_name'], ['privacy_policy', 'terms_and_conditions'])) {
            $message = translate('page_information_has_been_updated') . '!';
            topic_notification('customer', $request['page_name'], $message, 'def.png', null, $request['page_name']);
        }

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

    public function setLandingSpeciality(Request $request): RedirectResponse
    {
        $this->authorize('landing_update');
        $request->validate([
            'title.0' => 'required',
            'description.0' => 'required',
            'image' => 'image',
        ],
            [
                'title.0.required' => translate('default_title_is_required'),
                'description.0.required' => translate('default_description_is_required'),
            ]
        );

        $speciality = $this->speciality;
        $speciality->title = $request->title[array_search('default', $request->lang)];
        $speciality->description = $request->description[array_search('default', $request->lang)];
        $speciality->image = file_uploader('landing-page/', 'png', $request->file('image'));
        $speciality->save();

        $defaultLanguage = str_replace('_', '-', app()->getLocale());

        foreach ($request->lang as $index => $key) {
            if ($defaultLanguage == $key && !($request->title[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageSpeciality',
                            'translationable_id' => $speciality->id,
                            'locale' => $key,
                            'key' => 'title'],
                        ['value' => $speciality->title]
                    );
                }
            } else {

                if ($request->title[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageSpeciality',
                            'translationable_id' => $speciality->id,
                            'locale' => $key,
                            'key' => 'title'],
                        ['value' => $request->title[$index]]
                    );
                }
            }

            if ($defaultLanguage == $key && !($request->description[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageSpeciality',
                            'translationable_id' => $speciality->id,
                            'locale' => $key,
                            'key' => 'description'],
                        ['value' => $speciality->description]
                    );
                }
            } else {

                if ($request->description[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageSpeciality',
                            'translationable_id' => $speciality->id,
                            'locale' => $key,
                            'key' => 'description'],
                        ['value' => $request->description[$index]]
                    );
                }
            }
        }

        Toastr::success(translate(DEFAULT_STORE_200['message']));
        return back();
    }

    public function deleteLandingSpeciality($id): RedirectResponse
    {
        $this->authorize('landing_delete');
        $speciality = $this->speciality->where('id', $id)->first();
        if (isset($speciality)) {
            file_remover('landing-page/', $speciality->image);
            $speciality->translations()->delete();
            $speciality->delete();
            Toastr::success(translate(DEFAULT_DELETE_200['message']));
            return back();
        }
        Toastr::success(translate(DEFAULT_204['message']));
        return back();
    }

    public function setLandingTestimonial(Request $request): RedirectResponse
    {
        $this->authorize('landing_update');
        $request->validate([
            'name.0' => 'required',
            'designation.0' => 'required',
            'review.0' => 'required',
            'image' => 'image',
        ],
            [
                'name.0.required' => translate('default_name_is_required'),
                'designation.0.required' => translate('default_designation_is_required'),
                'review.0.required' => translate('default_review_is_required'),
            ]
        );

        $testimonial = $this->testimonial;
        $testimonial->name = $request->name[array_search('default', $request->lang)];
        $testimonial->designation = $request->designation[array_search('default', $request->lang)];
        $testimonial->review = $request->review[array_search('default', $request->lang)];
        $testimonial->image = file_uploader('landing-page/', 'png', $request->file('image'));
        $testimonial->save();

        $defaultLanguage = str_replace('_', '-', app()->getLocale());

        foreach ($request->lang as $index => $key) {
            if ($defaultLanguage == $key && !($request->name[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageTestimonial',
                            'translationable_id' => $testimonial->id,
                            'locale' => $key,
                            'key' => 'name'],
                        ['value' => $testimonial->name]
                    );
                }
            } else {

                if ($request->name[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageTestimonial',
                            'translationable_id' => $testimonial->id,
                            'locale' => $key,
                            'key' => 'name'],
                        ['value' => $request->name[$index]]
                    );
                }
            }

            if ($defaultLanguage == $key && !($request->designation[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageTestimonial',
                            'translationable_id' => $testimonial->id,
                            'locale' => $key,
                            'key' => 'designation'],
                        ['value' => $testimonial->designation]
                    );
                }
            } else {

                if ($request->designation[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageTestimonial',
                            'translationable_id' => $testimonial->id,
                            'locale' => $key,
                            'key' => 'designation'],
                        ['value' => $request->designation[$index]]
                    );
                }
            }

            if ($defaultLanguage == $key && !($request->review[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageTestimonial',
                            'translationable_id' => $testimonial->id,
                            'locale' => $key,
                            'key' => 'review'],
                        ['value' => $testimonial->review]
                    );
                }
            } else {

                if ($request->review[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageTestimonial',
                            'translationable_id' => $testimonial->id,
                            'locale' => $key,
                            'key' => 'review'],
                        ['value' => $request->review[$index]]
                    );
                }
            }
        }

        Toastr::success(translate(DEFAULT_STORE_200['message']));
        return back();
    }

    public function deleteLandingTestimonial($id): RedirectResponse
    {
        $this->authorize('landing_delete');
        $testimonial = $this->testimonial->where('id', $id)->first();
        if (isset($testimonial)) {
            file_remover('landing-page/', $testimonial->image);
            $testimonial->translations()->delete();
            $testimonial->delete();
            Toastr::success(translate(DEFAULT_DELETE_200['message']));
            return back();
        }
        Toastr::success(translate(DEFAULT_204['message']));
        return back();
    }

    public function setLandingFeature(Request $request): RedirectResponse
    {
        $this->authorize('landing_update');
        $request->validate([
            'title.0' => 'required',
            'sub_title.0' => 'required',
            'image_1' => 'image',
            'image_2' => 'image',
        ],
            [
                'title.0.required' => translate('default_title_is_required'),
                'sub_title.0.required' => translate('default_sub_title_is_required'),
            ]
        );

        $feature = $this->feature;
        $feature->title = $request->title[array_search('default', $request->lang)];
        $feature->sub_title = $request->sub_title[array_search('default', $request->lang)];
        $feature->image_1 = file_uploader('landing-page/', 'png', $request->file('image_1'));
        $feature->image_2 = file_uploader('landing-page/', 'png', $request->file('image_2'));
        $feature->save();

        $defaultLanguage = str_replace('_', '-', app()->getLocale());

        foreach ($request->lang as $index => $key) {
            if ($defaultLanguage == $key && !($request->title[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageFeature',
                            'translationable_id' => $feature->id,
                            'locale' => $key,
                            'key' => 'title'],
                        ['value' => $feature->title]
                    );
                }
            } else {

                if ($request->title[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageFeature',
                            'translationable_id' => $feature->id,
                            'locale' => $key,
                            'key' => 'title'],
                        ['value' => $request->title[$index]]
                    );
                }
            }

            if ($defaultLanguage == $key && !($request->sub_title[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageFeature',
                            'translationable_id' => $feature->id,
                            'locale' => $key,
                            'key' => 'sub_title'],
                        ['value' => $feature->sub_title]
                    );
                }
            } else {

                if ($request->sub_title[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageFeature',
                            'translationable_id' => $feature->id,
                            'locale' => $key,
                            'key' => 'sub_title'],
                        ['value' => $request->sub_title[$index]]
                    );
                }
            }
        }

        Toastr::success(translate(DEFAULT_STORE_200['message']));
        return back();
    }

    public function deleteLandingFeature($id): RedirectResponse
    {
        $this->authorize('landing_delete');
        $feature = $this->feature->where('id', $id)->first();
        if (isset($feature)) {
            file_remover('landing-page/', $feature->image_1);
            file_remover('landing-page/', $feature->image_2);
            $feature->translations()->delete();
            $feature->delete();
            Toastr::success(translate(DEFAULT_DELETE_200['message']));
            return back();
        }
        Toastr::success(translate(DEFAULT_204['message']));
        return back();
    }

}
