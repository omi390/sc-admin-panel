@extends('adminmodule::layouts.master')

@section('title',translate('business_setup'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/select.dataTables.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/swiper/swiper-bundle.min.css')}}">
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-4">
                <h2 class="page-title">{{translate('Login Setup')}}</h2>
            </div>
            <div class="mb-4">
                <ul class="nav nav--tabs nav--tabs__style2">
                    <li class="nav-item">
                        <a href="{{url()->current()}}?web_page=customer_login" class="nav-link {{$webPage=='customer_login'?'active':''}}">
                            {{translate('General Login Setup')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{url()->current()}}?web_page=admin_provider_login" class="nav-link {{$webPage=='admin_provider_login'?'active':''}}">
                            {{translate('Rules & Restrictions')}}
                        </a>
                    </li>
                </ul>
            </div>
            @if($webPage=='customer_login')
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-1">
                        {{translate('Setup Login Option')}}
                    </h4>
                    <p class="fs-12">{{translate('The option you select will allow the customer to login')}}</p>
                </div>
                <div class="card-body pt-3">
                    <form id="login-setup-form" action="{{route('admin.business-settings.login-setup-update')}}" method="post">
                        @csrf
                        <div class="row g-3 mb-4">
                            <div class="col-sm-6 col-md-4">
                                <label class="form-check form--check form--check--inline border rounded">
                                    <span class="user-select-none form-check-label flex-grow-1">{{translate('Manual Login')}}<span class="material-symbols-outlined" data-bs-toggle="tooltip" title="{{ translate('By enabling manual login, customers will get the option to create account and log in using necessary credentials & password in the app & website') }}">help</span></span>
                                    <input class="form-check-input" type="checkbox" name="manual_login" {{ $loginOptions?->manual_login == 1 ? 'checked' : '' }} id="otp-manual_login">
                                </label>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <label class="form-check form--check form--check--inline border rounded">
                                    <span class="user-select-none form-check-label flex-grow-1">{{translate('OTP login')}}<span class="material-symbols-outlined" data-bs-toggle="tooltip" title="{{ translate('With OTP Login, customers can log in using their phone number. while new customers can create accounts instantly.') }}">help</span></span>
                                    <input class="form-check-input" name="otp_login" type="checkbox"
                                           {{ $loginOptions?->otp_login == 1 ? 'checked' : '' }}
                                           id="otp-login">
                                </label>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <label class="form-check form--check form--check--inline border rounded">
                                    <span class="user-select-none form-check-label flex-grow-1">{{translate('Social Media Login')}}<span class="material-symbols-outlined" data-bs-toggle="tooltip" title="{{ translate('With Social Login, customers can log in using social media credentials. while new customers can create accounts instantly.') }}">help</span></span>
                                    <input class="form-check-input" name="social_media_login" type="checkbox"
                                           {{ $loginOptions?->social_media_login == 1 ? 'checked' : '' }}
                                           id="social-media-login">
                                </label>
                            </div>
                        </div>
                        <div class="mb-4" id="social-media-setup" style="display: none;">
                            <div class="mb-3">
                                <h4 class="mb-1">
                                    {{translate('Social Media Login Setup')}}
                                </h4>
                            </div>
                            <div class="bg-light p-4 rounded">
                                <h4 class="mb-1">
                                    {{translate('Choose Social Media')}}
                                </h4>
                                <div class="row g-3">
                                    <div class="col-sm-6 col-md-4">
                                        <label class="form-check form--check form--check--inline border rounded">
                                            <span class="user-select-none form-check-label flex-grow-1">{{translate('Google')}}<span class="material-symbols-outlined" data-bs-toggle="tooltip"  title="{{ translate('Enabling Google Login, customers can log in to the site using their existing Gmail credentials.') }}">help</span></span>
                                            <input class="form-check-input" name="google" type="checkbox"
                                                   {{ $socialMediaLoginOptions?->google == 1 ? 'checked' : '' }}
                                                   id="google">
                                        </label>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <label class="form-check form--check form--check--inline border rounded">
                                            <span class="user-select-none form-check-label flex-grow-1">{{translate('Facebook')}}<span class="material-symbols-outlined" data-bs-toggle="tooltip" title="{{ translate('Enabling Facebook Login, customers can log in to the site using their existing Facebook credentials.') }}">help</span></span>
                                            <input class="form-check-input" name="facebook" type="checkbox"
                                                   {{ $socialMediaLoginOptions?->facebook == 1 ? 'checked' : '' }}
                                                   id="facebook">
                                        </label>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <label class="form-check form--check form--check--inline border rounded">
                                            <span class="user-select-none form-check-label flex-grow-1">{{translate('Apple')}}<span class="material-symbols-outlined" data-bs-toggle="tooltip" title="{{ translate('Enabling Apple Login, customers can log in to the site using their existing Apple login credentials.') }}">help</span></span>
                                            <input class="form-check-input" name="apple" type="checkbox"
                                                   {{ $socialMediaLoginOptions?->apple == 1 ? 'checked' : '' }}
                                                   id="apple">
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="mb-3">
                                <h4 class="mb-1">
                                    {{translate('OTP Verification')}}
                                </h4>
                                <p class="fs-12">{{translate('The option you select will need to verified by the customer & the provider')}}</p>
                            </div>
                            <div class="bg-light p-4 rounded">
                                <div class="row g-3">
                                    <div class="col-sm-6 col-md-4">
                                        <label class="form-check form--check form--check--inline border rounded">
                                            <span class="user-select-none form-check-label flex-grow-1">{{translate('Email Verification')}}<span class="material-symbols-outlined" data-bs-toggle="tooltip" title="{{ translate('If Email verification is on, Customers must verify their email address with an OTP to complete the process.') }}">help</span></span>
                                            <input class="form-check-input" name="email_verification" type="checkbox"
                                                   {{ $emailVerification == 1 ? 'checked' : '' }}
                                                   id="email-verification">
                                        </label>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <label class="form-check form--check form--check--inline border rounded">
                                            <span class="user-select-none form-check-label flex-grow-1">{{translate('Phone Number Verification')}} <span class="material-symbols-outlined" data-bs-toggle="tooltip" title="{{ translate('If Phone Number verification is on, Customers must verify their Phone Number with an OTP to complete the process.') }}">help</span></span>
                                            <input class="form-check-input" name="phone_verification" type="checkbox"
                                                   {{ $phoneVerification == 1 ? 'checked' : '' }}
                                                   id="phone-verification">
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-3 pt-4">
                            <button type="reset" class="btn btn--secondary">{{translate('reset')}}</button>
                            <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}" class="btn btn--primary">{{translate('submit')}}</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
            @if($webPage=='admin_provider_login')
            <div class="card">
                <div class="card-body p-30">
                    <form action="{{route('admin.business-settings.set-otp-login-information')}}"
                          method="POST">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6 col-12">
                                <div class="form-floating">
                                    <input class="form-control remove-spin"
                                           name="temporary_login_block_time"
                                           placeholder="{{translate('Temporary Login Block Time')}} *"
                                           type="number" min="0" required
                                           value="{{$dataValues->where('key_name', 'temporary_login_block_time')->first()->live_values ?? ''}}"
                                    >
                                    <label>{{translate('Temporary Login Block Time')}}* <small
                                            class="text-danger">({{translate('In Second')}})</small>
                                    </label>

                                    <span class="material-icons" data-bs-toggle="tooltip"
                                          title="{{ translate('Temporary login block time refers to a security measure implemented by systems to restrict access for a specified period of time for wrong Password submission.') }}">info</span>
                                </div>
                            </div>

                            <div class="col-md-6 col-12">
                                <div class="form-floating">
                                    <input class="form-control remove-spin" name="maximum_login_hit"
                                           placeholder="{{translate('Maximum Login Hit')}} *"
                                           type="number" min="0" required
                                           value="{{$dataValues->where('key_name', 'maximum_login_hit')->first()->live_values ?? ''}}"
                                    >
                                    <label>{{translate('Maximum Login Hit')}}* </label>

                                    <span class="material-icons" data-bs-toggle="tooltip"
                                          title="{{ translate('The maximum login hit is a measure of how many times a user can submit password within a time.') }}">info</span>
                                </div>
                            </div>

                            <div class="col-md-6 col-12">
                                <div class="form-floating">
                                    <input class="form-control remove-spin"
                                           name="temporary_otp_block_time"
                                           placeholder="{{translate('Temporary OTP Block Time')}} *"
                                           type="number" min="0" required
                                           value="{{$dataValues->where('key_name', 'temporary_otp_block_time')->first()->live_values ?? ''}}"
                                    >
                                    <label>{{translate('Temporary OTP Block Time')}}* <small
                                            class="text-danger">({{translate('In Second')}})</small></label>

                                    <span class="material-icons" data-bs-toggle="tooltip"
                                          title="{{ translate('Temporary OTP block time refers to a security measure implemented by systems to restrict access to OTP service for a specified period of time for wrong OTP submission.') }}">info</span>
                                </div>
                            </div>

                            <div class="col-md-6 col-12">
                                <div class="form-floating">
                                    <input class="form-control remove-spin" name="maximum_otp_hit"
                                           placeholder="{{translate('Maximum OTP Hit')}} *"
                                           type="number" min="0" required
                                           value="{{$dataValues->where('key_name', 'maximum_otp_hit')->first()->live_values ?? ''}}">
                                    <label>{{translate('Maximum OTP Hit')}} *</label>

                                    <span class="material-icons" data-bs-toggle="tooltip"
                                          title="{{ translate('The maximum OTP hit is a measure of how many times a specific one-time password has been generated and used within a time.') }}">info</span>
                                </div>
                            </div>

                            <div class="col-md-6 col-12">
                                <div class="form-floating">
                                    <input class="form-control remove-spin" name="otp_resend_time"
                                           placeholder="{{translate('OTP Resend Time')}} *"
                                           type="number" min="0" required
                                           value="{{$dataValues->where('key_name', 'otp_resend_time')->first()->live_values ?? ''}}"
                                    >

                                    <label>{{translate('OTP Resend Time')}}* <small
                                            class="text-danger">({{translate('In Second')}})</small></label>

                                    <span class="material-icons" data-bs-toggle="tooltip"
                                          title="{{ translate('If the user fails to get the OTP within a certain time, user can request a resend.') }}">info</span>
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
             @endif
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="smsConfigModal" tabindex="-1" role="dialog" aria-labelledby="smsConfigModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <button type="button" class="btn-close position-absolute right-3 top-3 z-10" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body text-center">
                    <div>
                        <img src="{{ asset('public/assets/admin-module/img/sms-img.png') }}" alt="{{ translate('image') }}">
                    </div>
                    <div class="py-4">
                        <h4 class="modal-title" id="smsConfigModalLabel">{{ translate('Set Up SMS Configuration First') }}</h4>
                    </div>
                    <p>{{ translate('It looks like your SMS configuration is not set up yet. To enable the OTP system, please set up the SMS configuration first.') }}</p>
                </div>
                <div class="text text-center mb-5">
                    <a href="{{route('admin.configuration.get-third-party-config', ['web_page' => 'sms_config'])}}" target="_blank" class="btn btn--primary">{{ translate('Go to SMS Configuration') }}</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="socialMediaConfigModal" tabindex="-1" role="dialog" aria-labelledby="socialMediaConfigModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <button type="button" class="btn-close position-absolute right-3 top-3 z-10" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body text-center">
                    <div>
                        <img src="{{ asset('public/assets/admin-module/img/sms-img.png') }}" alt="{{ translate('image') }}">
                    </div>
                    <div class="py-4">
                        <h4 class="modal-title" id="socialMediaConfigModalLabel">{{ translate('Set Up Social Media Configuration First') }}</h4>
                    </div>
                    <p id="socialMediaConfigModalDescription">{{ translate('It looks like your social media configuration is not set up yet. To enable the social media login system, please set up the social media configuration first.') }}</p>
                </div>
                <div class="text text-center mb-5">
                    <a id="socialLink" href="{{route('admin.configuration.get-third-party-config', ['web_page' => 'social_login'])}}" target="_blank" class="btn btn--primary">{{ translate('Go to Social Media Configuration') }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use strict";

        $(document).ready(function() {
            toggleSocialMediaSetup();

            $('#social-media-login').change(function() {
                toggleSocialMediaSetup();
            });

            $('button[type="reset"]').click(function() {
                setTimeout(function() {
                    toggleSocialMediaSetup();
                }, 0);
            });

            $(window).on('pageshow', function() {
                toggleSocialMediaSetup();
            });

            function toggleSocialMediaSetup() {
                if ($('#social-media-login').is(':checked')) {
                    $('#social-media-setup').show();

                    if (!$('#google').is(':checked') && !$('#facebook').is(':checked')) {
                        $('#google').prop('checked', true);
                    }
                } else {
                    $('#social-media-setup').hide();
                }
            }

            $('#google, #facebook').change(function() {
                if (!$('#google').is(':checked') && !$('#facebook').is(':checked')) {
                    $(this).prop('checked', true);
                }
            });

        });

        $(document).ready(function() {
            $('#otp-login').change(function(event) {
                if ($(this).is(':checked')) {
                    isSmsGatewayActivated(function(isActivated) {
                        if (!isActivated) {
                            event.preventDefault();
                            $('#otp-login').prop('checked', false);
                            $('#smsConfigModal').modal('show');
                        }
                    });
                }
            });

            function isSmsGatewayActivated(callback) {
                $.ajax({
                    url: '{{ route('admin.business-settings.check-active-sms-gateway') }}',
                    method: 'GET',
                    success: function(response) {
                        callback(response > 0);
                    },
                    error: function() {
                        callback(false);
                    }
                });
            }
        });

        $(document).ready(function() {
            function isSocialMediaActivated(socialMedia, callback) {
                $.ajax({
                    url: '{{ route('admin.business-settings.check-active-social-media') }}',
                    method: 'GET',
                    success: function(response) {
                        callback(response[socialMedia] == 1);
                    },
                    error: function() {
                        callback(false);
                    }
                });
            }

            function showModal(title, description, imageUrl, link) {
                $('#socialMediaConfigModalLabel').text(title);
                $('#socialMediaConfigModalDescription').text(description);
                $('#socialLink').attr('href', link);
                $('#socialMediaConfigModal img').attr('src', imageUrl);
                $('#socialMediaConfigModal').modal('show');
            }

            $('#apple').change(function(event) {
                if ($(this).is(':checked')) {
                    isSocialMediaActivated('apple', function(isActivated) {
                        if (!isActivated) {
                            event.preventDefault();
                            $('#apple').prop('checked', false);
                            showModal(
                                '{{ translate("Set Up Apple Configuration First") }}',
                                '{{ translate("It looks like your Apple configuration is not set up yet. To enable the Apple login system, please set up the Apple configuration first.") }}',
                                '{{ asset("public/assets/admin-module/img/apple.png") }}',
                                '{{route('admin.configuration.get-third-party-config', ['web_page' => 'apple_login'])}}'
                            );
                        }
                    });
                }
            });
        });


        $('#login-setup-form').submit(function(event) {
            let manualLogin = $('#otp-manual_login').prop('checked');
            let otpLogin = $('#otp-login').prop('checked');
            let socialMediaLogin = $('#social-media-login').prop('checked');

            if (!manualLogin && !otpLogin && !socialMediaLogin) {
                event.preventDefault();
                Swal.fire({
                    type: 'warning',
                    title: '{{ translate("No Login Option Selected") }}!',
                    text: '{{ translate("Please select at least one login option.") }}',
                    confirmButtonText: '{{ translate("OK") }}',
                    confirmButtonColor: '#FC6A57',
                });
            }
        });

    </script>
@endpush
