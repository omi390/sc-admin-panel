@extends('adminmodule::layouts.master')

@section('title',translate('notification_setup'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/select.dataTables.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/swiper/swiper-bundle.min.css"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body p-30">
                    <div class="discount-type">
                        <div class="d-flex justify-content-between mb-2">
                            <div class="page-header align-items-center">
                                <h4>{{translate('Firebase_Push_Notification_Setup')}}</h4>
                            </div>
                            <div class="ripple-animation" data-bs-toggle="modal" data-bs-target="#documentationModal" type="button">
                                <img src="{{asset('/public/assets/admin-module/img/info.svg')}}" class="svg" alt="">
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex flex-wrap justify-content-between gap-3 mb-2">
                            @php($language= Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name','system_language')->first())
                            @php($defaultLanguage = str_replace('_', '-', app()->getLocale()))
                            @if($language)
                                <ul class="nav nav--tabs border-color-primary">
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

                            <div class="select-box mb-5">
                                <select class="js-select" id="notification_type" name="notification_type">
                                    <option
                                        value="customers" {{ $queryParams == 'customers' ? 'selected' : '' }}>{{ translate('Messages_For_Customers') }}</option>
                                    <option
                                        value="providers" {{ $queryParams == 'providers' ? 'selected' : '' }}>{{ translate('Messages_For_Providers') }}</option>
                                    <option
                                        value="serviceman" {{ $queryParams == 'serviceman' ? 'selected' : '' }}>{{ translate('Messages_For_Serviceman') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            @if($queryParams == 'customers')
                                @foreach(NOTIFICATION_FOR_USER as $userNotification)
                                    <div class="col-md-6">
                                        <form method="POST"
                                                action="{{route('admin.configuration.set-message-setting', ['type' => $queryParams])}}">
                                            @csrf
                                            @method('PUT')
                                            @if($language)
                                                <div class="mb-30 lang-form default-form">
                                                    <div class="mb-20 d-flex justify-content-between">
                                                        <b>{{ translate($userNotification['value'] . '_Message') }}</b>
                                                        @can('configuration_manage_status')
                                                            <label class="switcher">
                                                                <input class="switcher_input update-message"
                                                                        name="status"
                                                                        id="{{$userNotification['key']}}_status"
                                                                        {{$dataValues->where('key_name', $userNotification['key'])->where('settings_type', 'customer_notification')->first()?->live_values[$userNotification['key'].'_status']?'checked':''}}
                                                                        data-key="{{$userNotification['key'] ?? ''}}"
                                                                        type="checkbox"
                                                                        value="1">
                                                                <span class="switcher_control"></span>
                                                            </label>
                                                        @endcan
                                                    </div>
                                                    <input type="hidden" name="id"
                                                            value="{{ $userNotification['key'] }}">
                                                    <div class="form-floating">
                                                        <textarea class="form-control"
                                                                    id="{{ $userNotification['key'] }}_message"
                                                                    name="{{ $userNotification['key'] ?? '' }}_message[]">{{$dataValues->where('key_name', $userNotification['key'])->where('settings_type', 'customer_notification')->first()?->live_values[$userNotification['key'].'_message']}}</textarea>
                                                    </div>
                                                    @can('configuration_manage_status')
                                                        @can('configuration_update')
                                                            <div class="d-flex justify-content-end mt-10">
                                                                <button type="submit"
                                                                        class="btn btn--primary">
                                                                    {{translate('update')}}
                                                                </button>
                                                            </div>
                                                        @endcan
                                                    @endcan
                                                </div>
                                                <input type="hidden" name="lang[]" value="default">
                                                @foreach ($language?->live_values as $lang)
                                                        <?php
                                                        $notificationRow = $dataValues->where('key_name', $userNotification['key'])->where('settings_type', 'customer_notification')->first();
                                                        if (isset($notificationRow['translations']) && count($notificationRow['translations'])) {
                                                            $translate = [];
                                                            foreach ($notificationRow['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == $notificationRow->key_name) {
                                                                    $translate[$lang['code']][$notificationRow->key_name] = $t->value;
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                    <div class="mb-30 d-none lang-form {{$lang['code']}}-form">
                                                        <div class="mb-20 d-flex justify-content-between">
                                                            <b>{{ translate($userNotification['value'] . '_Message') }}
                                                                ({{strtoupper($lang['code'])}})</b>


                                                            @can('configuration_manage_status')
                                                                <label class="switcher">
                                                                    <input class="switcher_input update-message"
                                                                            name="status"
                                                                            id="{{$userNotification['key']}}_status"
                                                                            {{$dataValues->where('key_name', $userNotification['key'])->where('settings_type', 'customer_notification')->first()?->live_values[$userNotification['key'].'_status']?'checked':''}}
                                                                            data-key="{{$userNotification['key'] ?? ''}}"
                                                                            type="checkbox"
                                                                            value="1">
                                                                    <span class="switcher_control"></span>
                                                                </label>
                                                            @endcan
                                                        </div>
                                                        <input type="hidden" name="id"
                                                                value="{{ $userNotification['key'] }}">
                                                        <div class="form-floating">
                                                        <textarea class="form-control"
                                                                    id="{{ $userNotification['key'] }}_message"
                                                                    name="{{ $userNotification['key'] ?? '' }}_message[]">{{$translate[$lang['code']][$notificationRow?->key_name] ?? ''}}</textarea>
                                                        </div>
                                                        @can('configuration_update')
                                                            <div class="d-flex justify-content-end mt-10">
                                                                <button type="submit"
                                                                        class="btn btn--primary">
                                                                    {{translate('update')}}
                                                                </button>
                                                            </div>
                                                        @endcan
                                                    </div>
                                                    <input type="hidden" name="lang[]"
                                                            value="{{$lang['code']}}">
                                                @endforeach
                                            @else
                                                <div class="mb-30 lang-form">
                                                    <div class="mb-20 d-flex justify-content-between">
                                                        <b>{{ translate($userNotification['value'] . '_Message') }}</b>
                                                        @can('configuration_manage_status')
                                                            <label class="switcher">
                                                                <input class="switcher_input update-message"
                                                                        name="status"
                                                                        id="{{$userNotification['key']}}_status"
                                                                        {{$dataValues->where('key_name', $userNotification['key'])->where('settings_type', 'customer_notification')->first()?->live_values[$userNotification['key'].'_status']?'checked':''}}
                                                                        data-key="{{$userNotification['key'] ?? ''}}"
                                                                        type="checkbox"
                                                                        value="1">
                                                                <span class="switcher_control"></span>
                                                            </label>
                                                        @endcan
                                                    </div>
                                                    <input type="hidden" name="id"
                                                            value="{{ $userNotification['key'] }}">
                                                    <div class="form-floating">
                                                        <textarea class="form-control"
                                                                    id="{{ $userNotification['key'] }}_message"
                                                                    name="{{ $userNotification['key'] ?? '' }}_message[]">{{$dataValues->where('key_name', $userNotification['key'])->where('settings_type', 'customer_notification')->first()?->live_values[$userNotification['key'].'_message']}}</textarea>
                                                    </div>
                                                    @can('configuration_update')
                                                        <div class="d-flex justify-content-end mt-10">
                                                            <button type="submit"
                                                                    class="btn btn--primary">
                                                                {{translate('update')}}
                                                            </button>
                                                        </div>
                                                    @endcan
                                                </div>
                                                <input type="hidden" name="lang[]" value="default">
                                            @endif
                                        </form>
                                    </div>
                                @endforeach
                            @endif
                            @if($queryParams == 'providers')
                                @foreach(NOTIFICATION_FOR_PROVIDER as $providerNotification)
                                    <div class="col-md-6">
                                        <form method="POST"
                                                action="{{route('admin.configuration.set-message-setting', ['type' => $queryParams])}}">
                                            @csrf
                                            @method('PUT')
                                            @if($language)
                                                <div class="mb-30 lang-form default-form">
                                                    <div class="mb-20 d-flex justify-content-between">
                                                        <b>{{ translate($providerNotification['value'] . '_Message') }}</b>

                                                        <label class="switcher">
                                                            <input class="switcher_input update-message"
                                                                    name="status"
                                                                    id="{{$providerNotification['key']}}_status"
                                                                    {{$dataValues->where('key_name', $providerNotification['key'])->where('settings_type', 'provider_notification')->first()?->live_values[$providerNotification['key'].'_status']?'checked':''}}
                                                                    data-key="{{$providerNotification['key'] ?? ''}}"
                                                                    type="checkbox"
                                                                    value="1">
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                    <input type="hidden" name="id"
                                                            value="{{ $providerNotification['key'] }}">
                                                    <div class="form-floating">
                                                        <textarea class="form-control"
                                                                    id="{{ $providerNotification['key'] }}_message"
                                                                    name="{{ $providerNotification['key'] ?? '' }}_message[]">{{$dataValues->where('key_name', $providerNotification['key'])->where('settings_type', 'provider_notification')->first()?->live_values[$providerNotification['key'].'_message']}}</textarea>
                                                    </div>
                                                    <div class="d-flex justify-content-end mt-10">
                                                        <button type="submit"
                                                                class="btn btn--primary">
                                                            {{translate('update')}}
                                                        </button>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="lang[]" value="default">
                                                @foreach ($language?->live_values as $lang)
                                                        <?php
                                                        $notificationRow = $dataValues->where('key_name', $providerNotification['key'])->where('settings_type', 'provider_notification')->first();
                                                        if (isset($notificationRow['translations']) && count($notificationRow['translations'])) {
                                                            $translate = [];
                                                            foreach ($notificationRow['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == $notificationRow->key_name) {
                                                                    $translate[$lang['code']][$notificationRow->key_name] = $t->value;
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                    <div class="mb-30 d-none lang-form {{$lang['code']}}-form">
                                                        <div class="mb-20 d-flex justify-content-between">
                                                            <b>{{ translate($providerNotification['value'] . '_Message') }}
                                                                ({{strtoupper($lang['code'])}})</b>

                                                            <label class="switcher">
                                                                <input class="switcher_input update-message"
                                                                        name="status"
                                                                        id="{{$providerNotification['key']}}_status"
                                                                        {{$dataValues->where('key_name', $providerNotification['key'])->where('settings_type', 'provider_notification')->first()?->live_values[$providerNotification['key'].'_status']?'checked':''}}
                                                                        data-key="{{$providerNotification['key'] ?? ''}}"
                                                                        type="checkbox"
                                                                        value="1">
                                                                <span class="switcher_control"></span>
                                                            </label>
                                                        </div>
                                                        <input type="hidden" name="id"
                                                                value="{{ $providerNotification['key'] }}">
                                                        <div class="form-floating">
                                                        <textarea class="form-control"
                                                                    id="{{ $providerNotification['key'] }}_message"
                                                                    name="{{ $providerNotification['key'] ?? '' }}_message[]">{{$translate[$lang['code']][$notificationRow?->key_name] ?? ''}}</textarea>
                                                        </div>
                                                        <div class="d-flex justify-content-end mt-10">
                                                            <button type="submit"
                                                                    class="btn btn--primary">
                                                                {{translate('update')}}
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="lang[]"
                                                            value="{{$lang['code']}}">
                                                @endforeach
                                            @else
                                                <div class="mb-30 lang-form">
                                                    <div class="mb-20 d-flex justify-content-between">
                                                        <b>{{ translate($providerNotification['value'] . '_Message') }}</b>

                                                        <label class="switcher">
                                                            <input class="switcher_input update-message"
                                                                    name="status"
                                                                    id="{{$providerNotification['key']}}_status"
                                                                    {{$dataValues->where('key_name', $providerNotification['key'])->where('settings_type', 'provider_notification')->first()?->live_values[$providerNotification['key'].'_status']?'checked':''}}
                                                                    data-key="{{$providerNotification['key'] ?? ''}}"
                                                                    type="checkbox"
                                                                    value="1">
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                    <input type="hidden" name="id"
                                                            value="{{ $providerNotification['key'] }}">
                                                    <div class="form-floating">
                                                        <textarea class="form-control"
                                                                    id="{{ $providerNotification['key'] }}_message"
                                                                    name="{{ $providerNotification['key'] ?? '' }}_message[]">{{$dataValues->where('key_name', $providerNotification['key'])->where('settings_type', 'provider_notification')->first()?->live_values[$providerNotification['key'].'_message']}}</textarea>
                                                    </div>
                                                    <div class="d-flex justify-content-end mt-10">
                                                        <button type="submit"
                                                                class="btn btn--primary">
                                                            {{translate('update')}}
                                                        </button>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="lang[]" value="default">
                                            @endif
                                        </form>
                                    </div>
                                @endforeach
                            @endif
                            @if($queryParams == 'serviceman')
                                @foreach(NOTIFICATION_FOR_SERVICEMAN as $servicemanNotification)
                                    <div class="col-md-6">
                                        <form method="POST"
                                                action="{{route('admin.configuration.set-message-setting', ['type' => $queryParams])}}">
                                            @csrf
                                            @method('PUT')
                                            @if($language)
                                                <div class="mb-30 lang-form default-form">
                                                    <div class="mb-20 d-flex justify-content-between">
                                                        <b>{{ translate($servicemanNotification['value'] . '_Message') }}</b>

                                                        <label class="switcher">
                                                            <input class="switcher_input update-message"
                                                                    name="status"
                                                                    id="{{$servicemanNotification['key']}}_status"
                                                                    {{$dataValues->where('key_name', $servicemanNotification['key'])->where('settings_type', 'serviceman_notification')->first()?->live_values[$servicemanNotification['key'].'_status']?'checked':''}}
                                                                    data-key="{{$servicemanNotification['key'] ?? ''}}"
                                                                    type="checkbox"
                                                                    value="1">
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                    <input type="hidden" name="id"
                                                            value="{{ $servicemanNotification['key'] }}">
                                                    <div class="form-floating">
                                                        <textarea class="form-control"
                                                                    id="{{ $servicemanNotification['key'] }}_message"
                                                                    name="{{ $servicemanNotification['key'] ?? '' }}_message[]">{{$dataValues->where('key_name', $servicemanNotification['key'])->where('settings_type', 'serviceman_notification')->first()?->live_values[$servicemanNotification['key'].'_message']}}</textarea>
                                                    </div>
                                                    <div class="d-flex justify-content-end mt-10">
                                                        <button type="submit"
                                                                class="btn btn--primary">
                                                            {{translate('update')}}
                                                        </button>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="lang[]" value="default">
                                                @foreach ($language?->live_values as $lang)
                                                        <?php
                                                        $notificationRow = $dataValues->where('key_name', $servicemanNotification['key'])->where('settings_type', 'serviceman_notification')->first();
                                                        if (isset($notificationRow['translations']) && count($notificationRow['translations'])) {
                                                            $translate = [];
                                                            foreach ($notificationRow['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == $notificationRow->key_name) {
                                                                    $translate[$lang['code']][$notificationRow->key_name] = $t->value;
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                    <div class="mb-30 d-none lang-form {{$lang['code']}}-form">
                                                        <div class="mb-20 d-flex justify-content-between">
                                                            <b>{{ translate($servicemanNotification['value'] . '_Message') }}
                                                                ({{strtoupper($lang['code'])}})</b>

                                                            <label class="switcher">
                                                                <input class="switcher_input update-message"
                                                                        name="status"
                                                                        id="{{$servicemanNotification['key']}}_status"
                                                                        {{$dataValues->where('key_name', $servicemanNotification['key'])->where('settings_type', 'serviceman_notification')->first()?->live_values[$servicemanNotification['key'].'_status']?'checked':''}}
                                                                        data-key="{{$servicemanNotification['key'] ?? ''}}"
                                                                        type="checkbox"
                                                                        value="1">
                                                                <span class="switcher_control"></span>
                                                            </label>
                                                        </div>
                                                        <input type="hidden" name="id"
                                                                value="{{ $servicemanNotification['key'] }}">
                                                        <div class="form-floating">
                                                        <textarea class="form-control"
                                                                    id="{{ $servicemanNotification['key'] }}_message"
                                                                    name="{{ $servicemanNotification['key'] ?? '' }}_message[]">{{$translate[$lang['code']][$notificationRow?->key_name] ?? ''}}</textarea>
                                                        </div>
                                                        <div class="d-flex justify-content-end mt-10">
                                                            <button type="submit"
                                                                    class="btn btn--primary">
                                                                {{translate('update')}}
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="lang[]"
                                                            value="{{$lang['code']}}">
                                                @endforeach
                                            @else
                                                <div class="mb-30 lang-form">
                                                    <div class="mb-20 d-flex justify-content-between">
                                                        <b>{{ translate($servicemanNotification['value'] . '_Message') }}</b>

                                                        <label class="switcher">
                                                            <input class="switcher_input update-message"
                                                                    name="status"
                                                                    id="{{$servicemanNotification['key']}}_status"
                                                                    {{$dataValues->where('key_name', $servicemanNotification['key'])->where('settings_type', 'serviceman_notification')->first()?->live_values[$servicemanNotification['key'].'_status']?'checked':''}}
                                                                    data-key="{{$servicemanNotification['key'] ?? ''}}"
                                                                    type="checkbox"
                                                                    value="1">
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                    <input type="hidden" name="id"
                                                            value="{{ $servicemanNotification['key'] }}">
                                                    <div class="form-floating">
                                                        <textarea class="form-control"
                                                                    id="{{ $servicemanNotification['key'] }}_message"
                                                                    name="{{ $servicemanNotification['key'] ?? '' }}_message[]">{{$dataValues->where('key_name', $servicemanNotification['key'])->where('settings_type', 'serviceman_notification')->first()?->live_values[$servicemanNotification['key'].'_message']}}</textarea>
                                                    </div>
                                                    <div class="d-flex justify-content-end mt-10">
                                                        <button type="submit"
                                                                class="btn btn--primary">
                                                            {{translate('update')}}
                                                        </button>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="lang[]" value="default">
                                            @endif
                                        </form>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade firebase-modal" id="carouselModal" tabindex="-1" aria-labelledby="carouselModal"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-1">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 px-sm-5 pt-0">
                    <div dir="ltr" class="swiper modalSwiper pb-4">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <div class="d-flex flex-column align-items-center gap-2 fs-12">
                                    <img width="80" class="mb-3"
                                         src="{{asset('public/assets/admin-module/img/media/firebase-console.png')}}"
                                         alt="">
                                    <h5 class="modal-title text-center mb-3">{{translate('Go to Firebase Console')}}</h5>

                                    @php($firebaseLink = 'https://console.firebase.google.com')
                                    <ul class="d-flex flex-column gap-2 px-3">
                                        <li>{{translate('Open your web browser and go to the Firebase Console')}} <a
                                                href="https://console.firebase.google.com">{{$firebaseLink}}</a>
                                        </li>
                                        <li>{{translate('Select the project for which you want to configure FCM from the Firebase
                                            Console dashboard.')}}
                                        </li>
                                        <li>{{translate('If you don’t have any project before. Create one with the website name.')}}</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="d-flex flex-column align-items-center gap-2 fs-12">
                                    <img width="80" class="mb-3"
                                         src="{{asset('public/assets/admin-module/img/media/project-settings.png')}}"
                                         alt="">
                                    <h5 class="modal-title text-center mb-3">{{translate('Navigate to Project Settings')}}</h5>

                                    <ul class="d-flex flex-column gap-2 px-3">
                                        <li>{{translate('In the left-hand menu, click on the')}}
                                            <strong>"Settings"</strong> {{translate('gear icon,
                                            there you will vae a dropdown. and then select ')}}
                                            <strong>{{translate('"Project settings"')}}
                                            </strong> {{translate('from the dropdown.')}}
                                        </li>
                                        <li>{{translate('In the Project settings page, click on the "Cloud Messaging" tab from the
                                            top menu.')}}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="d-flex flex-column align-items-center gap-2 fs-12">
                                    <img width="80" class="mb-3"
                                         src="{{asset('public/assets/admin-module/img/media/cloud-message.png')}}"
                                         alt="">
                                    <h5 class="modal-title text-center mb-3">{{translate('Cloud Messaging API')}}</h5>

                                    <ul class="d-flex flex-column gap-2 px-3">
                                        <li>{{translate('From Cloud Messaging Page there will be a section called Cloud Messaging
                                            API.')}}
                                        </li>
                                        <li>{{translate('Click on the menu icon and enable the API')}}</li>
                                        <li>{{translate('Refresh the Cloud Messaging Page - You will have your server key. Just copy
                                            the code and paste here')}}
                                        </li>
                                    </ul>

                                    <div class="d-flex justify-content-center mt-2 w-100">
                                        <button type="button" class="btn btn-primary w-100 max-w320"
                                                data-bs-dismiss="modal">{{translate('Got It')}}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-pagination mb-2"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="documentationModal" tabindex="-1" aria-labelledby="documentationModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-0 pb-1">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-column align-items-center gap-2 max-w360 mx-auto fs-12">
                        <img width="80" class="mb-3"
                             src="{{asset('public/assets/admin-module/img/media/documentation.png')}}" alt="">
                        <h5 class="modal-title text-center mb-3">{{translate('Documentation')}}</h5>
                        <p>{{translate('If disabled customers and provider will not receive notifications on their devices')}}</p>

                            <?php
                            $providerName = 'providerName';
                            $serviceManName = 'serviceManName';
                            $bookingId = 'bookingId';
                            $scheduleTime = 'scheduleTime';
                            $userName = 'userName';
                            $zoneName = 'zoneName';
                            ?>
                        <ul class="d-flex flex-column gap-2 px-3">
                            <li><span
                                    class="fw-medium">&#123;&#123;{{$providerName}}&#125;&#125;:</span> {{translate('the name of the provider.')}}
                            </li>
                            <li><span
                                    class="fw-medium">&#123;&#123;{{$serviceManName}}&#125;&#125;:</span> {{translate('the name of the service man name.')}}
                            </li>
                            <li><span
                                    class="fw-medium">&#123;&#123;{{$bookingId}}&#125;&#125;:</span> {{translate('the unique ID of the Booking.')}}
                            </li>
                            <li><span
                                    class="fw-medium">&#123;&#123;{{$scheduleTime}}&#125;&#125;:</span> {{translate('the expected sechedule time.')}}
                            </li>
                            <li><span
                                    class="fw-medium">&#123;&#123;{{$userName}}&#125;&#125;:</span> {{translate('the name of the user who placed the order.')}}
                            </li>
                            <li><span
                                    class="fw-medium">&#123;&#123;{{$zoneName}}&#125;&#125;:</span> {{translate('the name of the zone.')}}
                            </li>
                        </ul>
                    </div>

                    <div class="d-flex justify-content-center mt-2">
                        <button type="button" class="btn btn-primary w-100 max-w320" data-bs-dismiss="modal">
                            {{translate('Got It')}}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/swiper/swiper-bundle.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/dataTables.select.min.js"></script>

    <script>
        "use strict";

        let swiper = new Swiper(".modalSwiper", {
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
                dynamicBullets: true,
                autoHeight: true,
            },
        });

        $(document).ready(function () {
            $('.js-select').select2();
        });

        $('.update-message').on('click', function () {
            let id = $(this).data('key');
            update_message(id)
        });

        $('#business-info-update-form').on('submit', function (event) {
            event.preventDefault();

            var form = $('#business-info-update-form')[0];
            var formData = new FormData(form);

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('admin.business-settings.set-business-information')}}",
                data: formData,
                processData: false,
                contentType: false,
                type: 'POST',
                success: function (response) {
                    toastr.success('{{translate('successfully_updated')}}')
                },
                error: function () {

                }
            });
        });

        $(".push-notification-update-action-status").on('click', function () {
            let keyName = $(this).data('keyname');
            let value = $(this).is(':checked') === true ? 1 : 0
            update_action_status(keyName, value);
        })

        function update_action_status(key_name, value) {
            Swal.fire({
                title: "{{translate('are_you_sure')}}?",
                text: '{{translate('want_to_update_status')}}',
                type: 'warning',
                showCloseButton: true,
                showCancelButton: true,
                cancelButtonColor: 'var(--c2)',
                confirmButtonColor: 'var(--c1)',
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Yes',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{route('admin.configuration.set-notification-setting')}}",
                        data: {
                            key: key_name,
                            value: value,
                        },
                        type: 'put',
                        success: function (response) {
                            console.log(response)
                            toastr.success('{{translate('successfully_updated')}}')
                        },
                        error: function () {

                        }
                    });
                }
            })
        }

        function update_message(id) {
            Swal.fire({
                title: "{{translate('are_you_sure')}}?",
                text: '{{translate('want_to_update')}}',
                type: 'warning',
                showCloseButton: true,
                showCancelButton: true,
                cancelButtonColor: 'var(--c2)',
                confirmButtonColor: 'var(--c1)',
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Yes',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {

                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{route('admin.configuration.set-message-setting')}}",
                        data: {
                            id: id,
                            status: $('#' + id + '_status').is(':checked') === true ? 1 : 0,
                            message: $('#' + id + '_message').val(),
                            type: "{{$queryParams}}"
                        },
                        type: 'PUT',
                        success: function (response) {
                            console.log(response)
                            toastr.success('{{translate('successfully_updated')}}')
                        },
                        error: function () {

                        }
                    });
                }
            })
        }

        $(document).ready(function () {
            $('#notification_type').on('change', function () {
                var selectedOption = $(this).val();

                var currentUrl = window.location.href;
                var url = new URL(currentUrl);

                url.searchParams.set('type', selectedOption);

                window.location.href = url.toString();
            });
        });

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
