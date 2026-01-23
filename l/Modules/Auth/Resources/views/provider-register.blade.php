<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{translate('Provider_Registration')}}</title>

    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="description" content=""/>
    <meta name="keywords" content=""/>

    <?php
    $favIcon = getBusinessSettingsImageFullPath(key: 'business_favicon', settingType: 'business_information', path: 'business/',  defaultPath : 'public/assets/admin-module/img/placeholder.png')
    ?>
    <link rel="shortcut icon" href="{{ $favIcon }}"/>

    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap"
        rel="stylesheet"/>

    <link href="{{asset('public/assets/provider-module')}}/css/material-icons.css" rel="stylesheet"/>
    <link rel="stylesheet" href="{{asset('public/assets/provider-module')}}/css/bootstrap.min.css"/>
    <link rel="stylesheet"
          href="{{asset('public/assets/provider-module')}}/plugins/perfect-scrollbar/perfect-scrollbar.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/provider-module')}}/css/toastr.css">
    <link rel="stylesheet" href="{{ asset('public/assets/admin-module/plugins/intl-tel-input/css/intlTelInput.min.css') }}">
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/swiper/swiper-bundle.min.css')}}">

    <link rel="stylesheet" href="{{asset('public/assets/provider-module')}}/css/style.css"/>
</head>

