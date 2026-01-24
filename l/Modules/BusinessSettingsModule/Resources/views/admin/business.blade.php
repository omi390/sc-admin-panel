@extends('adminmodule::layouts.master')

@section('title',translate('business_setup'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('assets/admin-module')}}/plugins/select2/select2.min.css"/>
    <link rel="stylesheet" href="{{asset('assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="{{asset('assets/admin-module')}}/plugins/dataTables/select.dataTables.min.css"/>


    <link rel="stylesheet" href="{{asset('assets/admin-module/plugins/swiper/swiper-bundle.min.css')}}">
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between">
                        <div class="page-title-wrap mb-3">
                            <h2 class="page-title">{{translate('business_setup')}}</h2>
                        </div>
                        <div>
                            <i class="material-icons" data-bs-toggle="tooltip" data-bs-placement="top"
                               title="{{translate('Please click update for making the changes')}}"
                            >info</i>
                        </div>
                    </div>

                    <div class="mb-3">
                        <ul class="nav nav--tabs nav--tabs__style2">
                            <li class="nav-item">
                                <a href="{{url()->current()}}?web_page=business_setup"
                                   class="nav-link {{$webPage=='business_setup'?'active':''}}">
                                    {{translate('Business info')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{url()->current()}}?web_page=service_setup"
                                   class="nav-link {{$webPage=='service_setup'?'active':''}}">
                                    {{translate('General_Setup')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{url()->current()}}?web_page=promotional_setup"
                                   class="nav-link {{$webPage=='promotional_setup'?'active':''}}">
                                    {{translate('Promotions')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{url()->current()}}?web_page=bookings"
                                   class="nav-link {{$webPage=='bookings'?'active':''}}">
                                    {{translate('bookings')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{url()->current()}}?web_page=customers"
                                   class="nav-link {{$webPage=='customers'?'active':''}}">
                                    {{translate('customers')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{url()->current()}}?web_page=providers"
                                   class="nav-link {{$webPage=='providers'?'active':''}}">
                                    {{translate('providers')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{url()->current()}}?web_page=servicemen"
                                   class="nav-link {{$webPage=='servicemen'?'active':''}}">
                                    {{translate('servicemen')}}
                                </a>
                            </li>
                        </ul>
                    </div>

                    @if($webPage=='business_setup')
                        <div class="tab-content">
                            <div class="tab-pane fade {{$webPage=='business_setup'?'active show':''}}">
                                <div class="card mb-2">
                                    <div class="card-header">
                                        <h4 class="mb-0">
                                            <span class="material-icons" title="Subscription Management">campaign</span>
                                            {{translate('System Maintenance')}}
                                        </h4>
                                    </div>
                                    <div class="card-body">
                                            <?php
                                                $config = (int)((business_config('maintenance_mode', 'maintenance_mode'))?->live_values) ?? 0;
                                                $selectedMaintenanceSystem = ((business_config('maintenance_system_setup', 'maintenance_mode'))?->live_values) ?? [];
                                                $selectedMaintenanceDuration = ((business_config('maintenance_duration_setup', 'maintenance_mode'))?->live_values) ?? [];

                                                if (isset($selectedMaintenanceDuration['start_date']) && isset($selectedMaintenanceDuration['end_date'])) {
                                                    $startDate = new DateTime($selectedMaintenanceDuration['start_date']);
                                                    $endDate = new DateTime($selectedMaintenanceDuration['end_date']);
                                                } else {
                                                    $startDate = null;
                                                    $endDate = null;
                                                }
                                            ?>

                                        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                                            <div>
                                                @if($config)
                                                    <div class="d-flex flex-wrap gap-3 align-items-center">
                                                            <p class="fz-12 mb-0 maintainance-text-button">
                                                                <span>
                                                                    {{ translate('Your maintenance mode is activated') }}
                                                                    @if(isset($selectedMaintenanceDuration['maintenance_duration']) && $selectedMaintenanceDuration['maintenance_duration'] == 'until_change')
                                                                        {{ translate(' until I change') }}
                                                                    @endif
                                                                    @if($startDate && $endDate)
                                                                        {{translate('from ')}}<strong>{{ $startDate->format('m/d/Y, h:i A') }}</strong> to <strong>{{ $endDate->format('m/d/Y, h:i A') }}</strong>.
                                                                    @endif
                                                                </span>
                                                                <a class="action-btn btn--primary edit square-btn maintenance-mode-show border-0 outline-0 shadow-none d-inline-flex" href="#"><span class="material-icons">edit</span></a>
                                                            </p>
                                                        </div>
                                                @else
                                                    <p>*{{ translate('By turning on maintenance mode Control your all system & function') }}</p>
                                                @endif

                                                @if($config && count($selectedMaintenanceSystem) > 0)
                                                    <div class="d-flex flex-wrap gap-3 mt-3 align-items-center">
                                                        <h6 class="mb-0">
                                                            {{ translate('Selected Systems') }}
                                                        </h6>
                                                        <ul class="selected-systems d-flex flex-wrap bg-soft-dark px-4 py-2 mb-0 rounded fs-12">
                                                            @foreach($selectedMaintenanceSystem as $system)
                                                                <li>{{ ucwords(str_replace('_', ' ', $system)) }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif

                                            </div>
                                            <div class="w-100 max-w320">
                                                <div class="d-flex justify-content-between align-items-center border rounded mb-2 px-3 py-2">
                                                    <h5 class="mb-0">{{translate('maintenance_mode')}}</h5>

                                                    <label class="switcher ml-auto mb-0">
                                                        <input type="checkbox" class="switcher_input {{ $config ? 'route-alert-reload' : 'maintenance-mode-show' }}"
                                                               data-route="{{route('admin.business-settings.maintenance-mode-status-update')}}"
                                                               data-message="{{translate('want_to_update_maintenance_mode_status')}}"
                                                               id="maintenance-mode-input"
                                                            {{ $config ? 'checked' : '' }}>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="mb-0">
                                            <span class="material-icons" title="Subscription Management">campaign</span>
                                            {{translate('General Information')}}
                                        </h4>
                                    </div>
                                    <div class="card-body p-30">
                                        <form action="javascript:void(0)" method="POST" id="business-info-update-form">
                                            @csrf
                                            @method('PUT')
                                            <div class="discount-type">
                                                <div class="row align-items-end">
                                                    <div class="col-md-6">
                                                        <div class="mb-30">
                                                            <div class="form-floating form-floating__icon">
                                                                <input type="text" class="form-control"
                                                                       name="business_name"
                                                                       placeholder="{{translate('business_name')}} *"
                                                                       required=""
                                                                       value="{{$dataValues->where('key_name','business_name')->first()->live_values}}">
                                                                <label>{{translate('business_name')}} *</label>
                                                                <span class="material-icons">subtitles</span>
                                                            </div>
                                                        </div>

                                                        <div class="mb-30">
                                                            <div class="form-floating">
                                                                <label
                                                                    for="business_phone">{{translate('business_phone')}}
                                                                    *</label>
                                                                <input type="text" class="form-control company_phone phone-input-with-country-picker iti__tel-input"
                                                                       name="business_phone"
                                                                       placeholder="{{translate('business_phone')}} *"
                                                                       required="" id="business_phone"
                                                                       oninput="this.value = this.value.replace(/[^+\d]+$/g, '').replace(/(\..*)\./g, '$1');"
                                                                       value="{{$dataValues->where('key_name','business_phone')->first()->live_values}}">
                                                                <div class="">
                                                                    <input type="text" class="country-picker-phone-number w-50" value="{{old('business_phone')}}" name="business_phone" hidden  readonly>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="mb-30">
                                                            <div class="form-floating form-floating__icon">
                                                                <input type="email" class="form-control"
                                                                       name="business_email"
                                                                       placeholder="{{translate('email')}} *"
                                                                       required=""
                                                                       value="{{$dataValues->where('key_name','business_email')->first()->live_values}}">
                                                                <label>{{translate('email')}} *</label>
                                                                <span class="material-icons">mail</span>
                                                            </div>
                                                        </div>
                                                        <div class="mb-30">
                                                            <div class="form-floating">
                                                            <textarea class="form-control" name="business_address"
                                                                      placeholder="{{translate('address')}} *"
                                                                      required="">{{$dataValues->where('key_name','business_address')->first()->live_values}}</textarea>
                                                                <label>{{translate('address')}} *</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="row align-items-end">
                                                            <div class="col-md-6">
                                                                <div
                                                                    class="mb-30 d-flex flex-column align-items-center gap-2">
                                                                    <p class="title-color">{{translate('favicon')}}</p>
                                                                    <div class="upload-file mb-30">
                                                                        <input type="file" class="upload-file__input"
                                                                               name="business_favicon"
                                                                               accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*">
                                                                        <div class="upload-file__img">
                                                                            <img src="{{ $businessFaviconFullPath }}" alt="{{ translate('favicon') }}">
                                                                        </div>
                                                                        <span class="upload-file__edit">
                                                                            <span class="material-icons">edit</span>
                                                                        </span>
                                                                    </div>
                                                                    <p class="opacity-75 max-w220">{{translate('Image format - jpg, png,
                                                                    jpeg, gif Image Size - maximum size 2 MB Image Ratio -
                                                                    1:1')}}</p>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div
                                                                    class="mb-30 d-flex flex-column align-items-center gap-2">
                                                                    <p class="title-color">{{translate('logo')}}</p>
                                                                    <div class="upload-file mb-30 max-w-100">
                                                                        <input type="file"
                                                                               class="upload-file__input"
                                                                               name="business_logo"
                                                                               accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*">
                                                                        <div
                                                                            class="upload-file__img upload-file__img_banner ratio-none">
                                                                            <img src="{{ $businessLogoFullPath }}" alt="{{ translate('logo') }}">
                                                                        </div>
                                                                        <span class="upload-file__edit">
                                                                            <span class="material-icons">edit</span>
                                                                        </span>
                                                                    </div>
                                                                    <p class="opacity-75 max-w220">{{translate('Image format - jpg, png, jpeg, gif Image Size - maximum size 2 MB Image Ratio - 3:1')}}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row align-items-end">
                                                    <div class="col-md-6 col-12 mb-30">
                                                        <div class="mb-2">{{translate('Country')}}</div>
                                                        @php($countryCode=$dataValues->where('key_name','country_code')->first()->live_values)
                                                        <select class="js-select theme-input-style w-100"
                                                                name="country_code">
                                                            <option value="0" selected
                                                                    disabled>{{translate('---Select_Country---')}}</option>
                                                            @foreach(COUNTRIES as $country)
                                                                <option
                                                                    value="{{$country['code']}}" {{$countryCode==$country['code']?'selected':''}}>
                                                                    {{$country['name']}}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 col-12 mb-30">
                                                        <div class="mb-2">{{translate('Currency Code')}}</div>
                                                        @php($currencyCode=$dataValues->where('key_name','currency_code')->first()->live_values)
                                                        <select class="js-select theme-input-style w-100"
                                                                name="currency_code" id="change_currency">
                                                            <option value="0" selected
                                                                    disabled>{{translate('---Select_Currency---')}}</option>
                                                            @foreach(CURRENCIES as $currency)
                                                                <option
                                                                    value="{{$currency['code']}}" {{$currencyCode==$currency['code']?'selected':''}}>
                                                                    {{$currency['name']}} ( {{$currency['symbol']}} )
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 col-12 mb-30">
                                                        <div
                                                            class="mb-2">{{translate('Currency Symbol Position')}}</div>
                                                        @php($position=$dataValues->where('key_name','currency_symbol_position')->first()->live_values)
                                                        <select class="js-select theme-input-style w-100"
                                                                name="currency_symbol_position">
                                                            <option value="0" selected
                                                                    disabled>{{translate('---Select_Corrency_Symbol_Position---')}}</option>
                                                            <option value="right" {{$position=='right'?'selected':''}}>
                                                                {{translate('right')}}
                                                            </option>
                                                            <option value="left" {{$position=='left'?'selected':''}}>
                                                                {{translate('left')}}
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 col-12 mb-30">
                                                        <div class="form-floating">
                                                            <input type="number" class="form-control"
                                                                   name="currency_decimal_point"
                                                                   min="0"
                                                                   max="10"
                                                                   placeholder="{{translate('ex: 2')}} *"
                                                                   required=""
                                                                   value="{{$dataValues->where('key_name','currency_decimal_point')->first()->live_values}}">
                                                            <label>{{translate('decimal_point_after_currency')}}
                                                                *</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-12 mb-30">
                                                        <div class="form-floating form-floating__icon">
                                                            <input type="number" class="form-control"
                                                                   name="default_commission"
                                                                   min="0"
                                                                   max="100"
                                                                   step="any"
                                                                   placeholder="{{translate('ex: 2')}} *"
                                                                   required=""
                                                                   value="{{$dataValues->where('key_name','default_commission')->first()->live_values}}">
                                                            <label>{{translate('default_commission_for_admin')}} ( % )
                                                                *</label>
                                                            <span class="material-icons">percent</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-12 mb-30">
                                                        <div class="form-floating">
                                                            <input type="number" class="form-control"
                                                                   name="pagination_limit"
                                                                   placeholder="{{translate('ex: 2')}} *"
                                                                   min="1"
                                                                   required=""
                                                                   value="{{$dataValues->where('key_name','pagination_limit')->first()->live_values}}">
                                                            <label>{{translate('pagination_limit')}} *</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-12 mb-30">
                                                        <div class="form-floating form-floating__icon">
                                                            <input type="number" class="form-control"
                                                                   name="minimum_withdraw_amount"
                                                                   placeholder="{{translate('ex: 100')}} *"
                                                                   min="1"
                                                                   step="any"
                                                                   required
                                                                   value="{{$dataValues->where('key_name','minimum_withdraw_amount')->first()->live_values??''}}">
                                                            <label>{{translate('minimum_withdraw_amount')}} *</label>
                                                            <span class="material-icons">local_atm</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-12 mb-30">
                                                        <div class="form-floating form-floating__icon">
                                                            <input type="number" class="form-control"
                                                                   name="maximum_withdraw_amount"
                                                                   placeholder="{{translate('ex: 2000')}} *"
                                                                   min="1"
                                                                   step="any"
                                                                   required
                                                                   value="{{$dataValues->where('key_name','maximum_withdraw_amount')->first()->live_values??''}}">
                                                            <label>{{translate('maximum_withdraw_amount')}} *</label>
                                                            <span class="material-icons">local_atm</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-12 mb-30 mt-4">
                                                        <div class="mb-2">{{translate('Time Zone')}}</div>
                                                        @php($timeZone=$dataValues->where('key_name','time_zone')->first()->live_values)
                                                        <select class="js-select theme-input-style w-100"
                                                                name="time_zone">
                                                            <option value="0" selected
                                                                    disabled>{{translate('---Select_Time_Zone---')}}</option>
                                                            @foreach(TIME_ZONES as $time)
                                                                <option
                                                                    value="{{$time['tzCode']}}" {{$timeZone==$time['tzCode']?'selected':''}}>{{$time['tzCode']}}
                                                                    UTC {{$time['utc']}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="col-md-6 col-12 mb-30 mt-4">
                                                        <div class="mb-2">{{translate('Time Format')}}</div>
                                                        @php($timeFormat=$dataValues->where('key_name','time_format')->first()->live_values ?? '24h')
                                                        <select class="js-select theme-input-style w-100"
                                                                name="time_format">
                                                            <option
                                                                value="12" {{$timeFormat=='12'?'selected':''}}>{{translate('12_hour')}}</option>
                                                            <option
                                                                value="24" {{$timeFormat=='24'?'selected':''}}>{{translate('24_hour')}}</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-6 col-12 mb-30 gap-3">
                                                        @php($value=$dataValues->where('key_name','phone_number_visibility_for_chatting')->first()->live_values??null)
                                                        <div class="border p-3 rounded d-flex justify-content-between">
                                                            <div
                                                                class="d-flex align-items-center gap-2">{{translate('Phone number visibility for chatting')}}
                                                                <i class="material-icons" data-bs-toggle="tooltip"
                                                                   data-bs-placement="top"
                                                                   title="{{translate('Customers or providers can not see each other phone numbers during chatting')}}"
                                                                >info</i>
                                                            </div>
                                                            <label class="switcher">
                                                                <input class="switcher_input" type="checkbox"
                                                                       name="phone_number_visibility_for_chatting"
                                                                       value="1"
                                                                    {{isset($value) && $value == '1' ? 'checked' : ''}}>
                                                                <span class="switcher_control"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-12 mb-30 gap-3">
                                                        @php($value=$dataValues->where('key_name','direct_provider_booking')->first()->live_values??null)
                                                        <div class="border p-3 rounded d-flex justify-content-between">
                                                            <div
                                                                class="d-flex align-items-center gap-2">{{translate('Direct Provider Booking')}}
                                                                <i class="material-icons" data-bs-toggle="tooltip"
                                                                   data-bs-placement="top"
                                                                   title="{{translate('Customers can directly book any provider')}}"
                                                                >info</i>
                                                            </div>
                                                            <label class="switcher">
                                                                <input class="switcher_input" type="checkbox"
                                                                       name="direct_provider_booking" value="1"
                                                                    {{isset($value) && $value == '1' ? 'checked' : ''}}>
                                                                <span class="switcher_control"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-12 mb-30">
                                                        @php($value=$dataValues->where('key_name','booking_notification')->first()->live_values??null)
                                                        <div class="border p-3 rounded d-flex justify-content-between">
                                                            <div class="d-flex align-items-center gap-2">{{translate('booking_notification')}}
                                                                <i class="material-icons" data-bs-toggle="tooltip"
                                                                   data-bs-placement="top"
                                                                   title="{{translate('Provider will get a pop-up notification with sounds for every booking placed by customers.')}}"
                                                                >info</i>
                                                            </div>
                                                            <label class="switcher">
                                                                <input class="switcher_input" type="checkbox"
                                                                       name="booking_notification" value="1"
                                                                        {{isset($value) && $value == '1' ? 'checked' : ''}}>
                                                                <span class="switcher_control"></span>
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6 col-12 mb-30">
                                                        <div class="mb-2">{{translate('booking_notification_type')}}
                                                            <i class="material-icons" data-bs-toggle="tooltip"
                                                               data-bs-placement="top"
                                                               title="{{translate('For Firebase a single real-time notification will be sent upon booking placement with no repetition. For the Manual option notifications will appear at 10-second intervals until the booking is viewed.')}}"
                                                            >info</i>
                                                        </div>
                                                        @php($value=$dataValues->where('key_name','booking_notification_type')->first()->live_values??null)
                                                        <select class="js-select theme-input-style w-100"
                                                                name="booking_notification_type">
                                                            <option
                                                                    value="manual" {{$value=='manual'?'selected':''}}>{{translate('manual')}}</option>
                                                            <option
                                                                    value="firebase" {{$value=='firebase'?'selected':''}}>{{translate('firebase')}}</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-12 mb-30">
                                                        <div class="form-floating form-floating__icon">
                                                            <input type="text" class="form-control" name="footer_text"
                                                                   placeholder="{{translate('ex:_all_right_reserved')}} *"
                                                                   required=""
                                                                   value="{{$dataValues->where('key_name','footer_text')->first()->live_values}}">
                                                            <label>{{translate('footer_text')}} *</label>
                                                            <span class="material-icons">description</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 mb-30">
                                                        <div class="form-floating">
                                                            <textarea type="text" class="form-control"
                                                                      name="cookies_text"
                                                                      placeholder="{{translate('ex:_al_right_reserved')}} *"
                                                                      required>{{$dataValues->where('key_name','cookies_text')->first()->live_values??null}}</textarea>
                                                            <label>{{translate('cookies_text')}} *</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @can('business_update')
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

                    @if($webPage=='service_setup')
                        <div class="tab-content">
                            <div class="tab-pane fade {{$webPage=='service_setup'?'active show':''}}"
                                 id="business-info">
                                <div class="card">
                                    <div class="card-body p-30">
                                        <form action="{{route('admin.business-settings.set-service-setup')}}"
                                              method="POST">
                                            @csrf
                                            @method('put')
                                            <div class="row">
                                                    <?php
                                                    $cashAfterService = collect([['key' => 'cash_after_service', 'info_message' => 'Customer can pay with cash after receiving the service', 'title' => 'Cash After Service']]);
                                                    $digitalPayment = collect([['key' => 'digital_payment', 'info_message' => 'Customers providers can pay with digital payments', 'title' => 'Digital Payment']]);
                                                    $partialPayment = collect([['key' => 'partial_payment', 'title' => 'Partial Payment', 'info_message' => 'Customer can pay partially with their wallet balance']]);
                                                    $partialPaymentCombinator = collect([['key' => 'partial_payment_combinator', 'title' => 'Can Combine Payment', 'info_message' => 'Admin can set how customers will make the partial payment by clicking on the preferred radio button. This section will be hidden if Partial Payment feature is disabled']]);
                                                    $offlinePayment = collect([['key' => 'offline_payment', 'title' => 'Offline Payment', 'info_message' => 'Offline Payment allows customers to use external payment methods. After payment, they need to use the transaction details while placing bookings. Admin can set if customers can make offline payments or not by enabling/disabling this button']]);
                                                    $guestCheckout = collect([['key' => 'guest_checkout', 'title' => 'Guest Checkout', 'info_message' => 'Admin can off guest checkout']]);
                                                    ?>

                                                <div class="col-md-6 col-12 mb-30">
                                                    <div class="border p-3 rounded d-flex justify-content-between">
                                                        <div>
                                                            <span
                                                                class="text-capitalize">{{translate($cashAfterService[0]['title'])}}</span>
                                                            <i class="material-icons px-1" data-bs-toggle="tooltip"
                                                               data-bs-placement="top"
                                                               title="{{translate($cashAfterService[0]['info_message'] ?? '')}}"
                                                            >info</i>
                                                        </div>
                                                        <label class="switcher">
                                                            @php($value = $dataValues->where('key_name', $cashAfterService[0]['key'])?->first()?->live_values ?? null)
                                                            <input class="switcher_input switch_alert"
                                                                   id="{{$cashAfterService[0]['key']}}"
                                                                   type="checkbox"
                                                                   name="{{$cashAfterService[0]['key']}}"
                                                                   value="1" {{$value ? 'checked' : ''}}
                                                                   data-id="{{$cashAfterService[0]['key']}}"
                                                                   data-message="{{ucfirst(translate($cashAfterService[0]['key']))}}">
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-12 mb-30">
                                                    <div class="border p-3 rounded d-flex justify-content-between">
                                                        <div>
                                                            <span
                                                                class="text-capitalize">{{translate($digitalPayment[0]['title'])}}</span>
                                                            <i class="material-icons px-1" data-bs-toggle="tooltip"
                                                               data-bs-placement="top"
                                                               title="{{translate($digitalPayment[0]['info_message'] ?? '')}}"
                                                            >info</i>
                                                        </div>
                                                        <label class="switcher">
                                                            @php($value = $dataValues->where('key_name', $digitalPayment[0]['key'])?->first()?->live_values ?? null)
                                                            <input class="switcher_input switch_alert"
                                                                   id="{{$digitalPayment[0]['key']}}" type="checkbox"
                                                                   name="{{$digitalPayment[0]['key']}}"
                                                                   value="1" {{$value ? 'checked' : ''}}
                                                                   data-id="{{$digitalPayment[0]['key']}}"
                                                                   data-message="{{ucfirst(translate($digitalPayment[0]['key']))}}"
                                                            >
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-12 mb-30">
                                                    <div class="border p-3 rounded d-flex justify-content-between">
                                                        <div>
                                                            <span
                                                                class="text-capitalize">{{translate($partialPayment[0]['title'])}}</span>
                                                            <i class="material-icons px-1" data-bs-toggle="tooltip"
                                                               data-bs-placement="top"
                                                               title="{{translate($partialPayment[0]['info_message'] ?? '')}}"
                                                            >info</i>
                                                        </div>
                                                        <label class="switcher">
                                                            @php($value = $dataValues->where('key_name', $partialPayment[0]['key'])?->first()?->live_values ?? null)
                                                            <input class="switcher_input switch_alert"
                                                                   id="{{$partialPayment[0]['key']}}" type="checkbox"
                                                                   name="{{$partialPayment[0]['key']}}"
                                                                   value="1" {{$value ? 'checked' : ''}}
                                                                   data-id="{{$partialPayment[0]['key']}}"
                                                                   data-message="{{ucfirst(translate($partialPayment[0]['key']))}}"
                                                            >
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-12 mb-30">
                                                    <label for="partials_payment_combinator"
                                                           class="mb-2">{{translate($partialPaymentCombinator[0]['title'])}}</label>
                                                    <div
                                                        class="border p-3 rounded d-flex justify-content-between gap-2">
                                                        <div class="d-flex align-items-start gap-3 gap-xl-4">
                                                            <div class="custom-radio">
                                                                <input type="radio" id="cash_after_service_combinator"
                                                                       name="partial_payment_combinator"
                                                                       value="cash_after_service" {{$dataValues->where('key_name', $partialPaymentCombinator[0]['key'])->first()->live_values == 'cash_after_service' ? 'checked' : ''}}>
                                                                <label
                                                                    for="cash_after_service_combinator">{{translate('Cash After Service')}}</label>
                                                            </div>
                                                            <div class="custom-radio">
                                                                <input type="radio" id="digital_payment_combinator"
                                                                       name="partial_payment_combinator"
                                                                       value="digital_payment" {{$dataValues->where('key_name', $partialPaymentCombinator[0]['key'])->first()->live_values == 'digital_payment' ? 'checked' : ''}}>
                                                                <label
                                                                    for="digital_payment_combinator">{{translate('Digital Payment')}}</label>
                                                            </div>
                                                            <div class="custom-radio">
                                                                <input type="radio" id="offline_payment_combinator"
                                                                       name="partial_payment_combinator"
                                                                       value="offline_payment" {{$dataValues->where('key_name', $partialPaymentCombinator[0]['key'])->first()->live_values == 'offline_payment' ? 'checked' : ''}}>
                                                                <label
                                                                    for="offline_payment_combinator">{{translate('Offline Payment')}}</label>
                                                            </div>
                                                            <div class="custom-radio">
                                                                <input type="radio" id="all_combinator"
                                                                       name="partial_payment_combinator"
                                                                       value="all" {{$dataValues->where('key_name', $partialPaymentCombinator[0]['key'])->first()->live_values == 'all' ? 'checked' : ''}}>
                                                                <label for="all_combinator">{{translate('All')}}</label>
                                                            </div>
                                                        </div>

                                                        <div>
                                                            <i class="material-icons cursor-pointer"
                                                               data-bs-toggle="tooltip"
                                                               title="{{$partialPaymentCombinator[0]['info_message']}}">info</i>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-12 mb-30">
                                                    <div class="border p-3 rounded d-flex justify-content-between">
                                                        <div>
                                                            <span
                                                                class="text-capitalize">{{translate($offlinePayment[0]['title'])}}</span>
                                                            <i class="material-icons px-1" data-bs-toggle="tooltip"
                                                               data-bs-placement="top"
                                                               title="{{translate($offlinePayment[0]['info_message'] ?? '')}}"
                                                            >info</i>
                                                        </div>
                                                        <label class="switcher">
                                                            @php($value = $dataValues->where('key_name', $offlinePayment[0]['key'])?->first()?->live_values ?? null)
                                                            <input class="switcher_input switch_alert"
                                                                   id="{{$offlinePayment[0]['key']}}" type="checkbox"
                                                                   name="{{$offlinePayment[0]['key']}}"
                                                                   value="1" {{$value ? 'checked' : ''}}
                                                                   data-id="{{$offlinePayment[0]['key']}}"
                                                                   data-message="{{ucfirst(translate($offlinePayment[0]['key']))}}"
                                                            >
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-12 mb-30">
                                                    <div class="border p-3 rounded d-flex justify-content-between">
                                                        <div>
                                                            <span
                                                                class="text-capitalize">{{translate($guestCheckout[0]['title'])}}</span>
                                                            <i class="material-icons px-1" data-bs-toggle="tooltip"
                                                               data-bs-placement="top"
                                                               title="{{translate($guestCheckout[0]['info_message'] ?? '')}}"
                                                            >info</i>
                                                        </div>
                                                        <label class="switcher">
                                                            @php($value = $dataValues->where('key_name', $guestCheckout[0]['key'])?->first()?->live_values ?? null)
                                                            <input class="switcher_input switch_alert"
                                                                   id="{{$guestCheckout[0]['key']}}" type="checkbox"
                                                                   name="{{$guestCheckout[0]['key']}}"
                                                                   value="1" {{$value ? 'checked' : ''}}
                                                                   data-id="{{$guestCheckout[0]['key']}}"
                                                                   data-message="{{ucfirst(translate($guestCheckout[0]['key']))}}"
                                                            >
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                </div>

                                            </div>
                                            @can('business_update')
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

                    @if($webPage=='bookings')
                        <div class="tab-content">
                            <div class="tab-pane fade {{$webPage=='bookings'?'active show':''}}"
                                 id="business-info">
                                <div class="card">
                                    <div class="card-body p-30">
                                        <div class="d-flex gap-2 align-items-center mb-4">
                                            <img width="20"
                                                 src="{{asset('assets/admin-module/img/icons/announcement.png')}}"
                                                 alt="">
                                            <h4>{{translate('Bookings_Setup')}}</h4>
                                        </div>
                                        <form action="javascript:void(0)" method="POST" id="booking-system-update-form">
                                            @csrf
                                            @method('PUT')
                                            <div class="row g-4">
                                                <div class="col-md-6 col-12">
                                                    <div class="border p-3 rounded d-flex justify-content-between">
                                                        <div class="d-flex gap-2 align-items-center">
                                                            <span>{{translate('bidding_System')}}</span>
                                                            <i class="material-symbols-outlined cursor-pointer"
                                                               data-bs-toggle="tooltip"
                                                               data-bs-placement="top"
                                                               title="{{translate('Customers can use the bid feature to create posts for customized service requests when the option is enabled.')}}"
                                                            >info</i>
                                                        </div>
                                                        <label class="switcher">
                                                            <input class="switcher_input switch_alert" type="checkbox"
                                                                   name="bidding_status" value="1" id="bidding_status"
                                                                   {{$dataValues->where('key_name', 'bidding_status')->first()?->live_values ?'checked':''}}
                                                                   data-id="bidding_status"
                                                                   data-message="Want to change the status of bidding system">
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-12">
                                                    <div class="form-floating">
                                                        <input class="form-control" name="bidding_post_validity"
                                                               placeholder="{{translate('Post Validation (days)')}} *"
                                                               type="number" required
                                                               value="{{$dataValues->where('key_name', 'bidding_post_validity')->first()->live_values ?? ''}}">
                                                        <label>{{translate('Post Validation (days)')}} *</label>
                                                        <i class="material-symbols-outlined cursor-pointer"
                                                           data-bs-toggle="tooltip"
                                                           data-bs-placement="top"
                                                           title="{{translate('User  can use the bid feature to create post for customize service requests while the option is enabled')}}"
                                                        >info</i>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-12">
                                                    <div class="border p-3 rounded d-flex justify-content-between">
                                                        <div class="d-flex gap-2 align-items-center">
                                                            <span>{{translate('See Other Providers Offers')}}</span>
                                                            <i class="material-symbols-outlined cursor-pointer"
                                                               data-bs-toggle="tooltip"
                                                               data-bs-placement="top"
                                                               title="{{translate('Enabling the option allows any provider to view the bid amounts offered by other providers.')}}"
                                                            >info</i>
                                                        </div>
                                                        <label class="switcher">
                                                            <input class="switcher_input switch_alert" type="checkbox"
                                                                   name="bid_offers_visibility_for_providers" value="1"
                                                                   id="bid_offer_visibility"
                                                                   data-id="bid_offer_visibility"
                                                                   data-message="Want to change the status of provider offers"

                                                                {{$dataValues->where('key_name', 'bid_offers_visibility_for_providers')->first()?->live_values ?'checked':''}}>
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-12">
                                                    <div class="form-floating">
                                                        <input class="form-control spin-none" name="max_booking_amount"
                                                               placeholder="{{translate('Post Validation (days)')}} *"
                                                               type="number" required step="any"
                                                               value="{{$dataValues->where('key_name', 'max_booking_amount')->first()->live_values ?? ''}}">
                                                        <label>{{translate('max_booking_amount')}} *</label>
                                                        <i class="material-symbols-outlined cursor-pointer"
                                                           data-bs-toggle="tooltip"
                                                           data-bs-placement="top"
                                                           title="{{translate('Set the maximum value for booking a service. Any amount exceeding this limit will require verification for that service.')}}"
                                                        >info</i>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-12">
                                                    <div class="border p-3 rounded d-flex justify-content-between">
                                                        <div class="d-flex gap-2 align-items-center">
                                                            <span>{{translate('confirmation_OTP_for_Complete_Service')}}</span>
                                                            <i class="material-symbols-outlined cursor-pointer"
                                                               data-bs-toggle="tooltip"
                                                               data-bs-placement="top"
                                                               title="{{translate('To ensure thorough OTP verification of the complete service provided')}}"
                                                            >info</i>
                                                        </div>
                                                        <label class="switcher">
                                                            <input class="switcher_input switch_alert" type="checkbox"
                                                                   name="booking_otp" value="1" id="booking_otp"
                                                                   data-id="booking_otp"
                                                                   data-message="Want to change the status of confirmation otp for complete service"
                                                                {{$dataValues->where('key_name', 'booking_otp')->first()?->live_values ?'checked':''}}>
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-12">
                                                    <div class="form-floating">
                                                        <input class="form-control" name="min_booking_amount"
                                                               placeholder="{{translate('Post Validation (days)')}} *"
                                                               type="number" required step="any"
                                                               value="{{$dataValues->where('key_name', 'min_booking_amount')->first()->live_values ?? ''}}">
                                                        <label>{{translate('min_booking_amount')}} *</label>
                                                        <i class="material-symbols-outlined cursor-pointer"
                                                           data-bs-toggle="tooltip"
                                                           data-bs-placement="top"
                                                           title="{{translate('Determine the minimum amount needed to book a service. No bookings can be made if the cost is below this.')}}"
                                                        >info</i>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 col-12">
                                                    <div class="form-floating">
                                                        <input class="form-control remove-spin"
                                                               name="additional_charge_label_name"
                                                               placeholder="{{translate('Additional Charge Label')}} *"
                                                               type="text" required
                                                               value="{{$dataValues->where('key_name', 'additional_charge_label_name')->first()->live_values ?? ''}}"
                                                        >
                                                        <label>{{translate('Additional Charge Label')}}* </label>

                                                        <span class="material-symbols-outlined cursor-pointer"
                                                              data-bs-toggle="tooltip"
                                                              title="{{ translate('This will be shown as the label for the additional charge to the customer.') }}">info</span>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 col-12">
                                                    <div class="form-floating">
                                                        <input class="form-control remove-spin"
                                                               name="additional_charge_fee_amount"
                                                               placeholder="{{translate('Additional charge fee')}} *"
                                                               type="number" min="0" step="any" required
                                                               value="{{$dataValues->where('key_name', 'additional_charge_fee_amount')->first()->live_values ?? ''}}"
                                                        >
                                                        <label>{{translate('Additional charge fee')}}* </label>
                                                        <span class="material-symbols-outlined cursor-pointer"
                                                              data-bs-toggle="tooltip"
                                                              title="{{ translate('Specify the necessary amount for the additional charge.') }}">info</span>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-12">
                                                    <div class="border p-3 rounded d-flex justify-content-between">
                                                        <div class="d-flex gap-2 align-items-center">
                                                            <span>{{translate('Service_complete_Photo_Evidence')}}</span>
                                                            <i class="material-symbols-outlined cursor-pointer"
                                                               data-bs-toggle="tooltip"
                                                               data-bs-placement="top"
                                                               title="{{translate('Upload images as evidence to confirm the completion of the service.')}}"
                                                            >info</i>
                                                        </div>
                                                        <label class="switcher">
                                                            <input class="switcher_input switch_alert" type="checkbox"
                                                                   name="service_complete_photo_evidence" value="1"
                                                                   id="photo_evidence"
                                                                   {{$dataValues->where('key_name', 'service_complete_photo_evidence')->first()?->live_values ?'checked':''}}
                                                                   data-id="photo_evidence"
                                                                   data-message="Want to change the status Take Picture Before Complete">
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-12">
                                                    <div class="border p-3 rounded d-flex justify-content-between">
                                                        <div class="d-flex gap-2 align-items-center">
                                                            <span>{{translate('Additional_Charge_on_Booking')}}</span>
                                                            <i class="material-symbols-outlined cursor-pointer"
                                                               data-bs-toggle="tooltip"
                                                               data-bs-placement="top"
                                                               title="{{translate('Allow for specifying an extra fee to be collected for a booking.')}}"
                                                            >info</i>
                                                        </div>
                                                        <label class="switcher">
                                                            <input class="switcher_input switch_alert" type="checkbox"
                                                                   name="booking_additional_charge" value="1"
                                                                   id="booking_additional_charge"
                                                                   {{$dataValues->where('key_name', 'booking_additional_charge')->first()?->live_values ?'checked':''}}
                                                                   data-id="booking_additional_charge"
                                                                   data-message="Want to change the status Additional Charge on Booking">
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                </div>


                                                <div class="col-md-6 col-12">
                                                    <div class="border p-3 rounded d-flex justify-content-between">
                                                        <div class="d-flex gap-2 align-items-center">
                                                            <span>{{translate('instant_Booking')}}</span>
                                                            <i class="material-symbols-outlined cursor-pointer"
                                                               data-bs-toggle="tooltip"
                                                               title="{{translate('Enable customers to instantly book a service for a specific date without delays.')}}"
                                                            >info</i>
                                                        </div>
                                                        <label class="switcher">
                                                            <input class="switcher_input switch_alert" type="checkbox"
                                                                   id="instant_booking"
                                                                   value="1"
                                                                   {{$dataValues->where('key_name', 'instant_booking')->first()?->live_values ?'checked':''}}
                                                                   data-id="instant_booking" name="instant_booking"
                                                                   data-message="Want to change the status Instant Booking">
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <div class="border p-3 rounded d-flex justify-content-between">
                                                        <div class="d-flex gap-2 align-items-center">
                                                            <span>{{translate('schedule_Booking')}}</span>
                                                            <i class="material-symbols-outlined cursor-pointer"
                                                               data-bs-toggle="tooltip"
                                                               title="{{translate('Enable scheduling for a specific time and date to book a service')}}"
                                                            >info</i>
                                                        </div>
                                                        <label class="switcher">
                                                            <input class="switcher_input" type="checkbox"
                                                                   id="schedule_booking_switch" name="schedule_booking"
                                                                   {{$dataValues->where('key_name', 'schedule_booking')->first()?->live_values ?'checked':''}}
                                                                   data-id="schedule_booking_switch"
                                                                   value="1"
                                                                   data-message="Want to change the status Schedule Booking">
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="rounded shadow p-4 mt-4" id="schedule_booking_section">
                                                <div class="row align-items-center justify-content-between gy-4">
                                                    <div class="col-lg-6">
                                                        <div class="form-check d-flex align-items-center gap-1">
                                                            <input class="form-check-input check-28" type="checkbox"
                                                                   value="1" id="schedule_booking_checkbox"
                                                                   {{$dataValues->where('key_name', 'schedule_booking_time_restriction')->first()?->live_values ?'checked':''}}
                                                                   data-id="schedule_booking_time_restriction"
                                                                   name="schedule_booking_time_restriction"
                                                                   data-message="Want to change the status Schedule Booking Time Restriction">
                                                            <label class="form-check-label"
                                                                   for="schedule_booking_checkbox">
                                                                {{translate('Check the box')}} <br>
                                                                {{translate('If you want to add time restriction on schedule booking')}}
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 col-xl-5" id="schedule_booking_restriction">
                                                        <div class="d-flex flex-wrap flex-sm-nowrap gap-2">
                                                            <div class="form-floating flex-grow-1">
                                                                <input class="form-control" min="1" type="number"
                                                                       value="{{$dataValues->where('key_name', 'advanced_booking_restriction_value')->first()?->live_values}}"
                                                                       name="advanced_booking_restriction_value"
                                                                       required>
                                                                <label>{{translate('Advance Booking Restriction')}}
                                                                    *</label>
                                                                <i class="material-symbols-outlined cursor-pointer"
                                                                   data-bs-toggle="tooltip"
                                                                   data-bs-placement="top"
                                                                   title="{{translate('Set a delay period for customers to book services in advance. Once enabled, customers can only schedule booking after the specified time period has elapsed. Adjust settings according to your business needs.')}}"
                                                                >info</i>
                                                            </div>

                                                            <select class="form-select w-auto"
                                                                    name="advanced_booking_restriction_type">
                                                                <option value="0" selected
                                                                        disabled>{{translate('Select')}}</option>
                                                                <option
                                                                    value="hour" {{$dataValues->where('key_name', 'advanced_booking_restriction_type')->first()?->live_values == 'hour' ?'selected':''}}>{{translate('Hour')}}</option>
                                                                <option
                                                                    value="day" {{$dataValues->where('key_name', 'advanced_booking_restriction_type')->first()?->live_values == 'day' ?'selected':''}}>{{translate('Days')}}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @can('business_update')
                                                <div class="d-flex gap-2 justify-content-end mt-4">
                                                    <button type="reset" class="btn btn-secondary" id="booking-reset">
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

                    @if($webPage=='promotional_setup')
                        <div class="tab-content">
                            <div class="tab-pane fade {{$webPage=='promotional_setup'?'active show':''}}">
                                <div class="row">
                                    @php($data = $dataValues->where('key_name', 'discount_cost_bearer')->first()->live_values ?? null)
                                    <div class="col-lg-6 col-12 mb-30">
                                        <div class="card">
                                            <div class="card-header">
                                                <h4 class="page-title d-flex align-items-center gap-2">
                                                    <i class="material-icons">redeem</i>
                                                    {{translate('Normal_Discount')}}
                                                </h4>
                                            </div>
                                            <div class="card-body p-30">
                                                <h5 class="pb-4">{{translate('Discount_Cost_Bearer')}}</h5>
                                                <form action="{{route('admin.business-settings.set-promotion-setup')}}"
                                                      method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="d-flex flex-column flex-sm-row flex-wrap gap-3">
                                                        <div
                                                            class="d-flex align-items-start flex-column gap-3 gap-xl-4 mb-30 flex-grow-1">
                                                            <div class="custom-radio">
                                                                <input type="radio" id="admin-select__discount"
                                                                       name="bearer"
                                                                       value="admin" {{isset($data) && $data['bearer'] == 'admin' ? 'checked' : ''}}>
                                                                <label
                                                                    for="admin-select__discount">{{translate('Admin')}}</label>
                                                            </div>
                                                            <div class="custom-radio">
                                                                <input type="radio" id="provider-select__discount"
                                                                       name="bearer"
                                                                       value="provider" {{isset($data) && $data['bearer'] == 'provider' ? 'checked' : ''}}>
                                                                <label
                                                                    for="provider-select__discount">{{translate('Provider')}}</label>
                                                            </div>
                                                            <div class="custom-radio">
                                                                <input type="radio" id="both-select__discount"
                                                                       name="bearer"
                                                                       value="both" {{isset($data) && $data['bearer'] == 'both' ? 'checked' : ''}}>
                                                                <label
                                                                    for="both-select__discount">{{translate('Both')}}</label>
                                                            </div>
                                                        </div>

                                                        <div
                                                            class="flex-grow-1 {{isset($data) && ($data['bearer'] != 'admin' && $data['bearer'] != 'provider') ? '' : 'd-none'}}"
                                                            id="bearer-section__discount">
                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="number" class="form-control"
                                                                           name="admin_percentage"
                                                                           id="admin_percentage__discount"
                                                                           placeholder="{{translate('Admin_Percentage')}} (%)"
                                                                           value="{{!is_null($data) ? $data['admin_percentage'] : ''}}"
                                                                           min="0" max="100" step="any">
                                                                    <label>{{translate('Admin_Percentage')}} (%)</label>
                                                                </div>
                                                            </div>
                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="number" class="form-control"
                                                                           name="provider_percentage"
                                                                           id="provider_percentage__discount"
                                                                           placeholder="{{translate('Provider_Percentage')}} (%)"
                                                                           value="{{!is_null($data) ? $data['provider_percentage'] : ''}}"
                                                                           min="0" max="100" step="any">
                                                                    <label>{{translate('Provider_Percentage')}}
                                                                        (%)</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input type="text" name="type" value="discount" class="d-none">
                                                    @can('business_update')
                                                        <div class="d-flex justify-content-end gap-20">
                                                            <button type="submit" class="btn btn--primary demo_check">
                                                                {{translate('update')}}
                                                            </button>
                                                        </div>
                                                    @endcan
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    @php($data = $dataValues->where('key_name', 'campaign_cost_bearer')->first()->live_values ?? null)
                                    <div class="col-lg-6 col-12 mb-30">
                                        <div class="card">
                                            <div class="card-header">
                                                <h4 class="page-title d-flex align-items-center gap-2">
                                                    <i class="material-icons">campaign</i>
                                                    {{translate('Campaign_Discount')}}
                                                </h4>
                                            </div>
                                            <div class="card-body p-30">
                                                <h5 class="pb-4">{{translate('Campaign_Cost_Bearer')}}</h5>
                                                <form action="{{route('admin.business-settings.set-promotion-setup')}}"
                                                      method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="d-flex flex-column flex-sm-row flex-wrap gap-3">
                                                        <div
                                                            class="d-flex align-items-start flex-column gap-3 gap-xl-4 mb-30 flex-grow-1">
                                                            <div class="custom-radio">
                                                                <input type="radio" id="admin-select__campaign"
                                                                       name="bearer"
                                                                       value="admin" {{isset($data) && $data['bearer'] == 'admin' ? 'checked' : ''}}>
                                                                <label
                                                                    for="admin-select__campaign">{{translate('Admin')}}</label>
                                                            </div>
                                                            <div class="custom-radio">
                                                                <input type="radio" id="provider-select__campaign"
                                                                       name="bearer"
                                                                       value="provider" {{isset($data) && $data['bearer'] == 'provider' ? 'checked' : ''}}>
                                                                <label
                                                                    for="provider-select__campaign">{{translate('Provider')}}</label>
                                                            </div>
                                                            <div class="custom-radio">
                                                                <input type="radio" id="both-select__campaign"
                                                                       name="bearer"
                                                                       value="both" {{isset($data) && $data['bearer'] == 'both' ? 'checked' : ''}}>
                                                                <label
                                                                    for="both-select__campaign">{{translate('Both')}}</label>
                                                            </div>
                                                        </div>

                                                        <div
                                                            class="flex-grow-1 {{isset($data) && ($data['bearer'] != 'admin' && $data['bearer'] != 'provider') ? '' : 'd-none'}}"
                                                            id="bearer-section__campaign">
                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="number" class="form-control"
                                                                           name="admin_percentage"
                                                                           id="admin_percentage__campaign"
                                                                           placeholder="{{translate('Admin_Percentage')}} (%)"
                                                                           value="{{!is_null($data) ? $data['admin_percentage'] : ''}}"
                                                                           min="0" max="100" step="any">
                                                                    <label>{{translate('Admin_Percentage')}} (%)</label>
                                                                </div>
                                                            </div>
                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="number" class="form-control"
                                                                           name="provider_percentage"
                                                                           id="provider_percentage__campaign"
                                                                           placeholder="{{translate('Provider_Percentage')}} (%)"
                                                                           value="{{!is_null($data) ? $data['provider_percentage'] : ''}}"
                                                                           min="0" max="100" step="any">
                                                                    <label>{{translate('Provider_Percentage')}}
                                                                        (%)</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input type="text" name="type" value="campaign" class="d-none">
                                                    @can('business_update')
                                                        <div class="d-flex justify-content-end gap-20">
                                                            <button type="submit" class="btn btn--primary demo_check">
                                                                {{translate('update')}}
                                                            </button>
                                                        </div>
                                                    @endcan
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    @php($data = $dataValues->where('key_name', 'coupon_cost_bearer')->first()->live_values ?? null)
                                    <div class="col-lg-6 col-12 mb-30">
                                        <div class="card">
                                            <div class="card-header">
                                                <h4 class="page-title d-flex align-items-center gap-2">
                                                    <i class="material-icons">sell</i>
                                                    {{translate('Coupon_Discount')}}
                                                </h4>
                                            </div>
                                            <div class="card-body p-30">
                                                <h5 class="pb-4">{{translate('Coupon_Cost_Bearer')}}</h5>
                                                <form action="{{route('admin.business-settings.set-promotion-setup')}}"
                                                      method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="d-flex flex-column flex-sm-row flex-wrap gap-3">
                                                        <div
                                                            class="d-flex align-items-start flex-column gap-3 gap-xl-4 mb-30 flex-grow-1">
                                                            <div class="custom-radio">
                                                                <input type="radio" id="admin-select__coupon"
                                                                       name="bearer"
                                                                       value="admin" {{isset($data) && $data['bearer'] == 'admin' ? 'checked' : ''}}>
                                                                <label
                                                                    for="admin-select__coupon">{{translate('Admin')}}</label>
                                                            </div>
                                                            <div class="custom-radio">
                                                                <input type="radio" id="provider-select__coupon"
                                                                       name="bearer"
                                                                       value="provider" {{isset($data) && $data['bearer'] == 'provider' ? 'checked' : ''}}>
                                                                <label
                                                                    for="provider-select__coupon">{{translate('Provider')}}</label>
                                                            </div>
                                                            <div class="custom-radio">
                                                                <input type="radio" id="both-select__coupon"
                                                                       name="bearer"
                                                                       value="both" {{isset($data) && $data['bearer'] == 'both' ? 'checked' : ''}}>
                                                                <label
                                                                    for="both-select__coupon">{{translate('Both')}}</label>
                                                            </div>
                                                        </div>

                                                        <div
                                                            class="flex-grow-1 {{isset($data) && ($data['bearer'] != 'admin' && $data['bearer'] != 'provider') ? '' : 'd-none'}}"
                                                            id="bearer-section__coupon">
                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="number" class="form-control"
                                                                           name="admin_percentage"
                                                                           id="admin_percentage__coupon"
                                                                           placeholder="{{translate('Admin_Percentage')}} (%)"
                                                                           value="{{!is_null($data) ? $data['admin_percentage'] : ''}}"
                                                                           min="0" max="100" step="any">
                                                                    <label>{{translate('Admin_Percentage')}} (%)</label>
                                                                </div>
                                                            </div>
                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <input type="number" class="form-control"
                                                                           name="provider_percentage"
                                                                           id="provider_percentage__coupon"
                                                                           placeholder="{{translate('Provider_Percentage')}} (%)"
                                                                           value="{{!is_null($data) ? $data['provider_percentage'] : ''}}"
                                                                           min="0" max="100" step="any">
                                                                    <label>{{translate('Provider_Percentage')}}
                                                                        (%)</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input type="text" name="type" value="coupon" class="d-none">
                                                    @can('business_update')
                                                        <div class="d-flex justify-content-end gap-20">
                                                            <button type="submit" class="btn btn--primary demo_check">
                                                                {{translate('update')}}
                                                            </button>
                                                        </div>
                                                    @endcan
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($webPage=='servicemen')
                        <div class="tab-content">
                            <div class="tab-pane fade {{$webPage=='servicemen'?'active show':''}}"
                                 id="business-info">
                                <div class="card">
                                    <div class="card-body p-30">
                                        <form action="{{route('admin.business-settings.set-servicemen')}}"
                                              method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="row g-4">
                                                @php($service_man_booking_cancel = collect([ ['key' => 'serviceman_can_cancel_booking','info_message' => 'Service Men Can Cancel Booking', 'title' => 'Cancel Booking Req'] ]))
                                                @php($service_man_booking_edit = collect([ ['key' => 'serviceman_can_edit_booking','info_message' => 'Service Men Can Edit Booking', 'title' => 'Edit Booking Req'] ]))

                                                <div class="col-md-6 col-12 mb-30">
                                                    <div class="border p-3 rounded d-flex justify-content-between">
                                                        <div>
                                                            <span
                                                                class="text-capitalize">{{translate($service_man_booking_cancel[0]['title'])}}</span>
                                                            <i class="material-icons px-1" data-bs-toggle="tooltip"
                                                               data-bs-placement="top"
                                                               title="{{translate($service_man_booking_cancel[0]['info_message'] ?? '')}}"
                                                            >info</i>
                                                        </div>
                                                        <label class="switcher">
                                                            @php($value = $dataValues->where('key_name', $service_man_booking_cancel[0]['key'])?->first()?->live_values ?? null)
                                                            <input class="switcher_input switch_alert"
                                                                   id="{{$service_man_booking_cancel[0]['key']}}"
                                                                   type="checkbox"
                                                                   name="{{$service_man_booking_cancel[0]['key']}}"
                                                                   value="1" {{$value ? 'checked' : ''}}
                                                                   data-id="{{$service_man_booking_cancel[0]['key']}}"
                                                                   data-message="Want to change the status of {{ucfirst(translate($service_man_booking_cancel[0]['key']))}}"
                                                            >
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-12 mb-30">
                                                    <div class="border p-3 rounded d-flex justify-content-between">
                                                        <div>
                                                            <span
                                                                class="text-capitalize">{{translate($service_man_booking_edit[0]['title'])}}</span>
                                                            <i class="material-icons px-1" data-bs-toggle="tooltip"
                                                               data-bs-placement="top"
                                                               title="{{translate($service_man_booking_edit[0]['info_message'] ?? '')}}"
                                                            >info</i>
                                                        </div>
                                                        <label class="switcher">
                                                            @php($value = $dataValues->where('key_name', $service_man_booking_edit[0]['key'])?->first()?->live_values ?? null)
                                                            <input class="switcher_input switch_alert"
                                                                   id="{{$service_man_booking_edit[0]['key']}}"
                                                                   type="checkbox"
                                                                   name="{{$service_man_booking_edit[0]['key']}}"
                                                                   value="1" {{$value ? 'checked' : ''}}
                                                                   data-id="{{$service_man_booking_edit[0]['key']}}"
                                                                   data-message="Want to change the status of {{ucfirst(translate($service_man_booking_edit[0]['key']))}}"
                                                            >
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                </div>

                                            </div>
                                            @can('business_update')
                                                <div class="d-flex gap-2 justify-content-end mt-4">
                                                    <button type="reset"
                                                            class="btn btn-secondary">{{translate('reset')}}
                                                    </button>
                                                    <button type="submit"
                                                            class="btn btn--primary">{{translate('update')}}
                                                    </button>
                                                </div>
                                            @endcan
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($webPage=='customers')
                        <div class="tab-content">
                            <div class="tab-pane fade {{$webPage=='customers'?'active show':''}}" id="business-info">
                                <form action="{{route('admin.business-settings.set-customer-setup')}}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="d-flex gap-2 align-items-center">
                                                <img width="20"
                                                     src="{{asset('assets/admin-module/img/icons/announcement.png')}}"
                                                     alt="">
                                                <h4>{{translate('customer_Setup')}}</h4>
                                            </div>
                                        </div>
                                        <div class="card-body p-30">
                                            <div class="row g-4 mb-30">
                                                @php($customerWallet = collect([ ['key' => 'customer_wallet','info_message' => 'Customer Wallet Status', 'title' => 'Customer Wallet'] ]))
                                                @php($addFundToWallet = collect([ ['key' => 'add_to_fund_wallet','info_message' => 'Customer Can Add Fund to Wallet', 'title' => 'Add Fund to Wallet'] ]))
                                                @php($customerLoyaltyPoint = collect([ ['key' => 'customer_loyalty_point','info_message' => 'Customer Loyalty Point', 'title' => 'Customer Loyalty Point'] ]))
                                                @php($customerReferralEarning = collect([ ['key' => 'customer_referral_earning','info_message' => 'Customer Referral Earning', 'title' => 'Customer Referral Earning'] ]))

                                                <div class="col-md-6 col-lg-4 col-12">
                                                    <div
                                                        class="border p-3 rounded d-flex justify-content-between gap-2 align-items-center">
                                                        <div class="d-flex gap-2 align-items-center">
                                                            <span
                                                                class="text-capitalize">{{translate($customerWallet[0]['title'])}}</span>
                                                            <i class="material-symbols-outlined cursor-pointer"
                                                               data-bs-toggle="tooltip"
                                                               data-bs-placement="top"
                                                               title="{{translate('When the option is enabled, customers will have a virtual wallet in their account. The balance can be used for payment when booking services')}}"
                                                            >info</i>
                                                        </div>
                                                        <label class="switcher">
                                                            @php($value = $dataValues->where('key_name', $customerWallet[0]['key'])?->first()?->live_values ?? null)
                                                            <input class="switcher_input switch_alert"
                                                                   id="{{$customerWallet[0]['key']}}"
                                                                   type="checkbox"
                                                                   name="{{$customerWallet[0]['key']}}"
                                                                   value="1" {{$value ? 'checked' : ''}}
                                                                   data-id="{{$customerWallet[0]['key']}}"
                                                                   data-message="Want to change the status of {{ucfirst(translate($customerWallet[0]['key']))}}"
                                                            >
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-lg-4 col-12">
                                                    <div
                                                        class="border p-3 rounded d-flex justify-content-between gap-2 align-items-center">
                                                        <div class="d-flex gap-2 align-items-center">
                                                            <span
                                                                class="text-capitalize">{{translate($addFundToWallet[0]['title'])}}</span>
                                                            <i class="material-symbols-outlined cursor-pointer"
                                                               data-bs-toggle="tooltip"
                                                               data-bs-placement="top"
                                                               title="{{translate('By allowing this customer can add balance to their wallet using - available digital payment methods')}}"
                                                            >info</i>
                                                        </div>
                                                        <label class="switcher">
                                                            @php($value = $dataValues->where('key_name', $addFundToWallet[0]['key'])?->first()?->live_values ?? null)
                                                            <input class="switcher_input switch_alert"
                                                                   id="{{$addFundToWallet[0]['key']}}"
                                                                   type="checkbox"
                                                                   name="{{$addFundToWallet[0]['key']}}"
                                                                   value="1" {{$value ? 'checked' : ''}}
                                                                   data-id="{{$addFundToWallet[0]['key']}}"
                                                                   data-message="Want to change the status of {{ucfirst(translate($addFundToWallet[0]['key']))}}"
                                                            >
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-lg-4 col-12">
                                                    <div
                                                        class="border p-3 rounded d-flex justify-content-between gap-2 align-items-center">
                                                        <div class="d-flex gap-2 align-items-center">
                                                            <span
                                                                class="text-capitalize">{{translate($customerLoyaltyPoint[0]['title'])}}</span>
                                                            <i class="material-symbols-outlined cursor-pointer"
                                                               data-bs-toggle="tooltip"
                                                               data-bs-placement="top"
                                                               title="{{translate('By allowing this option, customers can earn points for each booking as part of a loyalty program')}}"
                                                            >info</i>
                                                        </div>
                                                        <label class="switcher">
                                                            @php($value = $dataValues->where('key_name', $customerLoyaltyPoint[0]['key'])?->first()?->live_values ?? null)
                                                            <input class="switcher_input switch_alert"
                                                                   id="{{$customerLoyaltyPoint[0]['key']}}"
                                                                   type="checkbox"
                                                                   name="{{$customerLoyaltyPoint[0]['key']}}"
                                                                   value="1" {{$value ? 'checked' : ''}}
                                                                   data-id="{{$customerLoyaltyPoint[0]['key']}}"
                                                                   data-message="Want to change the status of {{ucfirst(translate($customerLoyaltyPoint[0]['key']))}}"
                                                            >
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-lg-4 col-12">
                                                    <div
                                                        class="border p-3 rounded d-flex justify-content-between gap-2 align-items-center">
                                                        <div class="d-flex gap-2 align-items-center">
                                                            <span
                                                                class="text-capitalize">{{translate($customerReferralEarning[0]['title'])}}</span>
                                                            <i class="material-symbols-outlined cursor-pointer"
                                                               data-bs-toggle="tooltip"
                                                               data-bs-placement="top"
                                                               title="{{translate('Through this, customers will receive a referral code to share with friends. They\'ll earn points when their friends use it during signup and upon completing their first booking.')}}"
                                                            >info</i>
                                                        </div>
                                                        <label class="switcher">
                                                            @php($value = $dataValues->where('key_name', $customerReferralEarning[0]['key'])?->first()?->live_values ?? null)
                                                            <input class="switcher_input switch_alert"
                                                                   id="{{$customerReferralEarning[0]['key']}}"
                                                                   type="checkbox"
                                                                   name="{{$customerReferralEarning[0]['key']}}"
                                                                   value="1" {{$value ? 'checked' : ''}}
                                                                   data-id="{{$customerReferralEarning[0]['key']}}"
                                                                   data-message="Want to change the status of {{ucfirst(translate($customerReferralEarning[0]['key']))}}"
                                                            >
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card mt-3">
                                        <div class="card-header">
                                            <h4 class="page-title d-flex align-items-center gap-2">
                                                <span class="material-symbols-outlined text-dark">settings</span>
                                                {{translate('Customer Loyalty Point Settings')}}
                                                <i class="material-symbols-outlined cursor-pointer"
                                                   data-bs-toggle="tooltip"
                                                   title="{{translate('tooltip on top')}}">info</i>
                                            </h4>
                                        </div>
                                        <div class="card-body p-30">
                                            <div class="row g-4">
                                                <div class="col-md-6 col-lg-4">
                                                    @php($value=$dataValues->where('key_name','loyalty_point_value_per_currency_unit')->first())
                                                    <div class="form-floating">
                                                        <input type="number" class="form-control spin-none"
                                                               name="loyalty_point_value_per_currency_unit" step="any"
                                                               min="1" value="{{$value->live_values??''}}">
                                                        <label class="mb-1">
                                                            {{translate('Equivalent Point to 1 Unit ')}}
                                                            {{ "(" . currency_symbol() . ")" }}
                                                        </label>
                                                        <i class="material-symbols-outlined cursor-pointer"
                                                           data-bs-toggle="tooltip"
                                                           title="{{translate('tooltip on top')}}">info</i>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-lg-4">
                                                    @php($value=$dataValues->where('key_name','loyalty_point_percentage_per_booking')->first())
                                                    <div class="form-floating">
                                                        <input type="number" class="form-control spin-none"
                                                               name="loyalty_point_percentage_per_booking"
                                                               min="1" max="100" step="any"
                                                               value="{{$value->live_values??''}}">
                                                        <label class="mb-1">
                                                            <span>{{translate('Point Earn on Each Bookings ')}}(%)</span>
                                                        </label>
                                                        <i class="material-symbols-outlined info-transform-fix"
                                                           data-bs-toggle="tooltip"
                                                           data-bs-placement="top"
                                                           title="{{translate('On every booking this percent of amount will be added as loyalty point on customer account')}}"
                                                        >info</i>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-lg-4">
                                                    @php($value=$dataValues->where('key_name','min_loyalty_point_to_transfer')->first())
                                                    <div class="form-floating">
                                                        <input type="number" class="form-control spin-none"
                                                               name="min_loyalty_point_to_transfer" step="any"
                                                               min="1" value="{{$value->live_values??''}}">
                                                        <label
                                                            class="mb-1">{{translate('Minimum Point Required To Convert')}}</label>
                                                        <i class="material-symbols-outlined cursor-pointer"
                                                           data-bs-toggle="tooltip"
                                                           title="{{translate('tooltip on top')}}">info</i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card mt-3">
                                        <div class="card-header">
                                            <h4 class="page-title d-flex align-items-center gap-2">
                                                <span class="material-symbols-outlined text-dark">settings</span>
                                                {{translate('Customer_Referrer_Settings')}}
                                                {{ "(" . currency_symbol() . ")" }}
                                                <i class="material-symbols-outlined cursor-pointer"
                                                   data-bs-toggle="tooltip"
                                                   title="{{translate('tooltip on top')}}">info</i>
                                            </h4>
                                        </div>
                                        <div class="card-body p-30">
                                            <div class="row g-4">
                                                <div class="col-md-6">
                                                    @php($value=$dataValues->where('key_name','referral_value_per_currency_unit')->first())
                                                    <div class="form-floating">
                                                        <input type="number" class="form-control spin-none"
                                                               name="referral_value_per_currency_unit" step="any"
                                                               min="1" value="{{$value->live_values??''}}">
                                                        <label class="mb-1">
                                                            {{translate('Earnings To Each Referral')}}
                                                            {{ "(" . currency_symbol() . ")" }}
                                                        </label>
                                                        <i class="material-symbols-outlined cursor-pointer"
                                                           data-bs-toggle="tooltip"
                                                           title="{{translate('tooltip on top')}}">info</i>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="border p-3 rounded d-flex justify-content-between">
                                                        @php($newUserDiscount=$dataValues->where('key_name','referral_based_new_user_discount')->first())
                                                        <div class="d-flex gap-2 align-items-center">
                                                            <span>{{translate('Referral-Based New User Discount Settings')}}</span>
                                                            <i class="material-symbols-outlined cursor-pointer"
                                                               data-bs-toggle="tooltip"
                                                               title="{{translate('Configure discounts for newly registered users who sign up with a referral code. Customize the discount type and amount to incentivize referrals and encourage user engagement.')}}"
                                                            >info</i>
                                                        </div>
                                                        <label class="switcher">
                                                            <input class="switcher_input" type="checkbox"
                                                                   name="referral_based_new_user_discount"
                                                                   id="user_discount_switch"
                                                                   value="1" {{$newUserDiscount?->live_values ? 'checked' : ''}}>
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="rounded shadow p-4 mt-4" id="user_discount_section">
                                                <div class="row align-items-center gy-4">
                                                    @php($referralDiscountType=$dataValues->where('key_name','referral_discount_type')->first())
                                                    <div class="col-md-4 col-xl-2">
                                                        <div class="d-flex flex-wrap flex-sm-nowrap gap-2">
                                                            <select class="form-select" name="referral_discount_type"
                                                                    id="referral_discount_type">
                                                                <option
                                                                    value="flat" {{$referralDiscountType?->live_values == 'flat' ? 'selected' : ''}}>{{translate('Flat')}}</option>
                                                                <option
                                                                    value="percentage" {{$referralDiscountType?->live_values == 'percentage' ? 'selected' : ''}}>{{translate('Percentage')}}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-8 col-xl-4">
                                                        @php($discountAmount=$dataValues->where('key_name','referral_discount_amount')->first())
                                                        <div class="d-flex flex-wrap flex-sm-nowrap gap-2">
                                                            <div class="form-floating flex-grow-1">
                                                                <input class="form-control remove-spin discount_amount"
                                                                       type="number" name="referral_discount_amount"
                                                                       id="discount_amount"
                                                                       value="{{$discountAmount?->live_values ?? 0}}"
                                                                       min="1" max="100" step="any" required>
                                                                <label
                                                                    id="discount_amount__label">{{translate('Discount_Amount')}}
                                                                    *</label>
                                                                <i class="material-symbols-outlined cursor-pointer"
                                                                   data-bs-toggle="tooltip"
                                                                   data-bs-placement="top"
                                                                   title="{{translate('Enter the discount value for referral-based new user registrations.')}}"
                                                                >info</i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 col-xl-2">
                                                        @php($validityType = $dataValues->where('key_name','referral_discount_validity_type')->first())
                                                        <div class="d-flex flex-wrap flex-sm-nowrap gap-2">
                                                            <select class="form-select"
                                                                    name="referral_discount_validity_type"
                                                                    id="referral_discount_validity_type">
                                                                <option
                                                                    value="day" {{$validityType?->live_values == 'day' ? 'selected' : ''}}>{{translate('Day')}}</option>
                                                                <option
                                                                    value="month" {{$validityType?->live_values == 'month' ? 'selected' : ''}}>{{translate('Month')}}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-8 col-xl-4">
                                                        <div class="d-flex flex-wrap flex-sm-nowrap gap-2">
                                                            @php($validity = $dataValues->where('key_name','referral_discount_validity')->first())
                                                            <div class="form-floating flex-grow-1">
                                                                <input class="form-control remove-spin" type="number"
                                                                       name="referral_discount_validity"
                                                                       id="referral_discount_validity"
                                                                       value="{{$validity?->live_values ?? 0}}" max="12"
                                                                       required min="1">
                                                                <label>{{translate('validity')}} *</label>
                                                                <i class="material-symbols-outlined cursor-pointer"
                                                                   data-bs-toggle="tooltip"
                                                                   data-bs-placement="top"
                                                                   title="{{translate('Set how long the discount remains active after registration.')}}"
                                                                >info</i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @can('business_update')
                                        <div class="d-flex gap-2 justify-content-end mt-4">
                                            <button type="reset" class="btn btn-secondary">{{translate('reset')}}
                                            </button>
                                            <button type="submit" class="btn btn--primary">{{translate('save')}}
                                            </button>
                                        </div>
                                    @endcan
                                </form>
                            </div>
                        </div>
                </div>
            </div>
            @endif

            @if($webPage=='providers')
                <div class="tab-content">
                    <div class="tab-pane fade {{$webPage=='providers'?'active show':''}}"
                         id="business-info">
                        <div class="card">
                            <div class="card-body p-30">
                                <form action="{{route('admin.business-settings.set-provider-setup')}}"
                                      method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="row g-4">
                                        @php($providerCanCancelBooking = collect([ ['key' => 'provider_can_cancel_booking','info_message' => 'If enabled, providers can cancel a booking even after it has been placed.', 'title' => 'Can Cancel Booking'] ]))
                                        @php($providerCanEditBooking = collect([ ['key' => 'provider_can_edit_booking','info_message' => 'If enabled, providers can edit a booking request after it has been placed', 'title' => 'Can Edit Booking'] ]))
                                        @php($providerSelfRegistration = collect([ ['key' => 'provider_self_registration','info_message' => 'If enabled, providers can do self-registration from the admin landing page, provider panel & app, and customer website & app.', 'title' => 'Provider Self Registration'] ]))
                                        @php($providerSelfDelete = collect([ ['key' => 'provider_self_delete','info_message' => 'If enabled, provider can delete account', 'title' => 'Provider Self Delete'] ]))
                                        @php($suspendOnExceedCashLimit = collect([ ['key' => 'suspend_on_exceed_cash_limit_provider','info_message' => 'If enabled, the provider will be automatically suspended by the system when their ‘Cash in Hand’ limit is exceeded.', 'title' => 'Suspend on Exceed Cash Limit'] ]))
                                        @php($minPaybleAmount = collect([ ['key' => 'min_payable_amount','info_message' => 'Provider must have to pay greater than or equal to this amount', 'title' => 'Minimum payable amount'] ]))
                                        @php($providerCommision = collect([ ['key' => 'provider_commision','info_message' => 'If enabled, providers have to give a certain percentage of commission to admin for every booking request', 'title' => 'Commision Base'] ]))
                                        @php($providerSubscription = collect([ ['key' => 'provider_subscription','info_message' => 'If enabled, providers have to pay a certain amount in every month / year to admin as subscription fee', 'title' => 'Subscription Base'] ]))
                                        @php($providerReviewReply = collect([ ['key' => 'provider_can_reply_review','info_message' => 'If enabled, providers have to review reply', 'title' => 'Provider can reply review'] ]))

                                        <div class="col-md-6 col-12 mb-30">
                                            <div class="border p-3 rounded d-flex justify-content-between">
                                                <div>
                                                            <span
                                                                class="text-capitalize">{{translate($providerCanCancelBooking[0]['title'])}}</span>
                                                    <i class="material-icons px-1" data-bs-toggle="tooltip"
                                                       data-bs-placement="top"
                                                       title="{{translate($providerCanCancelBooking[0]['info_message'] ?? '')}}"
                                                    >info</i>
                                                </div>
                                                <label class="switcher">
                                                    @php($value = $dataValues->where('key_name', $providerCanCancelBooking[0]['key'])?->first()?->live_values ?? null)
                                                    <input class="switcher_input switch_alert"
                                                           id="{{$providerCanCancelBooking[0]['key']}}"
                                                           type="checkbox"
                                                           name="{{$providerCanCancelBooking[0]['key']}}"
                                                           value="1" {{$value ? 'checked' : ''}}
                                                           data-id="{{$providerCanCancelBooking[0]['key']}}"
                                                           data-message="Want to change the status of {{ucfirst(translate($providerCanCancelBooking[0]['key']))}}"
                                                    >
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-6 col-12 mb-30">
                                            <div class="border p-3 rounded d-flex justify-content-between">
                                                <div>
                                                            <span
                                                                class="text-capitalize">{{translate($providerCanEditBooking[0]['title'])}}</span>
                                                    <i class="material-icons px-1" data-bs-toggle="tooltip"
                                                       data-bs-placement="top"
                                                       title="{{translate($providerCanEditBooking[0]['info_message'] ?? '')}}"
                                                    >info</i>
                                                </div>
                                                <label class="switcher">
                                                    @php($value = $dataValues->where('key_name', $providerCanEditBooking[0]['key'])?->first()?->live_values ?? null)
                                                    <input class="switcher_input switch_alert"
                                                           id="{{$providerCanEditBooking[0]['key']}}"
                                                           type="checkbox"
                                                           name="{{$providerCanEditBooking[0]['key']}}"
                                                           value="1" {{$value ? 'checked' : ''}}
                                                           data-id="{{$providerCanEditBooking[0]['key']}}"
                                                           data-message="Want to change the status of {{ucfirst(translate($providerCanEditBooking[0]['key']))}}"
                                                    >
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-6 col-12 mb-30">
                                            <div class="border p-3 rounded d-flex justify-content-between">
                                                <div>
                                                            <span
                                                                class="text-capitalize">{{translate($suspendOnExceedCashLimit[0]['title'])}}</span>
                                                    <i class="material-icons px-1" data-bs-toggle="tooltip"
                                                       data-bs-placement="top"
                                                       title="{{translate($suspendOnExceedCashLimit[0]['info_message'] ?? '')}}"
                                                    >info</i>
                                                </div>
                                                <label class="switcher">
                                                    @php($value = $dataValues->where('key_name', $suspendOnExceedCashLimit[0]['key'])?->first()?->live_values ?? null)
                                                    <input class="switcher_input switch_alert"
                                                           id="{{$suspendOnExceedCashLimit[0]['key']}}"
                                                           type="checkbox"
                                                           name="{{$suspendOnExceedCashLimit[0]['key']}}"
                                                           value="1" {{$value ? 'checked' : ''}}
                                                           data-id="{{$suspendOnExceedCashLimit[0]['key']}}"
                                                           data-message="Want to change the status of {{ucfirst(translate($suspendOnExceedCashLimit[0]['key']))}}"
                                                    >
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-6 col-12 mb-30">
                                            @php($maxCashInHand=$dataValues->where('key_name','max_cash_in_hand_limit_provider')->first())
                                            <div class="form-floating">
                                                <input type="number" class="form-control"
                                                       name="max_cash_in_hand_limit_provider"
                                                       min="0" step="any" value="{{$maxCashInHand->live_values??''}}">
                                                <label class="mb-1">{{translate('Maximum Cash in Hand Limit')}}
                                                    ({{currency_symbol()}})</label>
                                                <i class="material-symbols-outlined" data-bs-toggle="tooltip"
                                                   data-bs-placement="top"
                                                   title="{{translate('Define the maximum amount of ‘Cash in Hand’ a provider is allowed to keep. If the maximum limit is exceeded, the provider will be suspended and will not receive any service requests. ')}}"
                                                >info</i>
                                            </div>
                                        </div>

                                        <div class="col-md-6 col-12 mb-30">
                                            <div class="border p-3 rounded d-flex justify-content-between">
                                                <div>
                                                            <span
                                                                class="text-capitalize">{{translate($providerSelfRegistration[0]['title'])}}</span>
                                                    <i class="material-icons px-1" data-bs-toggle="tooltip"
                                                       data-bs-placement="top"
                                                       title="{{translate($providerSelfRegistration[0]['info_message'] ?? '')}}"
                                                    >info</i>
                                                </div>
                                                <label class="switcher">
                                                    @php($value = $dataValues->where('key_name', $providerSelfRegistration[0]['key'])?->first()?->live_values ?? null)
                                                    <input class="switcher_input switch_alert"
                                                           id="{{$providerSelfRegistration[0]['key']}}"
                                                           type="checkbox"
                                                           name="{{$providerSelfRegistration[0]['key']}}"
                                                           value="1" {{$value ? 'checked' : ''}}
                                                           data-id="{{$providerSelfRegistration[0]['key']}}"
                                                           data-message="Want to change the status of {{ucfirst(translate($providerSelfRegistration[0]['key']))}}"
                                                    >
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-6 col-12 mb-30">
                                            <div class="border p-3 rounded d-flex justify-content-between">
                                                <div>
                                                            <span
                                                                class="text-capitalize">{{translate($providerSelfDelete[0]['title'])}}</span>
                                                    <i class="material-icons px-1" data-bs-toggle="tooltip"
                                                       data-bs-placement="top"
                                                       title="{{translate($providerSelfDelete[0]['info_message'] ?? '')}}"
                                                    >info</i>
                                                </div>
                                                <label class="switcher">
                                                    @php($value = $dataValues->where('key_name', $providerSelfDelete[0]['key'])?->first()?->live_values ?? null)
                                                    <input class="switcher_input switch_alert"
                                                           id="{{$providerSelfDelete[0]['key']}}"
                                                           type="checkbox"
                                                           name="{{$providerSelfDelete[0]['key']}}"
                                                           value="1" {{$value ? 'checked' : ''}}
                                                           data-id="{{$providerSelfDelete[0]['key']}}"
                                                           data-message="Want to change the status of {{ucfirst(translate($providerSelfDelete[0]['key']))}}"
                                                    >
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-6 col-12 mb-30">
                                            <div class="border p-3 rounded d-flex justify-content-between">
                                                <div>
                                                    <span class="text-capitalize">{{translate($providerCommision[0]['title'])}}</span>
                                                    <i class="material-icons px-1" data-bs-toggle="tooltip"
                                                       data-bs-placement="top"
                                                       title="{{translate($providerCommision[0]['info_message'] ?? '')}}"
                                                    >info</i>
                                                </div>
                                                <label class="switcher">
                                                    @php($value = $dataValues->where('key_name', $providerCommision[0]['key'])?->first()?->live_values ?? null)
                                                    <input class="switcher_input {{ $value ? '' : 'switch_alert' }}"
                                                           @if($value) data-bs-toggle="modal" data-bs-target="#commissionToSubscription" @endif
                                                           id="{{$providerCommision[0]['key']}}"
                                                           type="checkbox"
                                                           name="{{$providerCommision[0]['key']}}"
                                                           value="1" {{$value ? 'checked' : ''}}
                                                           data-id="{{$providerCommision[0]['key']}}"
                                                           data-message="{{ translate('Want to turn on provider commission') }}?"
                                                    >
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-6 col-12 mb-30">
                                            <div class="border p-3 rounded d-flex justify-content-between">
                                                <div>
                                                    <span class="text-capitalize">{{translate($providerSubscription[0]['title'])}}</span>
                                                    <i class="material-icons px-1" data-bs-toggle="tooltip"
                                                       data-bs-placement="top"
                                                       title="{{translate($providerSubscription[0]['info_message'] ?? '')}}"
                                                    >info</i>
                                                </div>
                                                <label class="switcher">
                                                    @php($value = $dataValues->where('key_name', $providerSubscription[0]['key'])?->first()?->live_values ?? null)
                                                    <input class="switcher_input {{ $value ? '' : 'switch_alert' }}"
                                                           @if($value) data-bs-toggle="modal" data-bs-target="#subscriptionToCommission" @endif
                                                           id="{{$providerSubscription[0]['key']}}"
                                                           type="checkbox"
                                                           name="{{$providerSubscription[0]['key']}}"
                                                           value="1" {{$value ? 'checked' : ''}}
                                                           data-id="{{$providerSubscription[0]['key']}}"
                                                           data-message="{{ucfirst(translate($providerSubscription[0]['key']))}}">
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12 mb-30">
                                            <div class="border p-3 rounded d-flex justify-content-between">
                                                <div>
                                                    <span class="text-capitalize">{{translate($providerReviewReply[0]['title'])}}</span>
                                                    <i class="material-icons px-1" data-bs-toggle="tooltip"
                                                       data-bs-placement="top"
                                                       title="{{translate($providerReviewReply[0]['info_message'] ?? '')}}"
                                                    >info</i>
                                                </div>
                                                <label class="switcher">
                                                    @php($value = $dataValues->where('key_name', $providerReviewReply[0]['key'])?->first()?->live_values ?? null)
                                                    <input class="switcher_input {{ $value ? '' : 'switch_alert' }}"
                                                           id="{{$providerReviewReply[0]['key']}}"
                                                           type="checkbox"
                                                           name="{{$providerReviewReply[0]['key']}}"
                                                           value="1" {{$value ? 'checked' : ''}}
                                                           data-id="{{$providerReviewReply[0]['key']}}"
                                                           data-message="{{ucfirst(translate($providerReviewReply[0]['key']))}}">
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-6 col-12 mb-30">
                                            @php($minPayable=$dataValues->where('key_name','min_payable_amount')->first())
                                            <div class="form-floating">
                                                <input type="number" class="form-control"
                                                       name="min_payable_amount"
                                                       min="0.1" step="any" value="{{$minPayable->live_values ?? 0}}">
                                                <label class="mb-1">{{translate('Minimum Payable Amount')}}
                                                    ({{currency_symbol()}})</label>
                                                <i class="material-symbols-outlined" data-bs-toggle="tooltip"
                                                   data-bs-placement="top"
                                                   title="{{translate($minPaybleAmount[0]['info_message'] ?? '')}}"
                                                >info</i>
                                            </div>
                                        </div>

                                    </div>
                                    @can('business_update')
                                        <div class="d-flex gap-2 justify-content-end mt-4">
                                            <button type="reset" class="btn btn-secondary">{{translate('reset')}}
                                            </button>
                                            <button type="submit" class="btn btn--primary">{{translate('update')}}
                                            </button>
                                        </div>
                                    @endcan
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    </div>
    </div>

    <div class="modal fade" id="subscriptionToCommission" tabindex="-1" aria-labelledby="subscriptionToCommissionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-30">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="d-flex flex-column gap-2 align-items-center text-center">
                        <img class="mb-3" src="{{asset('assets/admin-module')}}/img/ad_delete.svg" alt="">
                        <h3 class="mb-2">{{ translate('Switch commission base')}}</h3>
                        <p class="old-subscription-name" id="old_subscription_name">{{ translate('If disabled Subscription, All subscriber moved to commission and providers have to give a certain percentage of commission to admin for every booking request')}}</p>
                        <form action="{{ route('admin.subscription.package.subscription-to-commission') }}" method="post">
                            @csrf
                            <div class="choose-option">
                                <div class="d-flex gap-3 justify-content-center flex-wrap">
                                    <?php
                                    $commissionStatus = (int)((business_config('provider_commision', 'provider_config'))?->live_values??null);
                                    ?>
                                    @if($commissionStatus)
                                    <button type="submit" class="btn btn--primary text-capitalize">{{ translate('Switch & Turn Off The Status')}}</button>
                                    @else
                                        <label class="test-start p-3 text-danger">{{ translate('At first commission base system on') }}</label>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="commissionToSubscription" tabindex="-1" aria-labelledby="commissionToSubscriptionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-30">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="d-flex flex-column gap-2 align-items-center text-center">
                        <img class="mb-3" src="{{asset('assets/admin-module')}}/img/ad_delete.svg" alt="">
                        <h3 class="mb-2">{{ translate('Want to switch existing business plan')}}?</h3>
                        <p class="old-subscription-name" id="old_subscription_name"></p>
                        <form class="w-100" action="{{ route('admin.subscription.package.commission-to-subscription') }}" method="post">
                            @csrf
                            <input type="hidden" name="old_subscription" id="old_subscription" value="">
                            <div class="choose-option text-start">
                                <?php
                                    $subscriptionPackage = Modules\BusinessSettingsModule\Entities\SubscriptionPackage::ofStatus(1)->get();
                                    $subscriptionStatus = (int)((business_config('provider_subscription', 'provider_config'))?->live_values);
                                ?>
                                @if($subscriptionStatus)
                                    <label class="test-start my-2">{{ translate('Business Plan') }}</label>
                                    <select class="form-select mb-3 js-select-modal" name="subscription_id">
                                        <option selected>{{ translate('Select Plan') }}</option>
                                        @foreach($subscriptionPackage as $package)
                                            <option value="{{ $package->id }}">{{ $package->name}}</option>
                                        @endforeach
                                    </select>
                                    <div class="d-flex gap-3 justify-content-center flex-wrap m-3">
                                        @csrf
                                        <button type="submit" class="btn btn--primary text-capitalize">{{ translate('Switch & Turn Off The Status')}}</button>
                                    </div>
                                @else
                                    <label class="test-start p-3">{{ translate('At first subscription base system on') }}</label>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="maintenance-mode-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="mb-0">
                        <i class="tio-notifications-alert mr-1"></i>
                        {{translate('System Maintenance')}}
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <form method="post" action="{{route('admin.business-settings.maintenance-mode-setup')}}">
                        <?php
                            $selectedMaintenanceSystem      = ((business_config('maintenance_system_setup', 'maintenance_mode'))?->live_values) ?? [];
                            $selectedMaintenanceDuration    = ((business_config('maintenance_duration_setup', 'maintenance_mode'))?->live_values) ?? [];
                            $selectedMaintenanceMessage     = ((business_config('maintenance_message_setup', 'maintenance_mode'))?->live_values) ?? [];
                            $maintenanceMode                = (int)((business_config('maintenance_mode', 'maintenance_mode'))?->live_values) ?? 0;
                        ?>
                    <div class="modal-body">
                        @csrf
                        <div class="d-flex flex-column gap-4">
                            <div class="row">
                                <div class="col-8">
                                    <p>*{{ translate('By turning on maintenance mode Control your all system & function') }}</p>
                                </div>
                                <div class="col-4">
                                    <div class="d-flex justify-content-between align-items-center border rounded mb-2 px-3 py-2">
                                        <h5 class="mb-0">{{translate('maintenance_mode')}}</h5>

                                        <label class="switcher ml-auto mb-0">
                                            <input type="checkbox" class="switcher_input" name="maintenance_mode"
                                                {{ $maintenanceMode ?'checked':''}}>
                                            <span class="switcher_control"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xl-4">
                                    <h5 class="mb-2">{{ translate('Select System') }}</h5>
                                    <p>{{ translate('Select the systems you want to temporarily deactivate for maintenance') }}</p>
                                </div>
                                <div class="col-xl-8">
                                    <div class="border p-3">
                                        <div class="d-flex flex-wrap gap-x-30">
                                            <div class="form-check form--check">
                                                <input class="form-check-input system-checkbox" name="all_system" type="checkbox"
                                                       {{ in_array('mobile_app', $selectedMaintenanceSystem) &&
                                                            in_array('web_app', $selectedMaintenanceSystem) &&
                                                            in_array('provider_panel', $selectedMaintenanceSystem) &&
                                                            in_array('provider_app', $selectedMaintenanceSystem) &&
                                                            in_array('serviceman_app', $selectedMaintenanceSystem) ? 'checked' :'' }}
                                                       id="allSystem">
                                                <label class="form-check-label" for="allSystem">{{ translate('All System') }}</label>
                                            </div>
                                            <div class="form-check form--check">
                                                <input class="form-check-input system-checkbox" name="mobile_app" type="checkbox"
                                                       {{ in_array('mobile_app', $selectedMaintenanceSystem) ? 'checked' :'' }}
                                                       id="mobileApp">
                                                <label class="form-check-label" for="mobileApp">{{ translate('Mobile App') }}</label>
                                            </div>
                                            <div class="form-check form--check">
                                                <input class="form-check-input system-checkbox" name="web_app" type="checkbox"
                                                       {{ in_array('web_app', $selectedMaintenanceSystem) ? 'checked' :'' }}
                                                       id="webApp">
                                                <label class="form-check-label" for="webApp">{{ translate('Web App') }}</label>
                                            </div>
                                            <div class="form-check form--check">
                                                <input class="form-check-input system-checkbox" name="provider_panel" type="checkbox"
                                                       {{ in_array('provider_panel', $selectedMaintenanceSystem) ? 'checked' :'' }}
                                                       id="providerPanel">
                                                <label class="form-check-label" for="providerPanel">{{ translate('Provider Panel') }}</label>
                                            </div>
                                            <div class="form-check form--check">
                                                <input class="form-check-input system-checkbox" name="provider_app" type="checkbox"
                                                       {{ in_array('provider_app', $selectedMaintenanceSystem) ? 'checked' :'' }}
                                                       id="providerApp">
                                                <label class="form-check-label" for="providerApp">{{ translate('Provider App') }}</label>
                                            </div>
                                            <div class="form-check form--check">
                                                <input class="form-check-input system-checkbox" name="serviceman_app" type="checkbox"
                                                       {{ in_array('serviceman_app', $selectedMaintenanceSystem) ? 'checked' :'' }}
                                                       id="servicemanApp">
                                                <label class="form-check-label" for="servicemanApp">{{ translate('Serviceman App') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xl-4">
                                    <h5 class="mb-2">{{ translate('Maintenance Date') }} & {{ translate('Time') }}</h5>
                                    <p>{{ translate('Choose the maintenance mode duration for your selected system.') }}</p>
                                </div>
                                <div class="col-xl-8">
                                    <div class="border p-3">
                                        <div class="d-flex flex-wrap gap-x-30">
                                            <div class="form-check form--check">
                                                <input class="form-check-input" type="radio" name="maintenance_duration"
                                                       {{ isset($selectedMaintenanceDuration['maintenance_duration']) && $selectedMaintenanceDuration['maintenance_duration'] == 'one_day' ? 'checked' : '' }}
                                                       value="one_day" id="one_day">
                                                <label class="form-check-label" for="one_day">{{ translate('For 24 Hours') }}</label>
                                            </div>
                                            <div class="form-check form--check">
                                                <input class="form-check-input" type="radio" name="maintenance_duration"
                                                       {{ isset($selectedMaintenanceDuration['maintenance_duration']) && $selectedMaintenanceDuration['maintenance_duration'] == 'one_week' ? 'checked' : '' }}
                                                       value="one_week" id="one_week">
                                                <label class="form-check-label" for="one_week">{{ translate('For 1 Week') }}</label>
                                            </div>
                                            <div class="form-check form--check">
                                                <input class="form-check-input" type="radio" name="maintenance_duration"
                                                       {{ isset($selectedMaintenanceDuration['maintenance_duration']) && $selectedMaintenanceDuration['maintenance_duration'] == 'until_change' ? 'checked' : '' }}
                                                       value="until_change" id="until_change">
                                                <label class="form-check-label" for="until_change">{{ translate('Until I change') }}</label>
                                            </div>
                                            <div class="form-check form--check">
                                                <input class="form-check-input" type="radio" name="maintenance_duration"
                                                       {{ isset($selectedMaintenanceDuration['maintenance_duration']) && $selectedMaintenanceDuration['maintenance_duration'] == 'customize' ? 'checked' : '' }}
                                                       value="customize" id="customize">
                                                <label class="form-check-label" for="customize">{{ translate('Customize') }}</label>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="datetime-local" class="form-control h-40 mt-3" name="start_date" id="startDate"
                                                        value="{{ old('start_date', $selectedMaintenanceDuration['start_date'] ?? '') }}" required>
                                                    <label>{{ translate('Start Date') }}</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="datetime-local" class="form-control h-40  mt-3" name="end_date" id="endDate"
                                                        value="{{ old('end_date', $selectedMaintenanceDuration['end_date'] ?? '') }}" required>
                                                    <label>{{ translate('End Date') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <small id="dateError" class="form-text text-danger" style="display: none;">{{ translate('Start date cannot be greater than end date.') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="advanceFeatureButtonDiv">
                            <div class="d-flex justify-content-center mt-3">
                                <a href="#" id="advanceFeatureToggle" class="d-block mb-3 maintenance-advance-feature-button text-decoration-underline">{{ translate('Advance Feature') }}</a>
                            </div>
                        </div>

                        <div class="row mt-4" id="advanceFeatureSection" style="display: none;">
                            <div class="col-xl-4">
                                <h5 class="mb-2">{{ translate('Maintenance Massage') }}</h5>
                                <p>{{ translate('Select & type what massage you want to see your selected system when maintenance mode is active.') }}</p>
                            </div>
                            <div class="col-xl-8">
                                <div class="border p-3">
                                    <div class="form-group">
                                        <label>{{ translate('Show Contact Info') }}</label>
                                        <div class="d-flex flex-wrap gap-5 mb-3">
                                            <div class="form-check form--check">
                                                <input class="form-check-input" type="checkbox" name="business_number"
                                                       {{ isset($selectedMaintenanceMessage['business_number']) && $selectedMaintenanceMessage['business_number'] == 1 ? 'checked' : '' }}
                                                       id="businessNumber">
                                                <label class="form-check-label" for="businessNumber">{{ translate('Business Number') }}</label>
                                            </div>
                                            <div class="form-check form--check">
                                                <input class="form-check-input" type="checkbox" name="business_email"
                                                       {{ isset($selectedMaintenanceMessage['business_email']) && $selectedMaintenanceMessage['business_email'] == 1 ? 'checked' : '' }}
                                                       id="businessEmail">
                                                <label class="form-check-label" for="businessEmail">{{ translate('Business Email') }}</label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-floating">
                                                <input type="text" class="form-control h-40" name="maintenance_message" placeholder="We're Working On Something Special!" maxlength="200" value="{{ $selectedMaintenanceMessage['maintenance_message'] ?? '' }}">
                                                <label>{{ translate('Message Title') }}</label>
                                            </div>
                                        </div>
                                        <div class="form-group mt-4">
                                            <div class="form-floating">
                                                <textarea class="form-control" name="message_body" maxlength="200" rows="3" placeholder="{{ translate('Message body') }}">{{ $selectedMaintenanceMessage['message_body'] ?? '' }}</textarea>
                                                <label>{{ translate('Message Body') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-center mt-4">
                                    <a href="#" id="seeLessToggle" class="d-block mb-3 maintenance-advance-feature-button text-decoration-underline">{{ translate('See Less') }}</a>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="btn--container justify-content-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
                            <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}" class="btn btn--primary demo_check">{{ translate('Save') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="currency-warning-modal">
        <div class="modal-dialog modal-dialog-centered status-warning-modal">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-20">
                        <div>
                            <div class="text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="76" height="76" viewBox="0 0 76 76" fill="none">
                                    <path d="M38 0.5C17.3 0.5 0.5 17.3 0.5 38C0.5 58.7 17.3 75.5 38 75.5C58.7 75.5 75.5 58.7 75.5 38C75.5 17.3 58.7 0.5 38 0.5ZM38 60.5C35.25 60.5 33 58.25 33 55.5C33 52.75 35.25 50.5 38 50.5C40.75 50.5 43 52.75 43 55.5C43 58.25 40.75 60.5 38 60.5ZM43.725 21.725L42.05 41.775C41.875 43.875 40.125 45.5 38 45.5C35.875 45.5 34.125 43.875 33.95 41.775L32.275 21.725C32 18.375 34.625 15.5 38 15.5C41.2 15.5 43.75 18.1 43.75 21.25C43.75 21.4 43.75 21.575 43.725 21.725Z" fill="#FF6174"/>
                                </svg>
                                <h5 class="modal-title"></h5>
                            </div>
                            <div class="text-center">
                                <h3 class="mb-4 mt-4"> {{ translate('Important_Alert !') }}</h3>
                                <div > <p>{{ translate('This currency is not supported by any of your current active digital payment gateways. Customer will not see the digital payment option & will not be able to pay digitally from website and apps') }}</h3></p></div>
                            </div>

                            <div class="text-center mb-4 mt-4" >
                                <a class="text-underline text-primary" href="{{ request()->is('admin/configuration/offline-payment/*') ? url('admin/configuration/offline-payment/list?web_page=payment_config&type=offline_payment') : url('admin/configuration/get-third-party-config?web_page=payment_config&type=digital_payment') }}"> {{ translate('View Payment gateway Settings') }}</a>
                            </div>
                        </div>

{{--                        <div class="btn--container justify-content-center">--}}
{{--                            <button data-bs-dismiss="modal" id="confirm-currency-change" class="btn btn-secondary min-w-120" >{{translate("Cancel")}}</button>--}}
{{--                            <button data-bs-dismiss="modal" type="button" class="btn btn--primary min-w-120">{{translate('Turn Off')}}</button>--}}

{{--                        </div>--}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @php($currencyCode = $dataValues->where('key_name', 'currency_code')->first()->live_values ?? 'USD')
@endsection

@push('script')
    <script src="{{asset('assets/admin-module')}}/plugins/select2/select2.min.js"></script>
    <script src="{{asset('assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.js"></script>
    <script src="{{asset('assets/admin-module')}}/plugins/dataTables/dataTables.select.min.js"></script>


    <script>
        "use strict";

        let selectedCurrency = "{{ $currencyCode ? $currencyCode : 'USD' }}";
        let currencyConfirmed = false;
        let updatingCurrency = false;

        $("#change_currency").change(function() {
            if (!updatingCurrency) check_currency($(this).val());
        });

        $("#confirm-currency-change").click(function() {
            currencyConfirmed = true;
            update_currency(selectedCurrency);
            $('#currency-warning-modal').modal('hide');
        });

        function check_currency(currency) {
            $.ajax({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                url: "{{route('admin.business-settings.ajax-currency-change')}}",
                method: 'GET',
                data: { currency: currency },
                success: function(response) {
                    if (response.data) {
                        $('#currency-warning-modal').modal('show');
                    } else {
                        update_currency(currency);
                    }
                }
            });
        }

        function update_currency(currency) {
            if (currencyConfirmed) {
                updatingCurrency = true;
                $("#change_currency").val(currency).trigger('change');
                updatingCurrency = false;
                currencyConfirmed = false;
            }
        }

        document.getElementById('subscriptionToCommission').addEventListener('hidden.bs.modal', function () {
            location.reload();
        });
        document.getElementById('commissionToSubscription').addEventListener('hidden.bs.modal', function () {
            location.reload();
        });

        $('#business-info-update-form').on('submit', function (event) {
            event.preventDefault();

            let form = $('#business-info-update-form')[0];
            let formData = new FormData(form);

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
                    toastr.success('{{translate('successfully_updated')}}');
                },
                error: function (jqXHR, exception) {
                    if (jqXHR.responseJSON && jqXHR.responseJSON.errors && jqXHR.responseJSON.errors.length > 0) {
                        var errorMessages = jqXHR.responseJSON.errors.map(function (error) {
                            return error.message;
                        });

                        errorMessages.forEach(function (errorMessage) {
                            toastr.error(errorMessage);
                        });
                    } else {
                        toastr.error("An error occurred.");
                    }
                }
            });
        });

        $('#bidding-system-update-form').on('submit', function (event) {
            event.preventDefault();

            let form = $('#bidding-system-update-form')[0];
            let formData = new FormData(form);

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('admin.business-settings.set-bidding-system')}}",
                data: formData,
                processData: false,
                contentType: false,
                type: 'POST',
                success: function (response) {
                    toastr.success('{{translate('successfully_updated')}}');
                },
                error: function (jqXHR, exception) {
                    if (jqXHR.responseJSON && jqXHR.responseJSON.errors && jqXHR.responseJSON.errors.length > 0) {
                        var errorMessages = jqXHR.responseJSON.errors.map(function (error) {
                            return error.message;
                        });

                        errorMessages.forEach(function (errorMessage) {
                            toastr.error(errorMessage);
                        });
                    } else {
                        toastr.error("An error occurred.");
                    }
                }
            });
        });

        $('#booking-system-update-form').on('submit', function (event) {
            event.preventDefault();

            let form = $('#booking-system-update-form')[0];
            let formData = new FormData(form);

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('admin.business-settings.set-booking-setup')}}",
                data: formData,
                processData: false,
                contentType: false,
                type: 'POST',
                success: function (response) {
                    toastr.success('{{translate('successfully_updated')}}');
                },
                error: function (jqXHR, exception) {
                    if (jqXHR.responseJSON && jqXHR.responseJSON.errors && jqXHR.responseJSON.errors.length > 0) {
                        var errorMessages = jqXHR.responseJSON.errors.map(function (error) {
                            return error.message;
                        });

                        errorMessages.forEach(function (errorMessage) {
                            toastr.error(errorMessage);
                        });
                    } else {
                        toastr.error("An error occurred.");
                    }
                }
            });
        });

        function update_action_status(key_name, value, settings_type, will_reload = false) {
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
                        url: "{{route('admin.business-settings.update-action-status')}}",
                        data: {
                            key: key_name,
                            value: value,
                            settings_type: settings_type,
                        },
                        type: 'put',
                        success: function (response) {
                            toastr.success('{{translate('successfully_updated')}}');

                            if (will_reload) {
                                setTimeout(() => {
                                    document.location.reload();
                                }, 3000);
                            }
                        },
                        error: function () {

                        }
                    });
                }
            })
        }

        $(document).ready(function () {
            $('.js-select').select2();
        });

        $('.js-select-modal').select2({
            dropdownParent: $('#commissionToSubscription')
        });

        $(window).on('load', function () {
            $("#admin-select__discount, #provider-select__discount").on('click', function (e) {
                $("#bearer-section__discount").addClass('d-none');
            })

            $("#both-select__discount").on('click', function (e) {
                $("#bearer-section__discount").removeClass('d-none');
            })

            $("#admin_percentage__discount").keyup(function (e) {
                if (this.value >= 0 && this.value <= 100) {
                    $("#provider_percentage__discount").val((100 - this.value));
                }
            });

            $("#provider_percentage__discount").keyup(function (e) {
                if (this.value >= 0 && this.value <= 100) {
                    $("#admin_percentage__discount").val((100 - this.value));
                }
            });

            $("#admin-select__campaign, #provider-select__campaign").on('click', function (e) {
                $("#bearer-section__campaign").addClass('d-none');
            })

            $("#both-select__campaign").on('click', function (e) {
                $("#bearer-section__campaign").removeClass('d-none');
            })

            $("#admin_percentage__campaign").keyup(function (e) {
                if (this.value >= 0 && this.value <= 100) {
                    $("#provider_percentage__campaign").val((100 - this.value));
                }
            });

            $("#provider_percentage__campaign").keyup(function (e) {
                if (this.value >= 0 && this.value <= 100) {
                    $("#admin_percentage__campaign").val((100 - this.value));
                }
            });

            $("#admin-select__coupon, #provider-select__coupon").on('click', function (e) {
                $("#bearer-section__coupon").addClass('d-none');
            })

            $("#both-select__coupon").on('click', function (e) {
                $("#bearer-section__coupon").removeClass('d-none');
            })

            $("#admin_percentage__coupon").keyup(function (e) {
                if (this.value >= 0 && this.value <= 100) {
                    $("#provider_percentage__coupon").val((100 - this.value));
                }
            });

            $("#provider_percentage__coupon").keyup(function (e) {
                if (this.value >= 0 && this.value <= 100) {
                    $("#admin_percentage__coupon").val((100 - this.value));
                }
            });
        })

        function switch_alert(id, status, message) {
            Swal.fire({
                title: "{{translate('are_you_sure')}}?",
                text: message,
                type: 'warning',
                showDenyButton: true,
                showCancelButton: true,
                denyButtonColor: 'var(--c2)',
                confirmButtonColor: 'var(--c1)',
                confirmButtonText: 'Save',
                denyButtonText: `Don't save`,
            }).then((result) => {
                if (result.value) {
                } else {
                    if (status === 1) $(`#${id}`).prop('checked', false);
                    if (status === 0) $(`#${id}`).prop('checked', true);
                    Swal.fire('{{translate('Changes are not saved')}}', '', 'info')
                }
            })
        }
        $(document).ready(function ($) {
            $("#provider_commision").on('change', function () {
                if (!$(this).is(':checked') && !$("#provider_subscription").is(':checked')) {
                    $(this).prop('checked', true);
                }
            });
            $("#provider_subscription").on('change', function () {
                if (!$(this).is(':checked') && !$("#provider_commision").is(':checked')) {
                    $(this).prop('checked', true);
                }
            });
        });

    </script>
    <script>
        "use strict";

        @if(!$dataValues->where('key_name', 'schedule_booking')->where('settings_type', 'booking_setup')->first()?->live_values)
        $(document).ready(function () {
            $('#schedule_booking_section').hide();
        })
        @endif

        @if(!$dataValues->where('key_name', 'schedule_booking_time_restriction')->where('settings_type', 'booking_setup')->first()?->live_values)
        $(document).ready(function () {
            $('#schedule_booking_restriction').hide();
        })
        @endif

        @if(!$dataValues->where('key_name', 'referral_based_new_user_discount')->where('settings_type', 'customer_config')->first()?->live_values)
        $(document).ready(function () {
            $('#user_discount_section').hide();
        })
        @endif

        @if($dataValues->where('key_name', 'referral_discount_validity_type')->where('settings_type', 'customer_config')->first()?->live_values == 'day')
        $('#referral_discount_validity').removeAttr('max');
        @endif

        @if($dataValues->where('key_name', 'referral_discount_type')->where('settings_type', 'customer_config')->first()?->live_values == 'percentage')
        $('#discount_amount__label').html('{{translate('discount_percentage')}} (%)');
        @endif

        $('#referral_discount_type').on('change', function () {
            if ($(this).val() === 'flat') {
                $('#discount_amount').removeAttr('max');
                $('#discount_amount__label').html('{{translate('discount_amount')}} ({{currency_symbol()}})');
            } else if ($(this).val() === 'percentage') {
                $('#discount_amount').attr({"max": 100});
                $('#discount_amount__label').html('{{translate('discount_percentage')}} (%)');
            }
        });

        $('#referral_discount_validity_type').on('change', function () {
            if ($(this).val() === 'day') {
                $('#referral_discount_validity').removeAttr('max');
            } else if ($(this).val() === 'percentage') {
                $('#referral_discount_validity').attr('max');
            }
        });

        $('#schedule_booking_switch').on('change', function () {
            if ($(this).is(':checked') === true) {
                $('#schedule_booking_section').show();
            } else {
                $('#schedule_booking_section').hide();
            }

            const scheduleBookingStatus = $(this).is(':checked') === true ? 1 : 0;
            const instantBooking = $("#instant_booking").is(':checked') === true ? 1 : 0;

            if (scheduleBookingStatus === 0 && instantBooking === 0) {
                $("#instant_booking").prop('checked', true);
            }
        });

        $('#instant_booking').on('change', function () {
            const instantBooking = $(this).is(':checked') === true ? 1 : 0;
            const scheduleBookingStatus = $('schedule_booking_switch').is(':checked') === true ? 1 : 0;

            if (scheduleBookingStatus === 0 && instantBooking === 0) {
                $("#schedule_booking_switch").prop('checked', true);

                $('#schedule_booking_section').show();
            }
        });

        $('#schedule_booking_checkbox').on('change', function () {
            if ($(this).is(':checked') === true) {
                $('#schedule_booking_restriction').show();
            } else {
                $('#schedule_booking_restriction').hide();
            }
        });

        function toggleVisibility(checkbox, element) {
            $(checkbox).on('change', function () {
                $(element).toggle($(this).is(':checked'));
            });
        }

        toggleVisibility('#schedule_booking_switch', '#schedule_booking_section');
        toggleVisibility('#schedule_booking_checkbox', '#schedule_booking_restriction');
        toggleVisibility('#user_discount_switch', '#user_discount_section');
    </script>

    <script>
        $(document).ready(function() {

            $('.route-alert-reload').on('click', function () {
                const route = $(this).data('route');
                const message = $(this).data('message');
                const reload = true;
                const status = $(this).is(':checked') ? 1 : 0;
                const id = 'maintenance-mode-input';

                route_alert_reload(route, message, reload, status, id);
            });

            $('.maintenance-mode-show').click(function () {
                $('#maintenance-mode-modal').modal('show');
                let isChecked = $('#maintenance-mode-input').is(':checked');
                $('input[name="maintenance_mode"]').prop('checked', isChecked);
            });

            $('input[name="maintenance_mode"]').click(function () {
                let isChecked = $('input[name="maintenance_mode"]').is(':checked');
                $('#maintenance-mode-input').prop('checked', isChecked);
            });

            $('#advanceFeatureToggle').click(function (event) {
                event.preventDefault();
                $('#advanceFeatureSection').show();
                $('#advanceFeatureButtonDiv').hide();
            });

            $('#seeLessToggle').click(function (event) {
                event.preventDefault();
                $('#advanceFeatureSection').hide();
                $('#advanceFeatureButtonDiv').show();
            });

            $('#allSystem').change(function () {
                var isChecked = $(this).is(':checked');
                $('.system-checkbox').prop('checked', isChecked);
            });

            $('.system-checkbox').not('#allSystem').change(function () {
                if (!$(this).is(':checked')) {
                    $('#allSystem').prop('checked', false);
                } else {
                    // Check if all system-related checkboxes are checked
                    if ($('.system-checkbox').not('#allSystem').length === $('.system-checkbox:checked').not('#allSystem').length) {
                        $('#allSystem').prop('checked', true);
                    }
                }
            });

            $(document).ready(function () {
                var startDate = $('#startDate');
                var endDate = $('#endDate');
                var dateError = $('#dateError');

                function updateDatesBasedOnDuration(selectedOption) {
                    if (selectedOption === 'one_day' || selectedOption === 'one_week') {
                        var now = new Date();
                        var timezoneOffset = now.getTimezoneOffset() * 60000;
                        var formattedNow = new Date(now.getTime() - timezoneOffset).toISOString().slice(0, 16);

                        if (selectedOption === 'one_day') {
                            var end = new Date(now);
                            end.setDate(end.getDate() + 1);
                        } else if (selectedOption === 'one_week') {
                            var end = new Date(now);
                            end.setDate(end.getDate() + 7);
                        }

                        var formattedEnd = new Date(end.getTime() - timezoneOffset).toISOString().slice(0, 16);

                        startDate.val(formattedNow).prop('readonly', false).prop('required', true);
                        endDate.val(formattedEnd).prop('readonly', false).prop('required', true);
                        startDate.closest('div').css('display', 'block');
                        endDate.closest('div').css('display', 'block');
                        dateError.hide();
                    } else if (selectedOption === 'until_change') {
                        startDate.val('').prop('readonly', true).prop('required', false);
                        endDate.val('').prop('readonly', true).prop('required', false);
                        startDate.closest('div').css('display', 'none');
                        endDate.closest('div').css('display', 'none');
                        dateError.hide();
                    } else if (selectedOption === 'customize') {
                        startDate.prop('readonly', false).prop('required', true);
                        endDate.prop('readonly', false).prop('required', true);
                        startDate.closest('div').css('display', 'block');
                        endDate.closest('div').css('display', 'block');
                        dateError.hide();
                    }
                }

                function validateDates() {
                    var start = new Date(startDate.val());
                    var end = new Date(endDate.val());
                    if (start > end) {
                        dateError.show();
                        startDate.val('');
                        endDate.val('');
                    } else {
                        dateError.hide();
                    }
                }

                // Initial load
                var selectedOption = $('input[name="maintenance_duration"]:checked').val();
                updateDatesBasedOnDuration(selectedOption);

                // When maintenance duration changes
                $('input[name="maintenance_duration"]').change(function () {
                    var selectedOption = $(this).val();
                    updateDatesBasedOnDuration(selectedOption);
                });

                // When start date or end date changes
                $('#startDate, #endDate').change(function () {
                    $('input[name="maintenance_duration"][value="customize"]').prop('checked', true);
                    startDate.prop('readonly', false).prop('required', true);
                    endDate.prop('readonly', false).prop('required', true);
                    validateDates();
                });
            });

            function updateCheckboxState() {
                let config = {{ $config }};
                if (config) {
                    $('#maintenance-mode-input').prop('checked', true);
                } else {
                    $('#maintenance-mode-input').prop('checked', false);
                }
            }

            $('#maintenance-mode-modal').on('hidden.bs.modal', function () {
                updateCheckboxState();
            });

            updateCheckboxState();


            function route_alert_reload(route, message, reload, status = null, id = null) {
                Swal.fire({
                    title: "{{translate('are_you_sure')}}?",
                    text: message,
                    type: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: 'var(--c2)',
                    confirmButtonColor: 'var(--c1)',
                    cancelButtonText: 'Cancel',
                    confirmButtonText: 'Yes',
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        $.get({
                            url: route,
                            dataType: 'json',
                            data: {},
                            beforeSend: function () {
                                // Code to run before sending the request
                            },
                            success: function (data) {
                                if (reload) {
                                    setTimeout(location.reload.bind(location), 1000);
                                }
                                toastr.success(data.message, {
                                    CloseButton: true,
                                    ProgressBar: true
                                });
                            },
                            complete: function () {
                                // Code to run after request is complete
                            },
                        });
                    } else {
                        if (status === 1) $(`#${id}`).prop('checked', false);
                        if (status === 0) $(`#${id}`).prop('checked', true);
                    }
                });
            }

            if ($webPage === 'business_setup'){
                if (modal.close) {
                    if ($config) {
                        $('#maintenance-mode-input').prop('checked', true);
                    } else {
                        $('#maintenance-mode-input').prop('checked', false);
                    }
                }
            }

        });
    </script>

@endpush
