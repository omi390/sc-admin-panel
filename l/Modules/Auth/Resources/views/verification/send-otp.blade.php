@extends('auth::layouts.master')

@section('title',translate('Account Verification'))

@section('content')
    <div class="register-form dark-support"
         data-bg-img="{{asset('assets/provider-module')}}/img/media/login-bg.png">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-8">
                    <form action="{{route('provider.auth.verification.send-otp')}}" method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="card p-4">
                            <h4 class="mb-30">{{translate('Verify Your Account')}}</h4>
                            <h6 class="mb-2">{{translate('Verify your account in two easy steps-')}}</h6>
                            <ul>
                                <li>{{translate('Fill in your account email/phone below')}}</li>
                                <li>{{translate('We will send you a temporary code ')}}</li>
                                <li>{{translate('Use the code to verify your account on our secure website')}}</li>
                            </ul>

                            <div class="row">
                                <div class="col-10">
                                    <div class="mb-30">
                                        @php($emailVerification = (int)login_setup('email_verification')?->value ?? 0)
                                        @php($phoneVerification = (int)login_setup('phone_verification')?->value ?? 0)

                                        @if($emailVerification && !$user->is_email_verified)
                                            <div class="form-floating">
                                                <input type="email" class="form-control" name="identity"
                                                       placeholder="{{translate('Enter your email address')}} *"
                                                       required>
                                                <input type="hidden" name="identity_type" value="email">
                                                <label>{{translate('Enter your email address')}} *</label>
                                            </div>
                                        @elseif($phoneVerification && !$user->is_phone_verified)
                                            <div class="form-floating">
                                                <input type="tel" class="form-control" name="identity"
                                                       placeholder="{{translate('Enter your phone number')}} *"
                                                       required>
                                                <input type="hidden" name="identity_type" value="phone">
                                                <label>{{translate('Enter your phone number')}} *</label>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn--primary">{{translate('Send OTP')}}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
