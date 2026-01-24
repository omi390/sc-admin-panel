@extends('adminmodule::layouts.master')

@section('title',translate('customer_update'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/swiper/swiper-bundle.min.css')}}">

@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('customer_update')}}</h2>
                    </div>

                    <div class="card">
                        <div class="card-body p-30">
                            <form action="{{route('admin.customer.update',[$customer['id']])}}" method="post"
                                  enctype="multipart/form-data"
                                  id="customer-update-form">
                                @csrf
                                @method('put')
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-30">
                                            <div class="form-floating form-floating__icon">
                                                <input type="text" class="form-control" name="first_name"
                                                       placeholder="{{translate('first_name')}} *"
                                                       required="" value="{{$customer['first_name']}}">
                                                <label>{{translate('first_name')}} *</label>
                                                <span class="material-icons">account_circle</span>
                                            </div>
                                        </div>

                                        <div class="mb-30">
                                            <div class="form-floating form-floating__icon">
                                                <input type="text" class="form-control" name="last_name"
                                                       placeholder="{{translate('last_name')}} *"
                                                       required="" value="{{$customer['last_name']}}">
                                                <label>{{translate('last_name')}} *</label>
                                                <span class="material-icons">account_circle</span>
                                            </div>
                                        </div>

                                        <div class="mb-30">
                                            <div class="form-floating form-floating__icon">
                                                <input type="email" class="form-control" name="email"
                                                       placeholder="{{translate('ex: abc@email.com')}} *"
                                                       required="" value="{{$customer['email']}}">
                                                <label>{{translate('email')}} *</label>
                                                <span class="material-icons">mail</span>
                                            </div>
                                        </div>

                                        <div class="mb-30">
                                            <div class="form-floating">
                                                <input type="tel" class="form-control company_phone phone-input-with-country-picker iti__tel-input" name="phone"
                                                       placeholder="{{translate('phone')}} *" id="phone"
                                                       oninput="this.value = this.value.replace(/[^+\d]+$/g, '').replace(/(\..*)\./g, '$1');"
                                                       required="" value="{{$customer['phone']}}">
                                            </div>
                                            <input type="text" class="country-picker-phone-number w-50" value="{{old('phone')}}" name="phone" hidden  readonly>

                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="d-flex flex-column align-items-center gap-3">
                                            <p class="mb-0">{{translate('profile_image')}}</p>
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="upload-file">
                                                    <input type="file" class="upload-file__input" name="profile_image" accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*">
                                                    <div class="upload-file__img">
                                                        <img src="{{$customer->profile_image_full_path}}" alt="{{translate('image')}}">
                                                    </div>
                                                    <span class="upload-file__edit">
                                                        <span class="material-icons">edit</span>
                                                    </span>
                                                </div>
                                            </div>
                                            <p class="opacity-75 max-w220 mx-auto">
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
