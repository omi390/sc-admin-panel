@extends('adminmodule::layouts.master')

@section('title',translate('profile_update'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Update_Profile')}}</h2>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('admin.profile_update') }}" method="post"
                                  enctype="multipart/form-data">
                                @csrf
                                <div class="row gx-2 mt-2">
                                    <div class="col-md-6">
                                        <div class="radius-10 h-100">
                                            <div class="card-body">
                                                <h4 class="c1 mb-20">{{translate('Information')}}</h4>
                                                <div class="row gx-2">
                                                    <div class="col-lg-6">
                                                        <div class="form-floating form-floating__icon mb-30">
                                                            <input type="text" class="form-control" name="first_name"
                                                                   value="{{ auth()->user()->first_name }}"
                                                                   placeholder="{{translate('First_Name')}}">
                                                            <label>{{translate('First_Name')}}</label>
                                                            <span class="material-icons">account_circle</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="form-floating form-floating__icon mb-30">
                                                            <input type="text" class="form-control" name="last_name"
                                                                   value="{{ auth()->user()->last_name }}"
                                                                   placeholder="{{translate('Last_Name')}}">
                                                            <label>{{translate('Last_Name')}}</label>
                                                            <span class="material-icons">account_circle</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-floating form-floating__icon mb-30">
                                                    <input type="email" class="form-control" name="email"
                                                           value="{{ auth()->user()->email }}"
                                                           placeholder="{{translate('Email')}}">
                                                    <label>{{translate('Email')}}</label>
                                                    <span class="material-icons">mail</span>
                                                </div>

                                                <div class="form-floating mb-30">
                                                    <label for="phone">{{translate('Phone')}}</label>
                                                    <input
                                                        oninput="this.value = this.value.replace(/[^+\d]+$/g, '').replace(/(\..*)\./g, '$1');"
                                                        type="tel" class="form-control company_phone phone-input-with-country-picker iti__tel-input" name="phone"
                                                        id="phone"
                                                        value="{{ auth()->user()->phone }}"
                                                        placeholder="{{translate('Phone')}}">
                                                    <div class="">
                                                        <input type="text" class="country-picker-phone-number w-50" value="{{old('phone')}}" name="phone" hidden  readonly>
                                                    </div>
                                                </div>
                                                <div class="row gx-2">
                                                    <div class="col-lg-6">
                                                        <div class="form-floating form-floating__icon mb-30">
                                                            <input type="password" class="form-control" name="password"
                                                                   placeholder="{{translate('Password')}}">
                                                            <label>{{translate('Password')}}</label>
                                                            <span class="material-icons togglePassword">visibility_off</span>
                                                            <span class="material-icons">lock</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="form-floating form-floating__icon mb-30">
                                                            <input type="password" class="form-control"
                                                                   name="confirm_password"
                                                                   placeholder="{{translate('Confirm_Password')}}">
                                                            <label>{{translate('Confirm_Password')}}</label>
                                                            <span class="material-icons togglePassword">visibility_off</span>
                                                            <span class="material-icons">lock</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex flex-column align-items-center gap-3">
                                            <h3 class="mb-0">{{translate('profile_image')}}</h3>
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="upload-file">
                                                    <input type="file" class="upload-file__input" name="profile_image" accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*">
                                                    <div class="upload-file__img">
                                                        <img src="{{auth()->user()->profile_image_full_path}}"
                                                            alt="{{ translate('profile_image') }}">
                                                    </div>
                                                    <span class="upload-file__edit">
                                                        <span class="material-icons">edit</span>
                                                    </span>
                                                </div>
                                            </div>
                                            <p class="opacity-75 max-w220 mx-auto">
                                                {{translate('Image format - jpg, png,jpeg, gif Image Size -maximum size 2 MB Image Ratio - 1:1')}}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-4 flex-wrap justify-content-end mt-20">
                                    <button type="reset" class="btn btn--secondary">{{translate('Reset')}}</button>
                                    <button type="submit"
                                            class="btn btn--primary demo_check">{{translate('update')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

