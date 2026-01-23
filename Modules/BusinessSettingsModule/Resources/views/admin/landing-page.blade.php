@extends('adminmodule::layouts.master')

@section('title',translate('landing_page_setup'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/select.dataTables.min.css"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('landing_page_setup')}}</h2>
                    </div>

                    <div class="mb-3">
                        <ul class="nav nav--tabs nav--tabs__style2">
                            <li class="nav-item">
                                <a href="{{url()->current()}}?web_page=text_setup"
                                   class="nav-link {{$webPage=='text_setup'?'active':''}}">
                                    {{translate('text_setup')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{url()->current()}}?web_page=button_and_links"
                                   class="nav-link {{$webPage=='button_and_links'?'active':''}}">
                                    {{translate('button_&_links')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{url()->current()}}?web_page=speciality"
                                   class="nav-link {{$webPage=='speciality'?'active':''}}">
                                    {{translate('speciality')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{url()->current()}}?web_page=testimonial"
                                   class="nav-link {{$webPage=='testimonial'?'active':''}}">
                                    {{translate('testimonial')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{url()->current()}}?web_page=features"
                                   class="nav-link {{$webPage=='features'?'active':''}}">
                                    {{translate('features')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{url()->current()}}?web_page=images"
                                   class="nav-link {{$webPage=='images'?'active':''}}">
                                    {{translate('images')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{url()->current()}}?web_page=background"
                                   class="nav-link {{$webPage=='background'?'active':''}}">
                                    {{translate('background')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{url()->current()}}?web_page=social_media"
                                   class="nav-link {{$webPage=='social_media'?'active':''}}">
                                    {{translate('social_media')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{url()->current()}}?web_page=meta"
                                   class="nav-link {{$webPage=='meta'?'active':''}}">
                                    {{translate('meta')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{url()->current()}}?web_page=web_app"
                                   class="nav-link {{$webPage=='web_app'?'active':''}}">
                                    {{translate('Web_App')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{url()->current()}}?web_page=web_app_image"
                                   class="nav-link {{$webPage=='web_app_image'?'active':''}}">
                                    {{translate('Web_App')}} <small class="opacity-75">({{translate('Images')}})</small>
                                </a>
                            </li>
                        </ul>
                    </div>

                    @if($webPage=='text_setup')
                        @php($language= Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name','system_language')->first())
                        @php($defaultLanguage = str_replace('_', '-', app()->getLocale()))
                        @if($language)
                            <ul class="nav nav--tabs border-color-primary mb-4">
                                <li class="nav-item">
                                    <a class="nav-link lang_link active"
                                       href="#"
                                       id="default-link">{{translate('default')}}</a>
                                </li>
                                @foreach ($language?->live_values as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link lang_link"
                                           href="#"
                                           id="{{ $lang['code'] }}-link">{{ get_language_name($lang['code']) }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        <div class="tab-content">
                            <div class="tab-pane fade {{$webPage=='text_setup'?'active show':''}}">
                                <div class="card">
                                    <div class="card-body p-30">
                                        <form action="javascript:void(0)" method="POST" id="landing-info-update-form">
                                            @csrf
                                            @method('PUT')
                                            @if ($language)
                                                <div class="discount-type lang-form default-form">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="top_title[]"
                                                                           value="{{$dataValues->where('key','top_title')->first()?->getRawOriginal('value') ?? ''}}">
                                                                    <label>{{translate('top_title')}} *</label>
                                                                </div>
                                                            </div>

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="top_description[]"
                                                                           value="{{$dataValues->where('key','top_description')->first()?->getRawOriginal('value') ?? ''}}"
                                                                    >
                                                                    <label>{{translate('top_description')}} *</label>
                                                                </div>
                                                            </div>

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="top_sub_title[]"
                                                                           value="{{$dataValues->where('key','top_sub_title')->first()?->getRawOriginal('value') ?? ''}}"
                                                                    >
                                                                    <label>{{translate('top_sub_title')}} *</label>
                                                                </div>
                                                            </div>

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="mid_title[]"
                                                                           value="{{$dataValues->where('key','mid_title')->first()?->getRawOriginal('value') ?? ''}}"
                                                                    >
                                                                    <label>{{translate('mid_title')}} *</label>
                                                                </div>
                                                            </div>

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="about_us_title[]"
                                                                           value="{{$dataValues->where('key','about_us_title')->first()?->getRawOriginal('value') ?? ''}}"
                                                                    >
                                                                    <label>{{translate('about_us_title')}} *</label>
                                                                </div>
                                                            </div>

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="about_us_description[]"
                                                                           value="{{$dataValues->where('key','about_us_description')->first()?->getRawOriginal('value') ?? ''}}"
                                                                    >
                                                                    <label>{{translate('about_us_description')}}
                                                                        *</label>
                                                                </div>
                                                            </div>

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="registration_title[]"
                                                                           value="{{$dataValues->where('key','registration_title')->first()?->getRawOriginal('value') ?? ''}}"
                                                                    >
                                                                    <label>{{translate('registration_title')}} *</label>
                                                                </div>
                                                            </div>

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="registration_description[]"
                                                                           value="{{$dataValues->where('key','registration_description')->first()?->getRawOriginal('value') ?? ''}}"
                                                                    >
                                                                    <label>{{translate('registration_description')}}
                                                                        *</label>
                                                                </div>
                                                            </div>

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="bottom_title[]"
                                                                           value="{{$dataValues->where('key','bottom_title')->first()?->getRawOriginal('value') ?? ''}}"
                                                                    >
                                                                    <label>{{translate('bottom_title')}} *</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="lang[]" value="default">
                                                @foreach ($language?->live_values as $lang)
                                                        <?php
                                                        $topTitle = $dataValues->where('key', 'top_title')->where('type', 'landing_text_setup')->first();
                                                        $topDescription = $dataValues->where('key', 'top_description')->where('type', 'landing_text_setup')->first();
                                                        $topSubTitle = $dataValues->where('key', 'top_sub_title')->where('type', 'landing_text_setup')->first();
                                                        $midTitle = $dataValues->where('key', 'mid_title')->where('type', 'landing_text_setup')->first();
                                                        $aboutUsTitle = $dataValues->where('key', 'about_us_title')->where('type', 'landing_text_setup')->first();
                                                        $aboutUsDescription = $dataValues->where('key', 'about_us_description')->where('type', 'landing_text_setup')->first();
                                                        $registrationTitle = $dataValues->where('key', 'registration_title')->where('type', 'landing_text_setup')->first();
                                                        $registrationDescription = $dataValues->where('key', 'registration_description')->where('type', 'landing_text_setup')->first();
                                                        $bottomTitle = $dataValues->where('key', 'bottom_title')->where('type', 'landing_text_setup')->first();
                                                        if (isset($topTitle['translations']) && count($topTitle['translations'])) {
                                                            $topTitleTranslation = [];
                                                            foreach ($topTitle['translations'] as $t) {

                                                                if ($t->locale == $lang['code'] && $t->key == "top_title") {
                                                                    $topTitleTranslation[$lang['code']]['top_title'] = $t->value;
                                                                }
                                                            }
                                                        }

                                                        if (isset($topDescription['translations']) && count($topDescription['translations'])) {
                                                            $topDescriptionTranslation = [];
                                                            foreach ($topDescription['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == "top_description") {
                                                                    $topDescriptionTranslation[$lang['code']]['top_description'] = $t->value;
                                                                }
                                                            }
                                                        }

                                                        if (isset($topSubTitle['translations']) && count($topSubTitle['translations'])) {
                                                            $topSubTitleTranslation = [];
                                                            foreach ($topSubTitle['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == "top_sub_title") {
                                                                    $topSubTitleTranslation[$lang['code']]['top_sub_title'] = $t->value;
                                                                }
                                                            }
                                                        }

                                                        if (isset($midTitle['translations']) && count($midTitle['translations'])) {
                                                            $midTitleTranslation = [];
                                                            foreach ($midTitle['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == "mid_title") {
                                                                    $midTitleTranslation[$lang['code']]['mid_title'] = $t->value;
                                                                }
                                                            }
                                                        }

                                                        if (isset($aboutUsTitle['translations']) && count($aboutUsTitle['translations'])) {
                                                            $aboutUsTitleTranslation = [];
                                                            foreach ($aboutUsTitle['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == "about_us_title") {
                                                                    $aboutUsTitleTranslation[$lang['code']]['about_us_title'] = $t->value;
                                                                }
                                                            }
                                                        }

                                                        if (isset($aboutUsDescription['translations']) && count($aboutUsDescription['translations'])) {
                                                            $aboutUsDescriptionTranslation = [];
                                                            foreach ($aboutUsDescription['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == "about_us_description") {
                                                                    $aboutUsDescriptionTranslation[$lang['code']]['about_us_description'] = $t->value;
                                                                }
                                                            }
                                                        }

                                                        if (isset($registrationTitle['translations']) && count($registrationTitle['translations'])) {
                                                            $registrationTitleTranslation = [];
                                                            foreach ($registrationTitle['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == "registration_title") {
                                                                    $registrationTitleTranslation[$lang['code']]['registration_title'] = $t->value;
                                                                }
                                                            }
                                                        }

                                                        if (isset($registrationDescription['translations']) && count($registrationDescription['translations'])) {
                                                            $registrationDescriptionTranslation = [];
                                                            foreach ($registrationDescription['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == "registration_description") {
                                                                    $registrationDescriptionTranslation[$lang['code']]['registration_description'] = $t->value;
                                                                }
                                                            }
                                                        }

                                                        if (isset($bottomTitle['translations']) && count($bottomTitle['translations'])) {
                                                            $bottomTitleTranslation = [];
                                                            foreach ($bottomTitle['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == "bottom_title") {
                                                                    $bottomTitleTranslation[$lang['code']]['bottom_title'] = $t->value;
                                                                }
                                                            }
                                                        }
                                                        ?>

                                                    <div class="discount-type d-none lang-form {{$lang['code']}}-form">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="top_title[]"
                                                                               value="{{ $topTitleTranslation[$lang['code']]['top_title'] ?? ''}}"
                                                                               @if($lang['status'] == '1') oninvalid="document.getElementById('{{$lang['code']}}-link').click()" @endif>
                                                                        <label>{{translate('top_title')}} *</label>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="top_description[]"
                                                                               value="{{ $topDescriptionTranslation[$lang['code']]['top_description'] ?? ''}}"
                                                                               @if($lang['status'] == '1') oninvalid="document.getElementById('{{$lang['code']}}-link').click()" @endif>
                                                                        <label>{{translate('top_description')}}
                                                                            *</label>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="top_sub_title[]"
                                                                               value="{{ $topSubTitleTranslation[$lang['code']]['top_sub_title'] ?? ''}}"
                                                                               @if($lang['status'] == '1') oninvalid="document.getElementById('{{$lang['code']}}-link').click()" @endif>
                                                                        <label>{{translate('top_sub_title')}} *</label>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="mid_title[]"
                                                                               value="{{ $midTitleTranslation[$lang['code']]['mid_title'] ?? ''}}"
                                                                               @if($lang['status'] == '1') oninvalid="document.getElementById('{{$lang['code']}}-link').click()" @endif>
                                                                        <label>{{translate('mid_title')}} *</label>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="about_us_title[]"
                                                                               value="{{ $aboutUsTitleTranslation[$lang['code']]['about_us_title'] ?? ''}}"
                                                                               @if($lang['status'] == '1') oninvalid="document.getElementById('{{$lang['code']}}-link').click()" @endif>
                                                                        <label>{{translate('about_us_title')}} *</label>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="about_us_description[]"
                                                                               value="{{ $aboutUsDescriptionTranslation[$lang['code']]['about_us_description'] ?? ''}}"
                                                                               @if($lang['status'] == '1') oninvalid="document.getElementById('{{$lang['code']}}-link').click()" @endif>
                                                                        <label>{{translate('about_us_description')}}
                                                                            *</label>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="registration_title[]"
                                                                               value="{{ $registrationTitleTranslation[$lang['code']]['registration_title'] ?? ''}}"
                                                                               @if($lang['status'] == '1') oninvalid="document.getElementById('{{$lang['code']}}-link').click()" @endif>
                                                                        <label>{{translate('registration_title')}}
                                                                            *</label>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="registration_description[]"
                                                                               value="{{ $registrationDescriptionTranslation[$lang['code']]['registration_description'] ?? ''}}"
                                                                               @if($lang['status'] == '1') oninvalid="document.getElementById('{{$lang['code']}}-link').click()" @endif>
                                                                        <label>{{translate('registration_description')}}
                                                                            *</label>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="bottom_title[]"
                                                                               value="{{ $bottomTitleTranslation[$lang['code']]['bottom_title'] ?? ''}}"
                                                                               @if($lang['status'] == '1') oninvalid="document.getElementById('{{$lang['code']}}-link').click()" @endif>
                                                                        <label>{{translate('bottom_title')}} *</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="lang[]" value="{{$lang['code']}}">
                                                @endforeach
                                            @else
                                                <div class="discount-type lang-form">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="top_title[]"
                                                                           value="{{$dataValues->where('key','top_title')->first()->value ?? ''}}">
                                                                    <label>{{translate('top_title')}} *</label>
                                                                </div>
                                                            </div>

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="top_description[]"
                                                                           value="{{$dataValues->where('key','top_description')->first()->value ?? ''}}">
                                                                    <label>{{translate('top_description')}} *</label>
                                                                </div>
                                                            </div>

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="top_sub_title[]"
                                                                           value="{{$dataValues->where('key','top_sub_title')->first()->value ?? ''}}">
                                                                    <label>{{translate('top_sub_title')}} *</label>
                                                                </div>
                                                            </div>

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="mid_title[]"
                                                                           value="{{$dataValues->where('key','mid_title')->first()->value ?? ''}}">
                                                                    <label>{{translate('mid_title')}} *</label>
                                                                </div>
                                                            </div>

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="about_us_title[]"
                                                                           value="{{$dataValues->where('key','about_us_title')->first()->value ?? ''}}">
                                                                    <label>{{translate('about_us_title')}} *</label>
                                                                </div>
                                                            </div>

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="about_us_description[]"
                                                                           value="{{$dataValues->where('key','about_us_description')->first()->value ?? ''}}">
                                                                    <label>{{translate('about_us_description')}}
                                                                        *</label>
                                                                </div>
                                                            </div>

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="registration_title[]"
                                                                           value="{{$dataValues->where('key','registration_title')->first()->value ?? ''}}">
                                                                    <label>{{translate('registration_title')}} *</label>
                                                                </div>
                                                            </div>

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="registration_description[]"
                                                                           value="{{$dataValues->where('key','registration_description')->first()->value ?? ''}}">
                                                                    <label>{{translate('registration_description')}}
                                                                        *</label>
                                                                </div>
                                                            </div>

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="bottom_title[]"
                                                                           value="{{$dataValues->where('key','bottom_title')->first()->value ?? ''}}">
                                                                    <label>{{translate('bottom_title')}} *</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="lang[]" value="default">
                                            @endif

                                            @can('landing_update')
                                                <div class="d-flex gap-2 justify-content-end">
                                                    <button type="reset" class="btn btn-secondary">
                                                        {{translate('reset')}}
                                                    </button>
                                                    <button type="submit" class="btn btn--primary">
                                                        {{translate('update')}}
                                                    </button>
                                                </div>
                                            @endcan
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($webPage=='button_and_links')
                        <div class="tab-content">
                            <div class="tab-pane fade {{$webPage=='button_and_links'?'active show':''}}">
                                <div class="card">
                                    <div class="card-body p-30">
                                        <form action="javascript:void(0)" method="POST" id="landing-info-update-form">
                                            @csrf
                                            @method('PUT')
                                            <div class="discount-type">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        @php($value=$dataValues->where('key_name','app_url_playstore')->first()->is_active??0)
                                                        <label class="switcher mb-4">
                                                            <input class="switcher_input" type="checkbox"
                                                                   name="app_url_playstore_is_active"
                                                                   {{$value?'checked':''}}
                                                                   value="1">
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                        <div class="mb-30">
                                                            <div class="form-floating">
                                                                <input type="text" class="form-control"
                                                                       name="app_url_playstore"
                                                                       value="{{$dataValues->where('key_name','app_url_playstore')->first()->live_values??''}}">
                                                                <label>
                                                                    {{translate('app_url_( playstore )')}}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        @php($value=$dataValues->where('key_name','app_url_appstore')->first()->is_active??0)
                                                        <label class="switcher mb-4">
                                                            <input class="switcher_input" type="checkbox"
                                                                   name="app_url_appstore_is_active"
                                                                   {{$value?'checked':''}}
                                                                   value="1">
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                        <div class="mb-30">
                                                            <div class="form-floating">
                                                                <input type="text" class="form-control"
                                                                       name="app_url_appstore"
                                                                       value="{{$dataValues->where('key_name','app_url_appstore')->first()->live_values??''}}">
                                                                <label>
                                                                    {{translate('app_url_( appstore )')}}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        @php($value=$dataValues->where('key_name','web_url')->first()->is_active??0)
                                                        <label class="switcher mb-4">
                                                            <input class="switcher_input" type="checkbox"
                                                                   name="web_url_is_active"
                                                                   {{$value?'checked':''}}
                                                                   value="1">
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                        <div class="mb-30">
                                                            <div class="form-floating">
                                                                <input type="text" class="form-control"
                                                                       name="web_url"
                                                                       value="{{$dataValues->where('key_name','web_url')->first()->live_values??''}}">
                                                                <label>{{translate('web_url')}} *</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                            @can('landing_update')
                                                <div class="d-flex gap-2 justify-content-end">
                                                    <button type="reset" class="btn btn-secondary">
                                                        {{translate('reset')}}
                                                    </button>
                                                    <button type="submit" class="btn btn--primary">
                                                        {{translate('update')}}
                                                    </button>
                                                </div>
                                            @endcan
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($webPage=='speciality')
                        @php($language= Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name','system_language')->first())
                        @php($defaultLanguage = str_replace('_', '-', app()->getLocale()))
                        @if($language)
                            <ul class="nav nav--tabs border-color-primary mb-4">
                                <li class="nav-item">
                                    <a class="nav-link lang_link active"
                                       href="#"
                                       id="default-link">{{translate('default')}}</a>
                                </li>
                                @foreach ($language?->live_values as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link lang_link"
                                           href="#"
                                           id="{{ $lang['code'] }}-link">{{ get_language_name($lang['code']) }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        <div class="tab-content">
                            <div class="tab-pane fade {{$webPage=='speciality'?'active show':''}}">
                                <div class="card">
                                    <div class="card-body p-30">
                                        <form
                                            action="{{route('admin.business-settings.set-landing-speciality')}}?web_page={{$webPage}}"
                                            method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <div class="discount-type">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        @if($language)
                                                            <div class="lang-form default-form">
                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="title[]">
                                                                        <label>
                                                                            {{translate('speciality_title')}}
                                                                        </label>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="description[]">
                                                                        <label>
                                                                            {{translate('speciality_description')}}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <input type="hidden" name="lang[]" value="default">
                                                            @foreach ($language?->live_values as $lang)
                                                                <div
                                                                    class="mb-30 d-none lang-form {{$lang['code']}}-form">
                                                                    <div class="mb-30">
                                                                        <div class="form-floating">
                                                                            <input type="text" class="form-control"
                                                                                   name="title[]"
                                                                                   @if($lang['status'] == '1') oninvalid="document.getElementById('{{$lang['code']}}-link').click()" @endif>
                                                                            <label>
                                                                                {{translate('speciality_title')}}
                                                                            </label>
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-30">
                                                                        <div class="form-floating">
                                                                            <input type="text" class="form-control"
                                                                                   name="description[]"
                                                                                   @if($lang['status'] == '1') oninvalid="document.getElementById('{{$lang['code']}}-link').click()" @endif>
                                                                            <label>
                                                                                {{translate('speciality_description')}}
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <input type="hidden" name="lang[]"
                                                                       value="{{$lang['code']}}">
                                                            @endforeach
                                                        @else
                                                            <div class="lang-form">
                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="title[]">
                                                                        <label>
                                                                            {{translate('speciality_title')}}
                                                                        </label>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="description[]">
                                                                        <label>
                                                                            {{translate('speciality_description')}}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <input type="hidden" name="lang[]" value="default">
                                                        @endif
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-30 d-flex flex-column align-items-center gap-2">
                                                            <div class="upload-file mb-30 max-w-100">
                                                                <input type="file" class="upload-file__input"
                                                                       name="image"
                                                                       accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*">
                                                                <div class="upload-file__img">
                                                                    <img
                                                                        src='{{asset('public/assets/admin-module/img/media/upload-file.png')}}'
                                                                        alt="">
                                                                </div>
                                                                <span class="upload-file__edit">
                                                                    <span class="material-icons">edit</span>
                                                                </span>
                                                            </div>
                                                            <p class="opacity-75 max-w220">{{translate('Image format - jpg, png, jpeg, gif Image Size - maximum size 2 MB Image Ratio - 1:1')}}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @can('landing_update')
                                                <div class="d-flex gap-2 justify-content-end">
                                                    <button type="reset" class="btn btn-secondary">
                                                        {{translate('reset')}}
                                                    </button>
                                                    <button type="submit" class="btn btn--primary">
                                                        {{translate('add')}}
                                                    </button>
                                                </div>
                                            @endcan
                                        </form>
                                    </div>

                                    <div class="card-body p-30">
                                        <div class="table-responsive">
                                            <table id="example" class="table align-middle">
                                                <thead>
                                                <tr>
                                                    <th>{{translate('title')}}</th>
                                                    <th>{{translate('description')}}</th>
                                                    <th>{{translate('image')}}</th>
                                                    <th>{{translate('action')}}</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($specialities??[] as $key=>$item)
                                                    <tr>
                                                        <td>{{$item['title']}}</td>
                                                        <td>{{$item['description']}}</td>
                                                        <td>
                                                            <img class="landing-images" src="{{ $item['image_full_path'] }}" alt="{{ translate('landing-images') }}">
                                                        </td>
                                                        <td>
                                                            @can('landing_delete')
                                                                <div class="table-actions">
                                                                    <button type="button"
                                                                            data-id="delete-{{$item['id']}}"
                                                                            class="table-actions_delete action-btn btn--danger bg-transparent border-0 p-0">
                                                                        <span class="material-icons">delete</span>
                                                                    </button>
                                                                    <form
                                                                        action="{{route('admin.business-settings.delete-landing-speciality',[$item['id']])}}"
                                                                        method="post" id="delete-{{$item['id']}}"
                                                                        class="hidden">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                    </form>
                                                                </div>
                                                            @endcan
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($webPage=='testimonial')
                        @php($language= Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name','system_language')->first())
                        @php($defaultLanguage = str_replace('_', '-', app()->getLocale()))
                        @if($language)
                            <ul class="nav nav--tabs border-color-primary mb-4">
                                <li class="nav-item">
                                    <a class="nav-link lang_link active"
                                       href="#"
                                       id="default-link">{{translate('default')}}</a>
                                </li>
                                @foreach ($language?->live_values as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link lang_link"
                                           href="#"
                                           id="{{ $lang['code'] }}-link">{{ get_language_name($lang['code']) }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        <div class="tab-content">
                            <div class="tab-pane fade {{$webPage=='testimonial'?'active show':''}}">
                                <div class="card">
                                    <div class="card-body p-30">
                                        <form
                                            action="{{route('admin.business-settings.set-landing-testimonial')}}?web_page={{$webPage}}"
                                            method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <div class="discount-type">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        @if($language)
                                                            <div class="lang-form default-form">
                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="name[]">
                                                                        <label>
                                                                            {{translate('reviewer_name')}}
                                                                        </label>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="designation[]">
                                                                        <label>
                                                                            {{translate('reviewer_designation')}}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="review[]">
                                                                        <label>
                                                                            {{translate('review')}}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <input type="hidden" name="lang[]" value="default">
                                                            @foreach ($language?->live_values as $lang)
                                                                <div
                                                                    class="mb-30 d-none lang-form {{$lang['code']}}-form">
                                                                    <div class="mb-30">
                                                                        <div class="form-floating">
                                                                            <input type="text" class="form-control"
                                                                                   name="name[]"
                                                                                   @if($lang['status'] == '1') oninvalid="document.getElementById('{{$lang['code']}}-link').click()" @endif>
                                                                            <label>
                                                                                {{translate('reviewer_name')}}
                                                                            </label>
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-30">
                                                                        <div class="form-floating">
                                                                            <input type="text" class="form-control"
                                                                                   name="designation[]"
                                                                                   @if($lang['status'] == '1') oninvalid="document.getElementById('{{$lang['code']}}-link').click()" @endif>
                                                                            <label>
                                                                                {{translate('reviewer_designation')}}
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-30">
                                                                        <div class="form-floating">
                                                                            <input type="text" class="form-control"
                                                                                   name="review[]"
                                                                                   @if($lang['status'] == '1') oninvalid="document.getElementById('{{$lang['code']}}-link').click()" @endif>
                                                                            <label>
                                                                                {{translate('review')}}
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <input type="hidden" name="lang[]"
                                                                       value="{{$lang['code']}}">
                                                            @endforeach
                                                        @else
                                                            <div class="lang-form">
                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="name[]">
                                                                        <label>
                                                                            {{translate('reviewer_name')}}
                                                                        </label>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="designation[]">
                                                                        <label>
                                                                            {{translate('reviewer_designation')}}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="review[]">
                                                                        <label>
                                                                            {{translate('review')}}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="mb-30 d-flex flex-column align-items-center gap-2">
                                                            <div class="upload-file mb-30 max-w-100">
                                                                <input type="file" class="upload-file__input"
                                                                       name="image"
                                                                       accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*">
                                                                <div class="upload-file__img">
                                                                    <img
                                                                        src='{{asset('public/assets/admin-module/img/media/upload-file.png')}}'
                                                                        alt="">
                                                                </div>
                                                                <span class="upload-file__edit">
                                                                    <span class="material-icons">edit</span>
                                                                </span>
                                                            </div>
                                                            <p class="opacity-75 max-w220">{{translate('Image format - jpg, png, jpeg, gif Image Size - maximum size 2 MB Image Ratio - 1:1')}}</p>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                            @can('landing_update')
                                                <div class="d-flex gap-2 justify-content-end">
                                                    <button type="reset" class="btn btn-secondary">
                                                        {{translate('reset')}}
                                                    </button>
                                                    <button type="submit" class="btn btn--primary">
                                                        {{translate('add')}}
                                                    </button>
                                                </div>
                                            @endcan
                                        </form>
                                    </div>

                                    <div class="card-body p-30">
                                        <div class="table-responsive">
                                            <table id="example" class="table align-middle">
                                                <thead>
                                                <tr>
                                                    <th>{{translate('name')}}</th>
                                                    <th>{{translate('designation')}}</th>
                                                    <th>{{translate('review')}}</th>
                                                    <th>{{translate('image')}}</th>
                                                    <th>{{translate('action')}}</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($testimonials ?? [] as $key=>$item)
                                                    <tr>
                                                        <td>{{$item['name']}}</td>
                                                        <td>{{$item['designation']}}</td>
                                                        <td>{{$item['review']}}</td>
                                                        <td>
                                                            <img class="landing-images"
                                                                 src="{{ $item['image_full_path'] }}" alt="{{translate('image')}}">
                                                        </td>
                                                        <td>
                                                            @can('landing_delete')
                                                                <div class="table-actions">
                                                                    <button type="button"
                                                                            data-id="delete-{{$item['id']}}"
                                                                            class="table-actions_delete action-btn btn--danger bg-transparent border-0 p-0">
                                                                        <span class="material-icons">delete</span>
                                                                    </button>
                                                                    <form
                                                                        action="{{route('admin.business-settings.delete-landing-testimonial',[$item['id']])}}"
                                                                        method="post" id="delete-{{$item['id']}}"
                                                                        class="hidden">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                    </form>
                                                                </div>
                                                            @endcan
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($webPage=='features')
                        @php($language= Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name','system_language')->first())
                        @php($defaultLanguage = str_replace('_', '-', app()->getLocale()))
                        @if($language)
                            <ul class="nav nav--tabs border-color-primary mb-4">
                                <li class="nav-item">
                                    <a class="nav-link lang_link active"
                                       href="#"
                                       id="default-link">{{translate('default')}}</a>
                                </li>
                                @foreach ($language?->live_values as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link lang_link"
                                           href="#"
                                           id="{{ $lang['code'] }}-link">{{ get_language_name($lang['code']) }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        <div class="tab-content">
                            <div class="tab-pane fade {{$webPage=='features'?'active show':''}}">
                                <div class="card">
                                    <div class="card-body p-30">
                                        <form
                                            action="{{route('admin.business-settings.set-landing-feature')}}?web_page={{$webPage}}"
                                            method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <div class="discount-type">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        @if($language)
                                                            <div class="lang-form default-form">
                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="title[]">
                                                                        <label>{{translate('feature_title')}}</label>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="sub_title[]">
                                                                        <label>{{translate('feature_sub_title')}}</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <input type="hidden" name="lang[]" value="default">
                                                            @foreach ($language?->live_values as $lang)
                                                                <div
                                                                    class="mb-30 d-none lang-form {{$lang['code']}}-form">
                                                                    <div class="mb-30">
                                                                        <div class="form-floating">
                                                                            <input type="text" class="form-control"
                                                                                   name="title[]"
                                                                                   @if($lang['status'] == '1') oninvalid="document.getElementById('{{$lang['code']}}-link').click()" @endif>
                                                                            <label>{{translate('feature_title')}}</label>
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-30">
                                                                        <div class="form-floating">
                                                                            <input type="text" class="form-control"
                                                                                   name="sub_title[]"
                                                                                   @if($lang['status'] == '1') oninvalid="document.getElementById('{{$lang['code']}}-link').click()" @endif>
                                                                            <label>{{translate('feature_sub_title')}}</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <input type="hidden" name="lang[]"
                                                                       value="{{$lang['code']}}">
                                                            @endforeach
                                                        @else
                                                            <div class="lang-form">
                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="title[]">
                                                                        <label>{{translate('feature_title')}}</label>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="sub_title[]">
                                                                        <label>{{translate('feature_sub_title')}}</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <input type="hidden" name="lang[]" value="default">
                                                        @endif
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div
                                                            class="mb-30 d-flex flex-column align-items-center gap-2">
                                                            <div class="upload-file mb-30 max-w-100">
                                                                <input type="file" class="upload-file__input"
                                                                       name="image_1"
                                                                       accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*">
                                                                <div class="upload-file__img">
                                                                    <img
                                                                        src='{{asset('public/assets/admin-module/img/media/upload-file.png')}}'
                                                                        alt="">
                                                                </div>
                                                                <span class="upload-file__edit">
                                                                    <span class="material-icons">edit</span>
                                                                </span>
                                                            </div>
                                                            <p class="opacity-75 max-w220">{{translate('Image Size - 200x381')}}</p>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div
                                                            class="mb-30 d-flex flex-column align-items-center gap-2">
                                                            <div class="upload-file mb-30 max-w-100">
                                                                <input type="file" class="upload-file__input"
                                                                       name="image_2"
                                                                       accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*">
                                                                <div class="upload-file__img">
                                                                    <img
                                                                        src='{{asset('public/assets/admin-module/img/media/upload-file.png')}}'
                                                                        alt="">
                                                                </div>
                                                                <span class="upload-file__edit">
                                                                    <span class="material-icons">edit</span>
                                                                </span>
                                                            </div>
                                                            <p class="opacity-75 max-w220">{{translate('Image Size - 200x381')}}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            @can('landing_update')
                                                <div class="d-flex gap-2 justify-content-end">
                                                    <button type="reset" class="btn btn-secondary">
                                                        {{translate('reset')}}
                                                    </button>
                                                    <button type="submit" class="btn btn--primary">
                                                        {{translate('add')}}
                                                    </button>
                                                </div>
                                            @endcan
                                        </form>
                                    </div>

                                    <div class="card-body p-30">
                                        <div class="table-responsive">
                                            <table id="example" class="table align-middle">
                                                <thead>
                                                <tr>
                                                    <th>{{translate('title')}}</th>
                                                    <th>{{translate('sub_title')}}</th>
                                                    <th>{{translate('images')}}</th>
                                                    <th>{{translate('action')}}</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($features??[] as $key=>$item)
                                                    <tr>
                                                        <td>{{$item['title']}}</td>
                                                        <td>{{$item['sub_title']}}</td>
                                                        <td>
                                                            <img class="landing-images" src="{{$item['image_1_full_path']}}" alt="{{translate('image')}}">
                                                            <img class="landing-images" src="{{$item['image_2_full_path']}}" alt="{{translate('image')}}">
                                                        </td>
                                                        <td>
                                                            <div class="table-actions">
                                                                @can('landing_delete')
                                                                    <button type="button"
                                                                            data-id="delete-{{$item['id']}}"
                                                                            class="table-actions_delete action-btn btn--danger bg-transparent border-0 p-0">
                                                                        <span class="material-icons">delete</span>
                                                                    </button>
                                                                    <form
                                                                        action="{{route('admin.business-settings.delete-landing-feature',[$item['id']])}}"
                                                                        method="post" id="delete-{{$item['id']}}"
                                                                        class="hidden">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                    </form>
                                                                @endcan
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($webPage=='images')
                        <div class="tab-content">
                            <div class="tab-pane fade {{$webPage=='images'?'active show':''}}">
                                <div class="card">
                                    <div class="card-body p-30">
                                        <div class="discount-type">
                                            <div class="row">
                                                @php($keys = ['top_image_1', 'top_image_2', 'top_image_3', 'top_image_4', 'about_us_image', 'service_section_image', 'provider_section_image'])
                                                @php($ratios = ['370x200', '315x200', '200x200', '485x200', '684x440', '200x350', '238x228'])
                                                @foreach($keys as $index=>$key)
                                                    <div class="col-md-3 mb-30">
                                                        <form
                                                            action="{{route('admin.business-settings.set-landing-information')}}?web_page={{$webPage}}"
                                                            method="POST" enctype="multipart/form-data">
                                                            @csrf
                                                            @method('PUT')
                                                            <div
                                                                class="mb-1 d-flex flex-column align-items-center gap-2">
                                                                <p class="title-color text-center web-images">
                                                                    {{translate($key)}}, {{translate('size')}}
                                                                    :{{$ratios[$index]}}
                                                                </p>
                                                                <div class="upload-file max-w-100">
                                                                    <input type="file" class="upload-file__input"
                                                                           name="{{$key}}" id="image-{{$key}}"
                                                                           accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*">
                                                                    <div class="upload-file__img">
                                                                        @php($image = getBusinessSettingsImageFullPath(key: $key, settingType: 'landing_images', path: 'landing-page/',  defaultPath : 'public/assets/admin-module/img/media/upload-file.png'))
                                                                        <img src="{{ $image }}" alt="{{translate('image')}}">
                                                                    </div>
                                                                    <span class="upload-file__edit">
                                                                        <span class="material-icons">edit</span>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            @can('landing_update')
                                                                <div class="d-flex gap-2 justify-content-center">
                                                                    <button type="submit"
                                                                            class="btn btn--primary btn-block">
                                                                        {{translate('upload')}}
                                                                    </button>
                                                                </div>
                                                            @endcan
                                                        </form>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($webPage=='background')
                        <div class="tab-content">
                            <div class="tab-pane fade {{$webPage=='background'?'active show':''}}">
                                <div class="card">
                                    <div class="card-body p-30">
                                        <form action="javascript:void(0)" method="POST" id="landing-info-update-form">
                                            @csrf
                                            @method('PUT')
                                            <div class="discount-type">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="mb-30">
                                                            <div class="form-floating">
                                                                <input type="color" class="form-control"
                                                                       name="header_background"
                                                                       value="{{$dataValues->where('key_name','header_background')->first()->live_values??"#E3F2FC"}}">
                                                                <label>
                                                                    {{translate('header_background')}}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="mb-30">
                                                            <div class="form-floating">
                                                                <input type="color" class="form-control"
                                                                       name="body_background"
                                                                       value="{{$dataValues->where('key_name','body_background')->first()->live_values??'white'}}">
                                                                <label>
                                                                    {{translate('body_background')}}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="mb-30">
                                                            <div class="form-floating">
                                                                <input type="color" class="form-control"
                                                                       name="footer_background"
                                                                       value="{{$dataValues->where('key_name','footer_background')->first()->live_values??'#E3F2FC'}}">
                                                                <label>
                                                                    {{translate('footer_background')}}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                            @can('landing_update')
                                                <div class="d-flex gap-2 justify-content-end">
                                                    <button type="reset" class="btn btn-secondary">
                                                        {{translate('reset')}}
                                                    </button>
                                                    <button type="submit" class="btn btn--primary">
                                                        {{translate('update')}}
                                                    </button>
                                                </div>
                                            @endcan
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($webPage=='social_media')
                        <div class="tab-content">
                            <div class="tab-pane fade {{$webPage=='social_media'?'active show':''}}">
                                <div class="card">
                                    <div class="card-body p-30">
                                        <form
                                            action="{{route('admin.business-settings.set-landing-information')}}?web_page={{$webPage}}"
                                            method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <div class="discount-type">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-30">
                                                            <select class="js-select theme-input-style w-100"
                                                                    name="media" required>
                                                                <option value="" selected disabled>
                                                                    ---{{translate('Select_media')}}---
                                                                </option>
                                                                <option
                                                                    value="facebook">{{translate('Facebook')}}</option>
                                                                <option
                                                                    value="instagram">{{translate('Instagram')}}</option>
                                                                <option
                                                                    value="linkedin">{{translate('LinkedIn')}}</option>
                                                                <option
                                                                    value="twitter">{{translate('Twitter')}}</option>
                                                                <option
                                                                    value="youtube">{{translate('Youtube')}}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-30">
                                                            <div class="form-floating">
                                                                <input type="text" class="form-control" name="link"
                                                                       placeholder="{{translate('link')}}" required>
                                                                <label>{{translate('link')}}</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @can('landing_update')
                                                <div class="d-flex gap-2 justify-content-end">
                                                    <button type="reset" class="btn btn-secondary">
                                                        {{translate('reset')}}
                                                    </button>
                                                    <button type="submit" class="btn btn--primary">
                                                        {{translate('add')}}
                                                    </button>
                                                </div>
                                            @endcan
                                        </form>
                                    </div>

                                    <div class="card-body p-30">
                                        <div class="table-responsive">
                                            <table id="example" class="table align-middle">
                                                <thead>
                                                <tr>
                                                    <th>{{translate('media')}}</th>
                                                    <th>{{translate('link')}}</th>
                                                    <th>{{translate('action')}}</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($dataValues[0]->live_values??[] as $key=>$item)
                                                    <tr>
                                                        <td>{{$item['media']}}</td>
                                                        <td><a href="{{$item['link']}}">{{$item['link']}}</a></td>
                                                        <td>
                                                            <div class="table-actions">
                                                                @can('landing_delete')
                                                                    <button type="button"
                                                                            data-id="delete-{{$item['id']}}"
                                                                            class="action-btn btn--danger">
                                                                        <span class="material-icons">delete</span>
                                                                    </button>
                                                                    <form
                                                                        action="{{route('admin.business-settings.delete-landing-information',[$webPage,$item['id']])}}"
                                                                        method="post" id="delete-{{$item['id']}}"
                                                                        class="hidden">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                    </form>
                                                                @endcan
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($webPage=='meta')
                        <div class="tab-content">
                            <div class="tab-pane fade {{$webPage=='meta'?'active show':''}}">
                                <div class="card">
                                    <div class="card-body p-30">
                                        <form action="javascript:void(0)" method="POST" id="landing-info-update-form">
                                            @csrf
                                            @method('PUT')
                                            <div class="discount-type">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-30">
                                                            <div class="form-floating">
                                                                <input type="text" class="form-control"
                                                                       placeholder="{{translate('meta_title')}} *"
                                                                       name="meta_title"
                                                                       value="{{$dataValues->where('key_name','meta_title')->first()->live_values??''}}"
                                                                       required>
                                                                <label>
                                                                    {{translate('meta_title')}}
                                                                </label>
                                                            </div>
                                                        </div>

                                                        <div class="mb-30">
                                                            <div class="form-floating">
                                                                <input type="text" class="form-control"
                                                                       placeholder="{{translate('meta_description')}} *"
                                                                       name="meta_description"
                                                                       value="{{$dataValues->where('key_name','meta_description')->first()->live_values??''}}"
                                                                       required>
                                                                <label>
                                                                    {{translate('meta_description')}}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-30 d-flex flex-column align-items-center gap-2">
                                                            <div class="upload-file mb-30 max-w-100">
                                                                <input type="file" class="upload-file__input"
                                                                       name="meta_image"
                                                                       accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*">
                                                                <div class="upload-file__img">
                                                                    @php($image = getBusinessSettingsImageFullPath(key: 'meta_image', settingType: 'landing_meta', path: 'landing-page/meta/',  defaultPath : 'public/assets/placeholder.png'))
                                                                    <img src="{{ $image }}" alt="{{translate('image')}}">
                                                                </div>
                                                                <span class="upload-file__edit">
                                                                    <span class="material-icons">edit</span>
                                                                </span>
                                                            </div>
                                                            <p class="opacity-75 max-w220">{{translate('Image format - jpg, png, jpeg, gif Image Size - maximum size 2 MB Image Ratio - 1:1')}}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @can('landing_update')
                                                <div class="d-flex gap-2 justify-content-end">
                                                    <button type="reset" class="btn btn-secondary">
                                                        {{translate('reset')}}
                                                    </button>
                                                    <button type="submit" class="btn btn--primary">
                                                        {{translate('add')}}
                                                    </button>
                                                </div>
                                            @endcan
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($webPage=='web_app')
                        @php($language= Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name','system_language')->first())
                        @php($defaultLanguage = str_replace('_', '-', app()->getLocale()))
                        @if($language)
                            <ul class="nav nav--tabs border-color-primary mb-4">
                                <li class="nav-item">
                                    <a class="nav-link lang_link active"
                                       href="#"
                                       id="default-link">{{translate('default')}}</a>
                                </li>
                                @foreach ($language?->live_values as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link lang_link"
                                           href="#"
                                           id="{{ $lang['code'] }}-link">{{ get_language_name($lang['code']) }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        <div class="tab-content">
                            <div class="tab-pane fade {{$webPage=='web_app'?'active show':''}}">
                                <div class="card">
                                    <div class="card-body p-30">
                                        <form action="javascript:void(0)" method="POST" id="landing-info-update-form" novalidate>
                                            @csrf
                                            @method('PUT')
                                            @if($language)
                                                <div class="discount-type lang-form default-form">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="web_top_title[]"
                                                                           placeholder="{{translate('top_title')}}"
                                                                           value="{{$dataValues->where('key','web_top_title')->first()?->getRawOriginal('value') ?? ''}}"
                                                                    >
                                                                    <label>{{translate('top_title')}} *</label>
                                                                </div>
                                                            </div>

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="web_top_description[]"
                                                                           placeholder="{{translate('top_description')}}"
                                                                           value="{{$dataValues->where('key','web_top_description')->first()?->getRawOriginal('value') ?? ''}}"
                                                                    >
                                                                    <label>{{translate('top_description')}} *</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12">

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="web_mid_title[]"
                                                                           placeholder="{{translate('mid_title')}}"
                                                                           value="{{$dataValues->where('key','web_mid_title')->first()?->getRawOriginal('value') ?? ''}}"
                                                                    >
                                                                    <label>{{translate('mid_title')}} *</label>
                                                                </div>
                                                            </div>

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="mid_sub_title_1[]"
                                                                           value="{{$dataValues->where('key','mid_sub_title_1')->first()?->getRawOriginal('value') ?? ''}}"
                                                                           placeholder="{{translate('mid_sub_title_1')}}"
                                                                    >
                                                                    <label>{{translate('mid_sub_title_1')}} *</label>
                                                                </div>
                                                            </div>
                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="mid_sub_description_1[]"
                                                                           value="{{$dataValues->where('key','mid_sub_description_1')->first()?->getRawOriginal('value') ?? ''}}"
                                                                           placeholder="{{translate('mid_sub_description_1')}}"
                                                                    >
                                                                    <label>{{translate('mid_sub_description_1')}}
                                                                        *</label>
                                                                </div>
                                                            </div>
                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="mid_sub_title_2[]"
                                                                           value="{{$dataValues->where('key','mid_sub_title_2')->first()?->getRawOriginal('value') ?? ''}}"
                                                                           placeholder="{{translate('mid_sub_title_2')}}"
                                                                    >
                                                                    <label>{{translate('mid_sub_title_2')}} *</label>
                                                                </div>
                                                            </div>
                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="mid_sub_description_2[]"
                                                                           value="{{$dataValues->where('key','mid_sub_description_2')->first()?->getRawOriginal('value') ?? ''}}"
                                                                           placeholder="{{translate('mid_sub_description_2')}}"
                                                                    >
                                                                    <label>{{translate('mid_sub_description_2')}}
                                                                        *</label>
                                                                </div>
                                                            </div>
                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="mid_sub_title_3[]"
                                                                           value="{{$dataValues->where('key','mid_sub_title_3')->first()?->getRawOriginal('value') ?? ''}}"
                                                                           placeholder="{{translate('mid_sub_title_3')}}"
                                                                    >
                                                                    <label>{{translate('mid_sub_title_3')}} *</label>
                                                                </div>
                                                            </div>
                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="mid_sub_description_3[]"
                                                                           value="{{$dataValues->where('key','mid_sub_description_3')->first()?->getRawOriginal('value') ?? ''}}"
                                                                           placeholder="{{translate('mid_sub_description_3')}}"
                                                                    >
                                                                    <label>{{translate('mid_sub_description_3')}}
                                                                        *</label>
                                                                </div>
                                                            </div>

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="download_section_title[]"
                                                                           value="{{$dataValues->where('key','download_section_title')->first()?->getRawOriginal('value') ?? ''}}"
                                                                           placeholder="{{translate('download_section_title')}}"
                                                                    >
                                                                    <label>{{translate('download_section_title')}}
                                                                        *</label>
                                                                </div>
                                                            </div>
                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="download_section_description[]"
                                                                           value="{{$dataValues->where('key','download_section_description')->first()?->getRawOriginal('value') ?? ''}}"
                                                                           placeholder="{{translate('download_section_description')}}"
                                                                    >
                                                                    <label>{{translate('download_section_description')}}
                                                                        *</label>
                                                                </div>
                                                            </div>

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="web_bottom_title[]"
                                                                           value="{{$dataValues->where('key','web_bottom_title')->first()?->getRawOriginal('value') ?? ''}}"
                                                                           placeholder="{{translate('bottom_title')}}"
                                                                    >
                                                                    <label>{{translate('bottom_title')}} *</label>
                                                                </div>
                                                            </div>

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="testimonial_title[]"
                                                                           value="{{$dataValues->where('key','testimonial_title')->first()?->getRawOriginal('value') ?? ''}}"
                                                                           placeholder="{{translate('testimonial_title')}}"
                                                                    >
                                                                    <label>{{translate('testimonial_title')}} *</label>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="lang[]" value="default">
                                                @foreach ($language?->live_values as $lang)
                                                        <?php
                                                        $webTopTitle = $dataValues->where('key', 'web_top_title')->first();
                                                        $webTopDescription = $dataValues->where('key', 'web_top_description')->first();
                                                        $webMidTitle = $dataValues->where('key', 'web_mid_title')->first();
                                                        $midSubTitleOne = $dataValues->where('key', 'mid_sub_title_1')->first();
                                                        $midSubTitleTwo = $dataValues->where('key', 'mid_sub_title_2')->first();
                                                        $midSubDescriptionOne = $dataValues->where('key', 'mid_sub_description_1')->first();
                                                        $midSubDescriptionTwo = $dataValues->where('key', 'mid_sub_description_2')->first();
                                                        $midSubTitleThree = $dataValues->where('key', 'mid_sub_title_3')->first();
                                                        $midSubDescriptionThree = $dataValues->where('key', 'mid_sub_description_3')->first();
                                                        $downloadSectionTitle = $dataValues->where('key', 'download_section_title')->first();
                                                        $downloadSectionDescription = $dataValues->where('key', 'download_section_description')->first();
                                                        $webBottomTitle = $dataValues->where('key', 'web_bottom_title')->first();
                                                        $testimonialTitle = $dataValues->where('key', 'testimonial_title')->first();

                                                        if (isset($webTopTitle['translations']) && count($webTopTitle['translations'])) {
                                                            $webTopTitleTranslation = [];
                                                            foreach ($webTopTitle['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == "web_top_title") {
                                                                    $webTopTitleTranslation[$lang['code']]['web_top_title'] = $t->value;
                                                                }
                                                            }
                                                        }

                                                        if (isset($webTopDescription['translations']) && count($webTopDescription['translations'])) {
                                                            $webTopDescriptionTranslation = [];
                                                            foreach ($webTopDescription['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == "web_top_description") {
                                                                    $webTopDescriptionTranslation[$lang['code']]['web_top_description'] = $t->value;
                                                                }
                                                            }
                                                        }

                                                        if (isset($webMidTitle['translations']) && count($webMidTitle['translations'])) {
                                                            $webMidTitleTranslation = [];
                                                            foreach ($webMidTitle['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == "web_mid_title") {
                                                                    $webMidTitleTranslation[$lang['code']]['web_mid_title'] = $t->value;
                                                                }
                                                            }
                                                        }

                                                        if (isset($midSubTitleOne['translations']) && count($midSubTitleOne['translations'])) {
                                                            $midSubTitleOneTranslation = [];
                                                            foreach ($midSubTitleOne['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == "mid_sub_title_1") {
                                                                    $midSubTitleOneTranslation[$lang['code']]['mid_sub_title_1'] = $t->value;
                                                                }
                                                            }
                                                        }

                                                        if (isset($midSubDescriptionOne['translations']) && count($midSubDescriptionOne['translations'])) {
                                                            $midSubDescriptionOneTranslation = [];
                                                            foreach ($midSubDescriptionOne['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == "mid_sub_description_1") {
                                                                    $midSubDescriptionOneTranslation[$lang['code']]['mid_sub_description_1'] = $t->value;
                                                                }
                                                            }
                                                        }

                                                        if (isset($midSubDescriptionTwo['translations']) && count($midSubDescriptionTwo['translations'])) {
                                                            $midSubDescriptionTwoTranslation = [];
                                                            foreach ($midSubDescriptionTwo['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == "mid_sub_description_2") {
                                                                    $midSubDescriptionTwoTranslation[$lang['code']]['mid_sub_description_2'] = $t->value;
                                                                }
                                                            }
                                                        }

                                                        if (isset($midSubTitleTwo['translations']) && count($midSubTitleTwo['translations'])) {
                                                            $midSubTitleTwoTranslation = [];
                                                            foreach ($midSubTitleTwo['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == "mid_sub_title_2") {
                                                                    $midSubTitleTwoTranslation[$lang['code']]['mid_sub_title_2'] = $t->value;
                                                                }
                                                            }
                                                        }

                                                        if (isset($midSubTitleThree['translations']) && count($midSubTitleThree['translations'])) {
                                                            $midSubTitleThreeTranslation = [];
                                                            foreach ($midSubTitleThree['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == "mid_sub_title_3") {
                                                                    $midSubTitleThreeTranslation[$lang['code']]['mid_sub_title_3'] = $t->value;
                                                                }
                                                            }
                                                        }

                                                        if (isset($midSubDescriptionThree['translations']) && count($midSubDescriptionThree['translations'])) {
                                                            $midSubDescriptionThreeTranslation = [];
                                                            foreach ($midSubDescriptionThree['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == "mid_sub_description_3") {
                                                                    $midSubDescriptionThreeTranslation[$lang['code']]['mid_sub_description_3'] = $t->value;
                                                                }
                                                            }
                                                        }

                                                        if (isset($downloadSectionTitle['translations']) && count($downloadSectionTitle['translations'])) {
                                                            $downloadSectionTitleTranslation = [];
                                                            foreach ($downloadSectionTitle['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == "download_section_title") {
                                                                    $downloadSectionTitleTranslation[$lang['code']]['download_section_title'] = $t->value;
                                                                }
                                                            }
                                                        }



                                                        if (isset($downloadSectionDescription['translations']) && count($downloadSectionDescription['translations'])) {
                                                            $downloadSectionDescriptionTranslation = [];
                                                            foreach ($downloadSectionDescription['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == "download_section_description") {
                                                                    $downloadSectionDescriptionTranslation[$lang['code']]['download_section_description'] = $t->value;
                                                                }
                                                            }
                                                        }

                                                        if (isset($webBottomTitle['translations']) && count($webBottomTitle['translations'])) {
                                                            $webBottomTitleTranslation = [];
                                                            foreach ($webBottomTitle['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == "web_bottom_title") {
                                                                    $webBottomTitleTranslation[$lang['code']]['web_bottom_title'] = $t->value;
                                                                }
                                                            }
                                                        }

                                                        if (isset($testimonialTitle['translations']) && count($testimonialTitle['translations'])) {
                                                            $testimonialTitleTranslation = [];
                                                            foreach ($testimonialTitle['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == "testimonial_title") {
                                                                    $testimonialTitleTranslation[$lang['code']]['testimonial_title'] = $t->value;
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                    <div class="discount-type d-none lang-form {{$lang['code']}}-form">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="web_top_title[]"
                                                                               placeholder="{{translate('top_title')}}"
                                                                               value="{{ $webTopTitleTranslation[$lang['code']]['web_top_title'] ?? ''}}"
                                                                               required>
                                                                        <label>{{translate('top_title')}} *</label>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="web_top_description[]"
                                                                               placeholder="{{translate('top_description')}}"
                                                                               value="{{ $webTopDescriptionTranslation[$lang['code']]['web_top_description'] ?? ''}}"
                                                                               required>
                                                                        <label>{{translate('top_description')}}
                                                                            *</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12">

                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="web_mid_title[]"
                                                                               placeholder="{{translate('mid_title')}}"
                                                                               value="{{ $webMidTitleTranslation[$lang['code']]['web_mid_title'] ?? ''}}"
                                                                               required>
                                                                        <label>{{translate('mid_title')}} *</label>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="mid_sub_title_1[]"
                                                                               value="{{ $midSubTitleOneTranslation[$lang['code']]['mid_sub_title_1'] ?? ''}}"
                                                                               placeholder="{{translate('mid_sub_title_1')}}"
                                                                               required>
                                                                        <label>{{translate('mid_sub_title_1')}}
                                                                            *</label>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="mid_sub_description_1[]"
                                                                               value="{{ $midSubDescriptionOneTranslation[$lang['code']]['mid_sub_description_1'] ?? ''}}"
                                                                               placeholder="{{translate('mid_sub_description_1')}}"
                                                                               required>
                                                                        <label>{{translate('mid_sub_description_1')}}
                                                                            *</label>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="mid_sub_title_2[]"
                                                                               value="{{ $midSubTitleTwoTranslation[$lang['code']]['mid_sub_title_2'] ?? ''}}"
                                                                               placeholder="{{translate('mid_sub_title_2')}}"
                                                                               required>
                                                                        <label>{{translate('mid_sub_title_2')}}
                                                                            *</label>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="mid_sub_description_2[]"
                                                                               value="{{ $midSubDescriptionTwoTranslation[$lang['code']]['mid_sub_description_2'] ?? ''}}"
                                                                               placeholder="{{translate('mid_sub_description_2')}}"
                                                                               required>
                                                                        <label>{{translate('mid_sub_description_2')}}
                                                                            *</label>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="mid_sub_title_3[]"
                                                                               value="{{ $midSubTitleThreeTranslation[$lang['code']]['mid_sub_title_3'] ?? ''}}"
                                                                               placeholder="{{translate('mid_sub_title_3')}}"
                                                                               required>
                                                                        <label>{{translate('mid_sub_title_3')}}
                                                                            *</label>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="mid_sub_description_3[]"
                                                                               value="{{ $midSubDescriptionThreeTranslation[$lang['code']]['mid_sub_description_3'] ?? ''}}"
                                                                               placeholder="{{translate('mid_sub_description_3')}}"
                                                                               required>
                                                                        <label>{{translate('mid_sub_description_3')}}
                                                                            *</label>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="download_section_title[]"
                                                                               value="{{ $downloadSectionTitleTranslation[$lang['code']]['download_section_title'] ?? ''}}"
                                                                               placeholder="{{translate('download_section_title')}}"
                                                                               required>
                                                                        <label>{{translate('download_section_title')}}
                                                                            *</label>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="download_section_description[]"
                                                                               value="{{ $downloadSectionDescriptionTranslation[$lang['code']]['download_section_description'] ?? ''}}"
                                                                               placeholder="{{translate('download_section_description')}}"
                                                                               required>
                                                                        <label>{{translate('download_section_description')}}
                                                                            *</label>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="web_bottom_title[]"
                                                                               value="{{ $webBottomTitleTranslation[$lang['code']]['web_bottom_title'] ?? ''}}"
                                                                               placeholder="{{translate('bottom_title')}}"
                                                                               required>
                                                                        <label>{{translate('bottom_title')}} *</label>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-30">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control"
                                                                               name="testimonial_title[]"
                                                                               value="{{ $testimonialTitleTranslation[$lang['code']]['testimonial_title'] ?? ''}}"
                                                                               placeholder="{{translate('testimonial_title')}}"
                                                                               required>
                                                                        <label>{{translate('testimonial_title')}}
                                                                            *</label>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="lang[]" value="{{$lang['code']}}">
                                                @endforeach
                                            @else
                                                <div class="discount-type lang-form">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="web_top_title"
                                                                           placeholder="{{translate('top_title')}}"
                                                                           value="{{$dataValues->where('key','web_top_title')->first()->value ??''}}"
                                                                           required>
                                                                    <label>{{translate('top_title')}} *</label>
                                                                </div>
                                                            </div>

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="web_top_description"
                                                                           placeholder="{{translate('top_description')}}"
                                                                           value="{{$dataValues->where('key','web_top_description')->first()->value??''}}"
                                                                           required>
                                                                    <label>{{translate('top_description')}} *</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12">

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="web_mid_title"
                                                                           placeholder="{{translate('mid_title')}}"
                                                                           value="{{$dataValues->where('key','web_mid_title')->first()->value??''}}"
                                                                           required>
                                                                    <label>{{translate('mid_title')}} *</label>
                                                                </div>
                                                            </div>

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="mid_sub_title_1"
                                                                           value="{{$dataValues->where('key','mid_sub_title_1')->first()->value??''}}"
                                                                           placeholder="{{translate('mid_sub_title_1')}}"
                                                                           required>
                                                                    <label>{{translate('mid_sub_title_1')}} *</label>
                                                                </div>
                                                            </div>
                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="mid_sub_description_1"
                                                                           value="{{$dataValues->where('key','mid_sub_description_1')->first()->value??''}}"
                                                                           placeholder="{{translate('mid_sub_description_1')}}"
                                                                           required>
                                                                    <label>{{translate('mid_sub_description_1')}}
                                                                        *</label>
                                                                </div>
                                                            </div>
                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="mid_sub_title_2"
                                                                           value="{{$dataValues->where('key','mid_sub_title_2')->first()->value??''}}"
                                                                           placeholder="{{translate('mid_sub_title_2')}}"
                                                                           required>
                                                                    <label>{{translate('mid_sub_title_2')}} *</label>
                                                                </div>
                                                            </div>
                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="mid_sub_description_2"
                                                                           value="{{$dataValues->where('key','mid_sub_description_2')->first()->value??''}}"
                                                                           placeholder="{{translate('mid_sub_description_2')}}"
                                                                           required>
                                                                    <label>{{translate('mid_sub_description_2')}}
                                                                        *</label>
                                                                </div>
                                                            </div>
                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="mid_sub_title_3"
                                                                           value="{{$dataValues->where('key','mid_sub_title_3')->first()->value??''}}"
                                                                           placeholder="{{translate('mid_sub_title_3')}}"
                                                                           required>
                                                                    <label>{{translate('mid_sub_title_3')}} *</label>
                                                                </div>
                                                            </div>
                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="mid_sub_description_3"
                                                                           value="{{$dataValues->where('key','mid_sub_description_3')->first()->value??''}}"
                                                                           placeholder="{{translate('mid_sub_description_3')}}"
                                                                           required>
                                                                    <label>{{translate('mid_sub_description_3')}}
                                                                        *</label>
                                                                </div>
                                                            </div>

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="download_section_title"
                                                                           value="{{$dataValues->where('key','download_section_title')->first()->value??''}}"
                                                                           placeholder="{{translate('download_section_title')}}"
                                                                           required>
                                                                    <label>{{translate('download_section_title')}}
                                                                        *</label>
                                                                </div>
                                                            </div>
                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="download_section_description"
                                                                           value="{{$dataValues->where('key','download_section_description')->first()->value??''}}"
                                                                           placeholder="{{translate('download_section_description')}}"
                                                                           required>
                                                                    <label>{{translate('download_section_description')}}
                                                                        *</label>
                                                                </div>
                                                            </div>

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="web_bottom_title"
                                                                           value="{{$dataValues->where('key','web_bottom_title')->first()->value??''}}"
                                                                           placeholder="{{translate('bottom_title')}}"
                                                                           required>
                                                                    <label>{{translate('bottom_title')}} *</label>
                                                                </div>
                                                            </div>

                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control"
                                                                           name="testimonial_title"
                                                                           value="{{$dataValues->where('key','testimonial_title')->first()->value??''}}"
                                                                           placeholder="{{translate('testimonial_title')}}"
                                                                           required>
                                                                    <label>{{translate('testimonial_title')}} *</label>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="lang[]" value="default">
                                            @endif

                                            @can('landing_update')
                                                <div class="d-flex gap-2 justify-content-end">
                                                    <button type="reset" class="btn btn-secondary">
                                                        {{translate('reset')}}
                                                    </button>
                                                    <button type="submit" class="btn btn--primary">
                                                        {{translate('update')}}
                                                    </button>
                                                </div>
                                            @endcan
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($webPage=='web_app_image')
                        <div class="tab-content">
                            <div class="tab-pane fade {{$webPage=='web_app_image'?'active show':''}}">
                                <div class="card">
                                    <div class="card-body p-30">
                                        <div class="discount-type">
                                            <div class="row">
                                                @php($keys = ['support_section_image', 'download_section_image', 'feature_section_image'])
                                                @php($ratios = ['200x242', '500x500', '500x500'])
                                                @foreach($keys as $index=>$key)
                                                    <div class="col-md-4 mb-30">
                                                        <form
                                                            action="{{route('admin.business-settings.set-landing-information')}}?web_page={{$webPage}}"
                                                            method="POST" enctype="multipart/form-data">
                                                            @csrf
                                                            @method('PUT')
                                                            <div
                                                                class="mb-1 d-flex flex-column align-items-center gap-2">
                                                                <p class="title-color text-center web-images">
                                                                    {{translate($key)}}, <small
                                                                        class="opacity-75">{{translate('size')}}
                                                                        : {{$ratios[$index]}}</small>
                                                                </p>
                                                                <div class="upload-file max-w-100">
                                                                    <input type="file" class="upload-file__input"
                                                                           name="{{$key}}" id="image-{{$key}}"
                                                                           accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*">
                                                                    <div class="upload-file__img">
                                                                        @php($image = getBusinessSettingsImageFullPath(key: $key, settingType: 'landing_web_app_image', path: 'landing-page/web/',  defaultPath : 'public/assets/admin-module/img/media/upload-file.png'))
                                                                        <img src="{{ $image }}" alt="{{translate('image')}}">

                                                                    </div>
                                                                    <span class="upload-file__edit">
                                                                        <span class="material-icons">edit</span>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            @can('landing_update')
                                                                <div class="d-flex gap-2 justify-content-center">
                                                                    <button type="submit"
                                                                            class="btn btn--primary btn-block">
                                                                        {{translate('upload')}}
                                                                    </button>
                                                                </div>
                                                            @endcan
                                                        </form>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.js"></script>
    <script>
        "use strict";

        $(document).ready(function () {
            $('.js-select').select2();
        });
    </script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/dataTables.select.min.js"></script>

    <script>
        "use strict";

        $('.action-btn.btn--danger').on('click', function () {
            let id = $(this).data('id');
            let message = "{{translate('want_to_delete_this')}}?"
            @if(env('APP_ENV')!='demo')
            form_alert(id, message)
            @endif
        });

        $('#landing-info-update-form').on('submit', function (event) {
            event.preventDefault();

            var form = $('#landing-info-update-form')[0];
            var formData = new FormData(form);

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('admin.business-settings.set-landing-information')}}?web_page={{$webPage}}",
                data: formData,
                processData: false,
                contentType: false,
                type: 'POST',
                success: function (response) {
                    console.log(response)
                    if (response.errors.length > 0) {
                        response.errors.forEach((value, key) => {
                            toastr.error(value.message);
                        });
                    } else {
                        toastr.success('{{translate('successfully_updated')}}');
                    }
                },
                error: function (jqXHR, exception) {
                    toastr.error(jqXHR.responseJSON.message);
                }
            });
        });
    </script>

    <script>
        "use strict";

        $(".lang_link").on('click', function (e) {
            e.preventDefault();
            $(".lang_link").removeClass('active');
            $(".lang-form").addClass('d-none');
            $(this).addClass('active');

            let form_id = this.id;
            let lang = form_id.substring(0, form_id.length - 5);
            console.log(lang);
            $("." + lang + "-form").removeClass('d-none');
        });
    </script>
@endpush
