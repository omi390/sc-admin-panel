<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{translate('Provider_login')}}</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="description" content=""/>
    <meta name="keywords" content=""/>
    <meta name="robots" content="nofollow, noindex ">
    @php($favIcon = getBusinessSettingsImageFullPath(key: 'business_favicon', settingType: 'business_information', path: 'business/',  defaultPath : 'public/assets/admin-module/img/placeholder.png'))
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

    <link rel="stylesheet" href="{{asset('public/assets/provider-module')}}/css/style.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/provider-module')}}/css/toastr.css">
</head>

<body>
<div class="preloader"></div>
<?php
$logo = getBusinessSettingsImageFullPath(key: 'business_logo', settingType: 'business_information', path: 'business/',  defaultPath : 'public/assets/admin-module/img/placeholder.png');

?>
<div>
    <form action="{{route('provider.auth.login')}}" enctype="multipart/form-data" method="POST" id="login-form">
        @csrf
        <div class="login-wrap">
            <div class="login-left d-flex justify-content-center align-items-center bg-center" data-bg-img="{{asset('public/assets/provider-module')}}/img/media/login-bg.png">
                <div class="tf-box d-flex flex-column gap-3 align-items-center justify-content-center p-5 mx-4 mx-sm-5 h-75">
                    <img class="login-logo mb-2"
                        src="{{ $logo }}" alt="{{ translate('logo') }}">
                    <h2 class="text-center text-dark px-xl-5">Your <strong class="c1">Right <br> Choice </strong> for On <br> Demand Business</h2>
                </div>
            </div>
            <div class="login-right-wrap bg-white">
                <div class="login-right w-100 m-auto p-3">

                    <div class="d-flex justify-content-between align-items-start gap-2 mb-5 mt-3">
                        <div class="d-flex flex-column gap-2">
                            <h2 class="c1 fw-medium">{{translate('provider_Sign_In')}}</h2>
                            <p>{{translate('sign_in_to_stay_connected')}}</p>
                        </div>
                        <span class="badge badge-primary fz-12 opacity-75">
                            {{translate('Software_Version')}} : {{ env('SOFTWARE_VERSION') }}
                        </span>
                    </div>

                    <div class="mb-4">
                        <div class="mb-5">
                            <div class="form-floating form-floating__icon">
                                <input type="email" name="email_or_phone" class="form-control"
                                        placeholder="{{translate('email')}}" required="" id="email">
                                <label>{{translate('email')}}</label>
                                <span class="material-icons">mail</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-floating form-floating__icon">
                                <input type="password" name="password" class="form-control"
                                        placeholder="{{translate('password')}}" required=""
                                        id="password">
                                <label>{{translate('password')}}</label>
                                <span class="material-icons togglePassword">visibility_off</span>
                                <span class="material-icons">lock</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div class="d-flex gap-1 align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="rememberMeCheckbox">
                                    <label class="form-check-label" for="rememberMeCheckbox">{{translate('Remember me?')}}</label>
                                </div>
                            </div>
                            <div class="d-flex gap-1 align-items-center">
                                <a href="{{route('provider.auth.reset-password.index')}}"
                                    class="lh-1">{{translate('Forget Password')}}?</a>
                            </div>
                        </div>
                    </div>

                    <div class="recaptcha d-flex justify-content-center mb-3 dark-support">
                        @php($recaptcha = business_config('recaptcha', 'third_party'))
                        @if(isset($recaptcha) && $recaptcha->is_active)
                            <div class="recaptcha d-flex justify-content-center mb-4">
                                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                            </div>
                        @endif
                    </div>

                    <div class="d-flex mb-4">
                        <button class="btn flex-grow-1 btn--primary text-capitalize"
                                type="submit">{{translate('login')}}</button>
                    </div>

                    @if(business_config('provider_self_registration','provider_config')->live_values??0)
                        <div class="text-center fz-12 pb-4">
                            {{translate('Want to Join as Provider')}} <a
                                href="{{route('provider.auth.sign-up')}}"
                                class="c1 text-decoration-underline">{{translate('Register Here')}}</a>
                        </div>
                    @endif
                </div>

                @if(env('APP_ENV')=='demo')
                    <div class="login-footer d-flex justify-content-between c1-bg text-white">
                        <div>
                            <div>{{translate('email')}} : {{translate('provider@provider.com')}}</div>
                            <div>{{translate('password')}} : {{translate('12345678')}}</div>
                        </div>
                        <button type="button" class="btn login-copy">
                            <span class="material-symbols-outlined m-0">content_copy</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </form>
</div>

<script src="{{asset('public/assets/provider-module')}}/js/jquery-3.6.0.min.js"></script>
<script src="{{asset('public/assets/provider-module')}}/js/bootstrap.bundle.min.js"></script>
<script src="{{asset('public/assets/provider-module')}}/plugins/perfect-scrollbar/perfect-scrollbar.min.js"></script>
<script src="{{asset('public/assets/provider-module')}}/js/main.js"></script>


<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
<script src="{{asset('public/assets/provider-module')}}/js/sweet_alert.js"></script>
<script src="{{asset('public/assets/provider-module')}}/js/toastr.js"></script>
{!! Toastr::message() !!}

@php($recaptcha = business_config('recaptcha', 'third_party'))
@if(isset($recaptcha) && $recaptcha->is_active)
    <script src="https://www.google.com/recaptcha/api.js?render={{$recaptcha->live_values['site_key']}}"></script>
    <script>
        "use strict";
        $('#signInBtn').click(function (e) {
            e.preventDefault();

            if (typeof grecaptcha === 'undefined') {
                toastr.error('Invalid recaptcha key provided. Please check the recaptcha configuration.');
                return;
            }

            grecaptcha.ready(function () {
                grecaptcha.execute('{{$recaptcha->live_values['site_key']}}', {action: 'submit'}).then(function (token) {
                    document.getElementById('g-recaptcha-response').value = token;
                    document.querySelector('form').submit();
                });
            });

            window.onerror = function(message) {
                var errorMessage = 'An unexpected error occurred.';
                if (message.includes('Invalid site key')) {
                    errorMessage = 'Invalid site key provided. Please check the site key configuration.';
                } else if (message.includes('not loaded in api.js')) {
                    errorMessage = 'reCAPTCHA API could not be loaded. Please check the API configuration.';
                }
                toastr.error(errorMessage)
                return true;
            };
        });
    </script>
@endif

<script>
    "use strict";

        @if(env('APP_ENV')=='demo')

            $('.login-copy').on('click', function () {
                copy_cred()
            })

            function copy_cred() {
                $('#email').val('provider@provider.com');
                $('#password').val('12345678');
                toastr.success('{{translate('Copied successfully')}}', 'Success', {
                    CloseButton: true,
                    ProgressBar: true
                });
            }
       @endif

        @php($recaptcha = business_config('recaptcha', 'third_party'))
        @if(isset($recaptcha) && $recaptcha->is_active)

            var onloadCallback = function () {
                grecaptcha.render('recaptcha_element', {
                    'sitekey': '{{$recaptcha->live_values['site_key']}}'
                });
            };

            $("#login-form").on('submit', function (e) {
                var response = grecaptcha.getResponse();

                if (response.length === 0) {
                    e.preventDefault();
                    toastr.error("{{translate('please_check_the_recaptcha')}}");
                }
            });
        @endif

        @if ($errors->any())

            @foreach($errors->all() as $error)
            toastr.error('{{$error}}', Error, {
                CloseButton: true,
                ProgressBar: true
            });
            @endforeach
       @endif
</script>
</body>
</html>
