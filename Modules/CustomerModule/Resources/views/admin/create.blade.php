@extends('adminmodule::layouts.master')

@section('title',translate('customer_add'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/swiper/swiper-bundle.min.css')}}">
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('add_new_customer')}}</h2>
                    </div>

                    <div class="card">
                        <div class="card-body p-30">
                            <form action="{{route('admin.customer.store')}}" method="post" enctype="multipart/form-data"
                                  id="customer-add-form">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-30">
                                            <div class="form-floating form-floating__icon">
                                                <input type="text" class="form-control" name="first_name"
                                                       placeholder="{{translate('first_name')}} *"
                                                       required="" value="{{old('first_name')}}">
                                                <label>{{translate('first_name')}} *</label>
                                                <span class="material-icons">account_circle</span>
                                            </div>
                                        </div>

                                        <div class="mb-30">
                                            <div class="form-floating form-floating__icon">
                                                <input type="text" class="form-control" name="last_name"
                                                       placeholder="{{translate('last_name')}} *"
                                                       required="" value="{{old('last_name')}}">
                                                <label>{{translate('last_name')}} *</label>
                                                <span class="material-icons">account_circle</span>
                                            </div>
                                        </div>

                                        <div class="mb-30">
                                            <div class="form-floating form-floating__icon">
                                                <input type="email" class="form-control" name="email"
                                                       placeholder="{{translate('ex: abc@email.com')}} *"
                                                       required="" value="{{old('email')}}">
                                                <label>{{translate('email')}} *</label>
                                                <span class="material-icons">mail</span>
                                            </div>
                                        </div>

                                        <div class="mb-30">
                                            <div class="form-floating">
                                                <label for="phone">{{translate('Phone')}}</label>
                                                <input type="tel" class="form-control company_phone phone-input-with-country-picker iti__tel-input"
                                                       name="phone" placeholder="{{translate('phone')}} *" id="phone"
                                                       oninput="this.value = this.value.replace(/[^+\d]+$/g, '').replace(/(\..*)\./g, '$1');"
                                                       required="" value="{{old('phone')}}">
                                                <div class="">
                                                    <input type="text" class="country-picker-phone-number w-50" value="{{old('phone')}}" name="phone" hidden  readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-30">
                                            <div class="form-floating form-floating__icon">
                                                <input type="password" class="form-control" name="password"
                                                       placeholder="{{translate('ex: password')}} *"
                                                       required="" value="{{old('password')}}" minlength="8">
                                                <label>{{translate('password')}} *</label>
                                                <span class="material-icons">lock</span>
                                                <span class="material-icons togglePassword">visibility_off</span>
                                            </div>
                                        </div>

                                        <div class="mb-30">
                                            <div class="form-floating form-floating__icon">
                                                <input type="password" class="form-control" name="confirm_password"
                                                       placeholder="{{translate('ex: Confirm_Password')}} *"
                                                       required="" minlength="8">
                                                <label>{{translate('Confirm_Password')}} *</label>
                                                <span class="material-icons togglePassword">visibility_off</span>
                                                <span class="material-icons">lock</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="d-flex flex-column align-items-center gap-3">
                                            <p class="mb-0">{{translate('profile_image')}}</p>
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="upload-file">
                                                    <input type="file" class="upload-file__input" name="profile_image" accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*">
                                                    <div class="upload-file__img">
                                                        <img
                                                            src="{{asset('public/assets/admin-module')}}/img/media/upload-file.png"
                                                            alt="">
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="opacity-75 max-w220 mx-auto text-center">
                                                {{translate('Image format - jpg, png,jpeg,gif Image Size -maximum size 2 MB Image Ratio - 1:1')}}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex justify-content-end gap-20 mt-30">
                                            <button class="btn btn--secondary"
                                                    type="reset">{{translate('reset')}}</button>
                                            <button class="btn btn--primary" type="submit">
                                                {{translate('submit')}}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
