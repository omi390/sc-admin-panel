@extends('auth::layouts.master')

@section('title', translate('Reset Password'))

@section('content')
    <div class="register-form dark-support"
         data-bg-img="{{asset('public/assets/provider-module')}}/img/media/login-bg.png">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-8">
                    <form action="{{route('provider.auth.reset-password.send-otp')}}" method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="card p-4">
                            <h4 class="mb-30">{{translate('Forgot your password')}}?</h4>
                            <h6 class="mb-2">{{translate('Change your password in three easy steps. This helps to keep your new password secure .')}}</h6>
                            <ul>
                                <li>{{translate('Fill in your account email/phone below')}}</li>
                                <li>{{translate('We will send you a temporary code ')}}</li>
                                <li>{{translate('Use the code to change your password on our secure website')}}</li>
                            </ul>

                            <div class="row">
                                <div class="col-10">
                                    <div class="mb-30">
                                        @php($forgetPasswordVerificationMetod = (business_config('forget_password_verification_method', 'business_information'))->live_values ?? 'email')

                                        @if($forgetPasswordVerificationMetod == 'email')
                                            <div class="form-floating">
                                                <input type="email" class="form-control" name="identity"
                                                       placeholder="{{translate('Enter your email address')}} *"
                                                       required>
                                                <input type="hidden" name="identity_type" value="email">
                                                <label>{{translate('Enter your email address')}} *</label>
                                            </div>
                                        @elseif($forgetPasswordVerificationMetod == 'phone')
                                            <div class="form-floating">
                                                <label for="identity">{{translate('Enter your phone number')}} *</label>
                                                <input type="tel" class="form-control" name="identity"
                                                       placeholder="{{translate('Enter your phone number')}} *" id="identity"
                                                       required>
                                                <input type="hidden" name="identity_type" value="phone">
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