<body>
    <div class="preloader"></div>

    <?php
    $logo = getBusinessSettingsImageFullPath(key: 'business_logo', settingType: 'business_information', path: 'business/',  defaultPath : 'public/assets/admin-module/img/placeholder.png');
    ?>

    <div class="dark-support">
        <div class="register-wrap style__two">
            <div class="register-left d-flex justify-content-center align-items-center bg-center" data-bg-img="{{asset('public/assets/provider-module')}}/img/media/login-bg.png">
                <div class="tf-box d-flex flex-column gap-3 align-items-center justify-content-center p-5 mx-5 h-75">
                    <div class="px-xl-5 text-center">
                        <img class="login-img login-logo mb-2"
                            src="{{ $logo  }}"
                            alt="{{ translate('logo') }}">
                        <h2 class="text-center text-dark mt-2">Your <strong class="c1">Right <br> Choice</strong> for On <br> Demand Business</h2>
                    </div>
                </div>
            </div>

            <div class="register-right-wrap bg-white">
                <div class="register-right mx-auto p-3">
                    <div class="text-center mb-5 d-flex flex-column gap-2 mt-3">
                        <h2 class="c1 fw-medium">{{translate('Self_Registration')}}</h2>
                        <p>{{translate('Sign up to provide service broadly')}}</p>
                    </div>

                    <form action="{{route('provider.auth.sign-up-submit')}}" method="POST" enctype="multipart/form-data" id="register-vertical-steps">
                        @csrf

                        <h3>{{translate('Step 1')}}</h3>
                        <section>
                            <div class="" id="register-form-p-0">
                                <h3 class="border-bottom mb-4 pb-2">{{translate('Basic Information')}}</h3>
                                <h5 class="border-bottom mb-4 pb-2">{{translate('General_Information')}}</h5>
                                <div class="mb-4">
                                    <div class="mb-30">
                                        <div class="form-floating form-floating__icon">
                                            <input type="text" class="form-control"
                                                    value="{{old('company_name')}}" name="company_name"
                                                    placeholder="{{translate('Company_Name')}}">
                                            <label>{{translate('Company_Name')}} <span class="text-danger">*</span></label>
                                            <span class="material-icons">apartment</span>
                                        </div>
                                    </div>
                                    <div class="mb-30">
                                        <div class="form-floating form-floating__icon">
                                            <input type="email" id="company_email" class="form-control"
                                                    value="{{old('company_email')}}" name="company_email"
                                                    placeholder="{{translate('email')}}">
                                            <label>{{translate('email')}}</label>
                                            <span class="material-icons">mail</span>
                                        </div>
                                    </div>
                                    <div class="mb-30">
                                        <div class="country-picker-1">
                                            <input type="tel" class="form-control phone-input-with-country-picker"
                                                    value="{{old('company_phone')}}" name="company_phone"
                                                    id="company_phone"
                                                    placeholder="{{translate('123 456 789')}}" maxlength="255">
                                            <div class="">
                                                <input type="text" class="country-picker-phone-number w-50" value="{{old('company_phone')}}" name="company_phone" hidden  readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-30">
                                        <div class="form-floating form-floating__icon">
                                                <input type="text" class="form-control" name="company_address"
                                                            placeholder="{{translate('Address')}}" value="{{old('company_address')}}">
                                            <label>{{translate('Address')}}</label>
                                            <span class="material-icons">location_on</span>
                                        </div>
                                    </div>
                                    <div class="mb-30">
                                        <h5 class="mb-3">{{ translate('company_logo') }} (1:1) <span class="text-text-danger">*</span></h5>
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                            <div>
                                                <div class="form-error-wrap upload-file">
                                                    <input type="file" class="upload-file__input"
                                                            name="logo" accept=".jpg,.png,.jpeg,.gif">
                                                    <span class="upload-file__edit">
                                                            <span class="material-icons">edit</span>
                                                        </span>
                                                    <div class="upload-file__img">
                                                        <img
                                                            src="{{asset('public/assets/provider-module')}}/img/media/upload-file.png"
                                                            alt="">
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="opacity-75 max-w220">{{translate('Image_format_-_jpg,_png,_jpeg,_gif_Image_Size_-_maximum_size_2_MB_Image_Ratio_-_1:1')}}</p>
                                        </div>
                                    </div>

                                    <div class="border-bottom d-flex align-items-center justify-content-between gap-2 pb-2 mb-4">
                                        <h5>{{translate('Contact Person Information')}}</h5>
                                        <div class="d-flex gap-2 align-items-center">
                                            <label for="sameAsGI">{{translate('Same as general info')}}</label>
                                            <input type="checkbox" value="" id="sameAsGI">
                                        </div>
                                    </div>

                                    <div class="sameAsGI_div">
                                        <div class="mb-30">
                                            <div class="form-floating form-floating__icon">
                                                <input type="text" class="form-control"
                                                        value="{{old('contact_person_name')}}"
                                                        name="contact_person_name"
                                                        placeholder="{{translate('Contact_Person_Name')}}">
                                                <label>{{translate('Name')}}</label>
                                                <span class="material-icons">person</span>
                                            </div>
                                        </div>
                                        <div class="mb-30">
                                            <div class="country-picker-2">
                                                <input type="tel" class="form-control phone-input-with-country-picker2"
                                                        value="{{old('contact_person_phone')}}"
                                                        id="contact_person_phone"
                                                        name="contact_person_phone"
                                                        placeholder="{{translate('123 456 789')}}">
                                                <div class="">
                                                    <input type="text" class="country-picker-phone-number2 w-50" value="{{old('contact_person_phone')}}" name="contact_person_phone" hidden  readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-30">
                                            <div class="form-floating form-floating__icon">
                                                <input type="email" class="form-control"
                                                        value="{{old('contact_person_email')}}"
                                                        name="contact_person_email"
                                                        placeholder="{{translate('Email')}}">
                                                <label>{{translate('Email')}}</label>
                                                <span class="material-icons">mail</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <h3>{{translate('Step 2')}}</h3>
                        <section>
                            <div class="">
                                <h5 class="border-bottom mb-4 pb-2">{{translate('Business_Information')}}</h5>
                                <div class="mb-4">
                                    <div class="mb-30">
                                        <div class="form-error-wrap">
                                            <select name="zone_id" id="zone_id" class="form-select">
                                                <option value="0" selected
                                                        disabled>{{translate('Select_Zone')}}</option>
                                                @foreach($zones as $zone)
                                                    <option
                                                        value="{{$zone->id}}" {{old('zone_id')==$zone->id?'selected':''}}>{{$zone->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-30">
                                        <div id="location_map_div">
                                            <input id="pac-input" class="form-control w-auto"
                                                    data-toggle="tooltip"
                                                    data-placement="right"
                                                    data-original-title="{{ translate('search_your_location_here') }}"
                                                    type="text"
                                                    placeholder="{{ translate('search_here') }}"/>
                                            <div id="location_map_canvas"
                                                    class="overflow-hidden rounded h-100"></div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="mb-30">
                                                <div class="form-floating">
                                                    <input type="text" id="latitude" name="latitude"
                                                            class="form-control"
                                                            placeholder="{{ translate('Ex:') }} 23.8118428"
                                                            value="{{ old('latitude') }}" required
                                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="{{translate('Select from map')}}" readonly>
                                                    <label for="latitude">{{ translate('latitude') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-30">
                                                <div class="form-floating">
                                                    <input type="text" step="0.1" name="longitude"
                                                            class="form-control"
                                                            placeholder="{{ translate('Ex:') }} 90.356331"
                                                            id="longitude"
                                                            value="{{ old('longitude') }}" required
                                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="{{translate('Select from map')}}" readonly>
                                                    <label for="longitude">{{ translate('longitude') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-30">
                                        <div class="form-error-wrap">
                                            <select name="identity_type" id="identity_type"
                                                    class="form-select">
                                                <option value="0" selected
                                                        disabled>{{translate('Identity_Type')}}</option>
                                                <option
                                                    value="passport" {{old('identity_type')=='passport'?'selected':''}}>{{translate('passport')}}</option>
                                                <option
                                                    value="nid" {{old('identity_type')=='nid'?'selected':''}}>{{translate('nid')}}</option>
                                                <option
                                                    value="driving_license" {{old('identity_type')=='driving_license'?'selected':''}}>{{translate('driving_license')}}</option>
                                                <option
                                                    value="trade_license" {{old('identity_type')=='trade_license'?'selected':''}}>{{translate('trade_license')}}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-30">
                                        <div class="form-floating form-floating__icon">
                                            <input type="text" class="form-control"
                                                    value="{{old('identity_number')}}" name="identity_number"
                                                    placeholder="{{translate('Identity_Number')}}">
                                            <label>{{translate('Identity_Number')}}</label>
                                            <span class="material-icons">badge</span>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <div class="form-error-wrap">
                                            <div class="mb-3">{{translate('Identity_Image')}}(2:1) <span class="text-danger">*</span></div>
                                            <div id="multi_image_picker2" class="row"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <h3>{{translate('Step 3')}}</h3>
                        <section>
                            <div class="">
                                <h5 class="border-bottom mb-4 pb-2">{{translate('account_Information')}}</h5>
                                <div class="mb-4">
                                    <div class="mb-30" data-bs-toggle="tooltip" data-bs-title="if you want to change
                                    mail you need to go to first stage">
                                        <div class="form-floating form-floating__icon">
                                            <input type="email" id="account_email" class="form-control"
                                                    value="{{old('account_email')}}" name="account_email"
                                                    placeholder="{{translate('Email')}}" readonly>
                                            <label>{{translate('Email')}} <span class="text-danger">*</span></label>
                                            <span class="material-icons">mail</span>
                                        </div>
                                    </div>
                                    <div class="mb-30">
                                        <div class="form-floating form-floating__icon">
                                            <span class="material-icons togglePassword">visibility_off</span>
                                            <input type="password" class="form-control" value=""
                                                    name="password" id="password"
                                                    placeholder="{{translate('Password')}}">
                                            <label>{{translate('Password')}} <span class="text-danger">*</span></label>
                                            <span class="material-icons">lock</span>
                                        </div>
                                    </div>
                                    <div class="mb-30">
                                        <div class="form-floating form-floating__icon">
                                            <span class="material-icons togglePassword">visibility_off</span>
                                            <input type="password" class="form-control" value=""
                                                    name="confirm_password"
                                                    placeholder="{{translate('Confirm_Password')}}">
                                            <label>{{translate('Confirm_Password')}} <span class="text-danger">*</span></label>
                                            <span class="material-icons">lock</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <h3>{{translate('Step 4')}}</h3>
                        <section>
                            <div class="">
                                <h5 class="border-bottom mb-4 pb-2">{{translate('Choose_Business_Plan')}}</h5>
                                <div class="choose_business_plan_wrap">
                                    <div class="d-flex flex-column gap-3">
                                        @if($commission)
                                            <label class="business-plan-option border active rounded p-3 d-flex justify-content-between gap-2">
                                                <div class="d-flex flex-column gap-2">
                                                    <h4>{{translate('Commision Base')}}</h4>
                                                    <div>{{translate('You_have_to_give_a_certain_percentage_of_commission_to_admin_for_every_booking_request')}}</div>
                                                </div>

                                                <input value="commision_base" type="radio" name="choose_business_plan" class="position-static w-26" checked>
                                            </label>
                                        @endif
                                        @if($subscription && count($formattedPackages) > 0)
                                            <label class="business-plan-option border rounded p-3 d-flex justify-content-between gap-2">
                                                <div class="d-flex flex-column gap-2">
                                                    <h4>{{translate('Subscription Base')}}</h4>
                                                    <div>{{translate('You_have_to_pay_a_certain_amount_in_every_month_/_year_to_admin_as_subscription_fee')}}</div>
                                                </div>

                                                <input value="subscription_base" type="radio" name="choose_business_plan" class="position-static w-26">
                                            </label>
                                        @endif
                                    </div>
                                </div>

                                @if($subscription)
                                    <div class="priceBoxSwiper-wrap">
                                        <h5 class="mt-4 mb-3 text-center">{{translate('Select Plan')}}</h5>
                                        <div class="w-100 mw-440">
                                            <div dir="ltr" class="swiper priceBoxSwiper">
                                                <div class="swiper-wrapper">
                                                    <input type="hidden" name="selected_package_id" id="selected-package-input" value="">

                                                    @foreach($formattedPackages as $index => $package)
                                                        <div class="swiper-slide h-auto">
                                                            <div class="price-box {{ $index == 1 ? 'active' : '' }} d-flex flex-column rounded-3 border h-100 package-option" data-id="{{ $package->id }}">
                                                                <div class="price-box__top d-flex gap-2 align-items-center justify-content-center px-2 py-4 text-center mb-3">
                                                                    <span class="material-symbols-outlined uncheck-icon">radio_button_unchecked</span>
                                                                    <span class="material-icons text-warning check-icon">check_circle</span>
                                                                    <h5 class="line-clamp-1">{{ $package->name }}</h5>
                                                                </div>

                                                                <div class="text-center min-h-62 d-flex flex-column justify-content-center">
                                                                    <strong class="h3">{{ with_currency_symbol($package->price) }}</strong>
                                                                    <div>{{ $package->duration }} {{translate('Days')}}</div>
                                                                </div>

                                                                <div class="px-2">
                                                                    <hr>
                                                                </div>

                                                                <div class="p-3 flex-grow-1 d-flex flex-column">
                                                                    <ul class="d-flex flex-column align-items-center gap-2 p-0 fs-12 mb-30">
                                                                        @foreach($package->feature_list as $feature)
                                                                            <li>{{ $feature }}</li>
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <div class="swiper-button-next"></div>
                                                <div class="swiper-button-prev"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="free_trial_or_payment_wrap">
                                    <div class="d-flex flex-column gap-3 mt-4">
                                        @if($freeTrialStatus)
                                            <label class="business-plan-option border active rounded p-3 d-flex justify-content-between gap-2">
                                                <div class="d-flex flex-column gap-2">
                                                    <h4>{{translate('Continue with')}} {{ $duration }} {{translate('Days')}} {{translate('Free Trial')}}</h4>
                                                    <div>{{translate('Use the system free for')}} {{ $duration }} {{translate('days')}}. {{translate('After that you
                                                        have to complete payments to continue')}}</div>
                                                </div>

                                                <input value="free_trial" type="radio" name="free_trial_or_payment" class="position-static w-26" checked>
                                            </label>
                                        @endif

                                        @if($digitalPayment)
                                            <label class="business-plan-option payment_methods_list_container border rounded p-3">
                                                <div class="d-flex justify-content-between gap-2">
                                                    <div class="d-flex flex-column gap-2">
                                                        <h4>{{translate('Select Payment Method')}}</h4>
                                                        <div>{{translate('You can use any of our secure payment method & get selected subscription plan features')}}</div>
                                                    </div>

                                                    <input value="payment" type="radio" name="free_trial_or_payment" class="position-static w-26">
                                                </div>
                                                <div class="payment_methods_list d-flex flex-column gap-3">
                                                    <input type="hidden" name="payment_platform" value="web">
                                                    <input type="hidden" name="callback" value="{{route('provider.auth.login')}}">
                                                    @foreach($paymentGateways ?? [] as $gateway)
                                                    <label class="payment-method-option border active rounded p-3 d-flex justify-content-between">
                                                        <div class="d-flex gap-2 align-items-center">
                                                            <img width="70" src="{{onErrorImage(
                                                                    $gateway['gateway_image'],
                                                                    asset('storage/app/public/payment_modules/gateway_image').'/' . $gateway['gateway_image'],
                                                                    asset('public/assets/admin-module/img/placeholder.png') ,
                                                                    'payment_modules/gateway_image/')}}" alt="{{translate('gateway image')}}">
                                                            <div>{{ $gateway['label'] }}</div>
                                                        </div>

                                                        <input value="{{ $gateway['gateway'] }}" type="radio" name="payment_method" class="position-static" {{ $loop->first ? 'checked' : '' }}>
                                                    </label>
                                                    @endforeach
                                                </div>
                                            </label>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            </div>
                        </section>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{asset('public/assets/provider-module')}}/js/jquery-3.6.0.min.js"></script>
    <script src="{{asset('public/assets/provider-module')}}/js/bootstrap.bundle.min.js"></script>
    <script src="{{asset('public/assets/provider-module')}}/plugins/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="{{asset('public/assets/provider-module')}}/js/main.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/swiper/swiper-bundle.min.js"></script>

    <script src="{{asset('public/assets/provider-module')}}/js/sweet_alert.js"></script>
    <script src="{{asset('public/assets/provider-module')}}/js/toastr.js"></script>
    {!! Toastr::message() !!}

    <script src="{{asset('public/assets/provider-module')}}/plugins/jquery-steps/jquery.steps.min.js"></script>
    <script src="{{asset('public/assets/provider-module')}}/plugins/jquery-validation/jquery.validate.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/js/helper.js"></script>


    <script src="{{ asset('public/assets/admin-module/plugins/intl-tel-input/js/intlTelInput.js') }}"></script>
    <script src="{{ asset('public/assets/admin-module/js/country-picker-init.js') }}"></script>

    <script>
        "use strict";

        document.addEventListener('DOMContentLoaded', function () {
            const packageOptions = document.querySelectorAll('.package-option');
            const selectedPackageInput = document.getElementById('selected-package-input');
            const initialSelectedPackage = document.querySelector('.package-option.active');
            if (initialSelectedPackage) {
                selectedPackageInput.value = initialSelectedPackage.dataset.id;
            }

            packageOptions.forEach(option => {
                option.addEventListener('click', function () {
                    packageOptions.forEach(opt => opt.classList.remove('active'));
                    this.classList.add('active');
                    selectedPackageInput.value = this.dataset.id;
                });
            });
        });

        $(document).ready(function () {
            $("#company_email").on("change keyup paste", function () {
                $('#account_email').val($(this).val());
            });
            $("#company_phone").on("change keyup paste", function () {
                const countryCode = $('#register-vertical-steps-p-0').find('.iti__selected-dial-code').text();
                $('#account_phone').val(`${countryCode} ${$(this).val()}`);
            });
            $('#register-vertical-steps-p-0').find('.iti__flag-container').on("click", function () {
                const countryCode = $('#register-vertical-steps-p-0').find('.iti__selected-dial-code').text();
                $('#account_phone').val(`${countryCode} ${$("#company_phone").val()}`);
            });

            setInterval(() => {
                const countryCode = $('#register-vertical-steps-p-0').find('.iti__selected-dial-code').text();
                $('#account_phone').val(`${countryCode} ${$("#company_phone").val()}`);
                $('#account_email').val($('#company_email').val());
            }, 2000);


            $('#sameAsGI').on('change', function() {
                if ($(this).is(':checked')) {
                    $('[name="contact_person_name"]').val($('[name="company_name"]').val());
                    $('[name="contact_person_email"]').val($('#company_email').val());

                    let companyPhone = $('#company_phone').val();
                    let dialCode = $('.country-picker-1 .iti__selected-dial-code').text();
                    let fullPhoneNumber = dialCode + companyPhone.replace(dialCode, '');

                    $('#contact_person_phone').val(fullPhoneNumber);

                    $('[name="contact_person_phone"]').val(companyPhone.replace(dialCode, ''));
                    // $('.country-picker-phone-number2').val(companyPhone.replace(dialCode, ''));
                    $('.country-picker-phone-number2').val(fullPhoneNumber);

                    $('.country-picker-2').find('.iti__selected-dial-code').text(dialCode);
                    $('.country-picker-2').find('.iti__selected-flag .iti__flag').attr('class', $('.country-picker-1').find('.iti__flag').attr('class'));

                } else {
                    $('[name="contact_person_name"]').val('');
                    $('[name="contact_person_email"]').val('');
                    $('#contact_person_phone').val('');
                    $('.country-picker-phone-number2').val('');
                }
            });



            let swiper = new Swiper(".priceBoxSwiper", {
                slidesPerView: 1.8,
                spaceBetween: 10,
                centeredSlides: true,
                initialSlide: 1,
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
            });
        });

    </script>

    <script>
        (function ($) {
            "use strict";


            let company_phone = undefined;
            let contact_person_phone = undefined;


            let formWizard = $("#register-vertical-steps");

            // Form validation with jQuery
            formWizard.validate({
                errorPlacement: function (error, element) {
                    element.parents('.form-floating, .form-error-wrap').after(error);
                },
            });

            function setValidationRulesAndMessages(rules, messages) {
                formWizard.validate().settings.rules = rules;
                formWizard.validate().settings.messages = messages;
            }

            function handleImageUploadValidation() {
                let uploaded = $("#multi_image_picker2 > .spartan_item_wrapper").length < 2;
                if (uploaded) {
                    var errorMessageElement = formWizard.find(".coba-identity-img-err");

                    if (errorMessageElement.length > 0) {
                        errorMessageElement.text("Please Upload identity Image");
                    } else {
                        formWizard.find("#multi_image_picker2").parents(".form-error-wrap").after(`<span class="text-danger coba-identity-img-err">Please Upload identity Image</span>`);
                    }
                    return false;
                } else {
                    formWizard.find(".coba-identity-img-err").remove();
                }
                return true;
            }

            let firstClick = true;
            formWizard.steps({
                headerTag: "h3",
                bodyTag: "section",
                transitionEffect: "fade",
                stepsOrientation: "vertical",
                autoFocus: true,
                labels: {
                    finish: "Complete",
                    next: "Next",
                    previous: "Previous"
                },
                onStepChanging: function (event, currentIndex, newIndex) {
                    if (newIndex < currentIndex) {
                        return true;
                    }

                    switch (currentIndex) {
                        case 0:
                            setValidationRulesAndMessages({
                                company_name: "required",
                                company_email: {
                                    required: true,
                                    email: true
                                },
                                company_phone: "required",
                                company_address: "required",
                                logo: "required",
                                contact_person_name: "required",
                                contact_person_phone: "required",
                                contact_person_email: "required",
                            }, {
                                company_name: "Please enter your name",
                                company_phone: "Please enter your phone",
                                company_phone_2: "Please enter your phone",
                                company_email: "Please enter a valid email address",
                                company_address: "Please enter your address",
                                logo: "Please upload logo",
                                contact_person_name: "Please enter your name",
                                contact_person_phone: "Please enter your phone",
                                contact_person_email: "Please enter a valid email address",
                            });
                            break;
                        case 1:
                            setValidationRulesAndMessages({
                                zone_id: "required",
                                latitude: "required",
                                longitude: "required",
                                identity_type: "required",
                                identity_number: "required",
                            }, {
                                zone_id: "Please enter your Zone",
                                latitude: "Please enter latitude",
                                longitude: "Please enter longitude",
                                identity_type: "Please enter identity type",
                                identity_number: "Please enter identity number",
                            });

                            if (!handleImageUploadValidation()) {
                                return false;
                            }
                            break;
                        case 2:
                            setValidationRulesAndMessages({
                                account_email: "required",
                                password: {
                                    required: true,
                                    minlength: 8
                                },
                                confirm_password: {
                                    required: true,
                                    minlength: 8,
                                    equalTo: "#password"
                                },
                            }, {
                                account_email: "Please enter email",
                                password: {
                                    required: "Please provide a password",
                                    minlength: "Your password must be at least 8 characters long"
                                },
                                confirm_password: {
                                    required: "Please provide confirm password",
                                    minlength: "Your password must be at least 8 characters long",
                                    equalTo: "Please enter the same password as above",
                                },
                            });
                            break;
                    }

                    formWizard.validate().settings.ignore = ":disabled,:hidden";

                    return formWizard.valid();
                },
                onFinished: function (event, currentIndex) {
                    event.preventDefault();
                    firstClick = $('body').hasClass('subscription_base');

                    if(firstClick){
                        $(".choose_business_plan_wrap").slideUp();
                        $(".priceBoxSwiper-wrap").slideUp();
                        $(".free_trial_or_payment_wrap").slideDown();
                        $("a[href='#finish']").text('Register');
                        $('body').removeClass('subscription_base');
                    } else {
                        formWizard.submit();
                    }
                }
            });

        })(jQuery);
    </script>


    <script src="{{asset('public/assets/provider-module')}}/js//tags-input.min.js"></script>
    <script src="{{asset('public/assets/provider-module')}}/js/spartan-multi-image-picker.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{business_config('google_map', 'third_party')?->live_values['map_api_key_client']}}&libraries=places&v=3.45.8"></script>
    <script>
        "use strict";

        $("#multi_image_picker2").spartanMultiImagePicker({
            fieldName: 'identity_images[]',
            maxCount: 2,
            allowedExt: 'png|jpg|jpeg',
            rowHeight: 'auto',
            groupClassName: 'col-6',
            dropFileLabel: "{{translate('Drop_here')}}",
            placeholderImage: {
                image: '{{asset('public/assets/admin-module')}}/img/media/banner-upload-file.png',
                width: '100%',
            },

            onRenderedPreview: function (index) {
                toastr.success('{{translate('Image_added')}}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            },
            // onRemoveRow: function (index) {
            //     console.log(index);
            // },
            onExtensionErr: function (index, file) {
                toastr.error('{{translate('Please_only_input_png_or_jpg_type_file')}}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            },
            onSizeErr: function (index, file) {
                toastr.error('{{translate('File_size_too_big')}}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            }

        });
        @if ($errors->any())
        @foreach($errors->all() as $error)
        toastr.error('{{$error}}', Error, {
            CloseButton: true,
            ProgressBar: true
        });
        @endforeach
        @endif

        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#viewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileEg1").change(function () {
            readURL(this);
        });


        $(document).ready(function () {
            function initAutocomplete(mapCanvasId) {
                var myLatLng = {
                    lat: 23.811842872190343,
                    lng: 90.356331
                };
                const map = new google.maps.Map(document.getElementById(mapCanvasId), {
                    center: {
                        lat: 23.811842872190343,
                        lng: 90.356331
                    },
                    zoom: 13,
                    mapTypeId: "roadmap",
                });

                var marker = new google.maps.Marker({
                    position: myLatLng,
                    map: map,
                });

                marker.setMap(map);
                var geocoder = geocoder = new google.maps.Geocoder();
                google.maps.event.addListener(map, 'click', function (mapsMouseEvent) {
                    var coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2);
                    var coordinates = JSON.parse(coordinates);
                    var latlng = new google.maps.LatLng(coordinates['lat'], coordinates['lng']);
                    marker.setPosition(latlng);
                    map.panTo(latlng);

                    document.getElementById('latitude').value = coordinates['lat'];
                    document.getElementById('longitude').value = coordinates['lng'];


                    geocoder.geocode({
                        'latLng': latlng
                    }, function (results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            if (results[1]) {
                                document.getElementById('address').innerHtml = results[1].formatted_address;
                            }
                        }
                    });
                });

                const input = document.getElementById("pac-input");
                const searchBox = new google.maps.places.SearchBox(input);
                map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);

                map.addListener("bounds_changed", () => {
                    searchBox.setBounds(map.getBounds());
                });
                let markers = [];

                searchBox.addListener("places_changed", () => {
                    const places = searchBox.getPlaces();

                    if (places.length == 0) {
                        return;
                    }

                    markers.forEach((marker) => {
                        marker.setMap(null);
                    });
                    markers = [];

                    const bounds = new google.maps.LatLngBounds();
                    places.forEach((place) => {
                        if (!place.geometry || !place.geometry.location) {
                            return;
                        }
                        var mrkr = new google.maps.Marker({
                            map,
                            title: place.name,
                            position: place.geometry.location,
                        });
                        google.maps.event.addListener(mrkr, "click", function (event) {
                            document.getElementById('latitude').value = this.position.lat();
                            document.getElementById('longitude').value = this.position.lng();
                        });

                        markers.push(mrkr);

                        if (place.geometry.viewport) {
                            bounds.union(place.geometry.viewport);
                        } else {
                            bounds.extend(place.geometry.location);
                        }
                    });
                    map.fitBounds(bounds);
                });
            };
            initAutocomplete("location_map_canvas");
        });


        $('.__right-eye').on('click', function () {
            if ($(this).hasClass('active')) {
                $(this).removeClass('active')
                $(this).find('i').removeClass('tio-invisible')
                $(this).find('i').addClass('tio-hidden-outlined')
                $(this).siblings('input').attr('type', 'password')
            } else {
                $(this).addClass('active')
                $(this).siblings('input').attr('type', 'text')


                $(this).find('i').addClass('tio-invisible')
                $(this).find('i').removeClass('tio-hidden-outlined')
            }
        })
    </script>
</body>
</html>
