@extends('adminmodule::layouts.master')

@section('title', translate('Update_employee_permission'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.css"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Update_employee_permission')}}</h2>
                    </div>

                    <div class="card">
                        <div class="card-body py-4">
                            <form id="add-new-employee-form" action="{{route('admin.employee.update',[$employee->id])}}"
                                  method="post" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div>
                                    <div>
                                        <div class="d-flex gap-1 flex-column">
                                            <h4>{{translate('Employee Info')}}</h4>
                                            <p class="fs-12">{{translate('Give employee’s basic and account info')}}</p>
                                        </div>
                                    </div>
                                    <section>
                                        <div class="d-flex flex-column gap-1 mb-20">
                                            <h4>{{translate('General_Information')}}</h4>
                                            <p>{{translate('Fill an employee’s general info such as name, address number and set
                                                role')}}</p>
                                        </div>

                                        <hr class="mb-30">

                                        <div class="row mb-5">
                                            <div class="col-lg-8">
                                                <div class="row">
                                                    <div class="col-md-6 mb-30">
                                                        <div class="input-wrap form-floating form-floating__icon">
                                                            <input type="text" class="form-control" name="first_name"
                                                                   placeholder="{{translate('First_name')}}"
                                                                   value="{{$employee['first_name']}}" required>
                                                            <label>{{translate('First_name')}}</label>
                                                            <span class="material-icons">account_circle</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-30">
                                                        <div class="input-wrap form-floating form-floating__icon">
                                                            <input type="text" class="form-control" name="last_name"
                                                                   placeholder="{{translate('Last_name')}}"
                                                                   value="{{$employee['last_name']}}" required>
                                                            <label>{{translate('Last_name')}}</label>
                                                            <span class="material-icons">account_circle</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-30">
                                                        <div class="input-wrap form-floating">
                                                            <label for="phone">{{translate('Phone_number')}}</label>
                                                            <input type="tel"
                                                                   class="form-control phone-input-with-country-picker"
                                                                   id="exampleInputPhone" name="phone"
                                                                   placeholder="{{translate('Phone_number')}}"
                                                                   value="{{$employee['phone']}}" required>
                                                            <div class="">
                                                                <input type="text"
                                                                       class="country-picker-phone-number w-50"
                                                                       value="{{old('phone')}}" name="phone" hidden
                                                                       readonly>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-30">
                                                        <div class="input-wrap form-floating form-floating__icon">
                                                            <input type="text" class="form-control" id="address"
                                                                   name="address"
                                                                   placeholder="{{translate('address')}}"
                                                                   value="{{$employee->addresses->first()->address}}"
                                                                   required>
                                                            <label>{{translate('Address')}}</label>
                                                            <span class="material-icons">home</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-30">
                                                        <div class="input-wrap">
                                                            <select class="select-identity theme-input-style role-btn"
                                                                    name="role_id" required>
                                                                <option selected
                                                                        disabled>{{translate('Select_role')}}</option>
                                                                @foreach($roles as $role)
                                                                    <option
                                                                        value="{{$role->id}}" {{$employee->roles->where('id',$role->id)->first()?'selected':''}}>
                                                                        {{$role->role_name}}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-30">
                                                        <div class="input-wrap">
                                                            <select class="zone-select theme-input-style"
                                                                    name="zone_ids[]" id="zone_selector__select"
                                                                    multiple required>
                                                                <option value="all">{{translate('Select All')}}</option>
                                                                @foreach($zones as $zone)
                                                                    <option
                                                                        value="{{$zone->id}}" {{in_array($zone->id,$employee->zones->pluck('id')->toArray())?'selected':''}}>{{$zone->name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="d-flex flex-column gap-1 align-items-center">
                                                    <div class="input-wrap">
                                                        <div class="d-flex flex-column align-items-center gap-3">
                                                            <div class="text-muted">{{translate('Employee Image')}}
                                                                (1:1) <span class="text-danger">*</span></div>
                                                            <div class="d-flex flex-column align-items-center">
                                                                <div class="upload-file">
                                                                    <input type="file" class="upload-file__input"
                                                                           name="profile_image"
                                                                           accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*">
                                                                    <div class="upload-file__img">
                                                                        <img class="onerror-image"
                                                                             src="{{onErrorImage($employee->profile_image, asset('storage/app/public/employee/profile').'/' . $employee->profile_image,
                                                                    asset('public/assets/admin-module/img/media/upload-file.png') ,'employee/profile/')}}"
                                                                             alt="{{ translate('profile_image') }}">
                                                                    </div>
                                                                    <span class="upload-file__edit">
                                                                            <span class="material-icons">edit</span>
                                                                        </span>
                                                                </div>
                                                            </div>
                                                            <p class="opacity-75 max-w220 mx-auto text-center fs-12">
                                                                {{translate('Upload jpg, png, jpeg, gif maximum 2 MB')}}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-column gap-1 mb-20">
                                            <h4>{{translate('Business_Information')}}</h4>
                                            <p>{{translate('Give verified information to verify a employee')}}</p>
                                        </div>

                                        <hr class="mb-30">

                                        <div class="row">
                                            <div class="col-lg-6 mb-30">
                                                <div class="input-wrap">
                                                    @php($id_types=['passport','driving_license','nid','trade_license'])
                                                    <select class="select-identity theme-input-style"
                                                            name="identity_type" required>
                                                        <option value="0" selected
                                                                disabled>{{translate('Select_Identity_Type')}}</option>
                                                        @foreach($id_types as $type)
                                                            <option
                                                                value="{{$type}}" {{$type==$employee->identification_type?'selected':''}}>{{translate($type)}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="input-wrap form-floating form-floating__icon mt-30">
                                                    <input type="text" class="form-control" name="identity_number"
                                                           placeholder="{{translate('Identity Number')}}"
                                                           value="{{$employee->identification_number}}" required>
                                                    <label>{{translate('Identity_Number')}}</label>
                                                    <span class="material-icons">badge</span>
                                                </div>
                                            </div>
                                            <div class="col-lg-6 mb-30">
                                                <div class="input-wrap">
                                                    <div class="d-flex flex-column align-items-center gap-3">
                                                        <div class="text-muted">{{translate('Identification_Image')}}
                                                            (2:1) <span class="text-danger">*</span></div>
                                                        <div id="multi_image_picker" class="w-100">
                                                            @foreach($employee->identification_image as $img)
                                                                <img class="p-1" height="150"
                                                                     src="{{onErrorImage($img, asset('storage/app/public/employee/identity').'/' . $img,
                                                                    asset('public/assets/admin-module/img/media/provider-id.png') ,'employee/identity/')}}"
                                                                     alt="{{ translate('identity-image') }}">
                                                            @endforeach
                                                        </div>
                                                        <p class="opacity-75 max-w220 mx-auto text-center fs-12">
                                                            {{translate('Upload jpg, png, jpeg, gif maximum 2 MB')}}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-column gap-1 mb-20">
                                            <h4>{{translate('Account_Information')}}</h4>
                                            <p>{{translate('This info will need for employee’s future login')}}</p>
                                        </div>

                                        <hr class="mb-30">

                                        <div class="row">
                                            <div class="col-lg-4 mb-30">
                                                <div class="input-wrap form-floating form-floating__icon">
                                                    <input type="email" class="form-control" name="email"
                                                           placeholder="{{translate('Email_*')}}"
                                                           value="{{$employee['email']}}" required>
                                                    <label>{{translate('Email_*')}}</label>
                                                    <span class="material-icons">mail</span>
                                                </div>
                                            </div>
                                            <div class="col-lg-4 mb-30">
                                                <div class="input-wrap form-floating form-floating__icon">
                                                    <input type="password" class="form-control" name="password" value=""
                                                           placeholder="{{translate('Password')}}" id="pass">
                                                    <label>{{translate('Password')}}</label>
                                                    <span class="material-icons togglePassword">visibility_off</span>
                                                    <span class="material-icons">lock</span>
                                                </div>
                                                <small
                                                    class="text-danger d-flex mb-30 mt-1">{{translate('Password_Must_be_at_Least_8_Digits')}}</small>
                                            </div>
                                            <div class="col-lg-4 mb-30">
                                                <div class="input-wrap form-floating form-floating__icon">
                                                    <input type="password" class="form-control" name="confirm_password"
                                                           value=""
                                                           placeholder="{{translate('Confirm_Password')}}"
                                                           id="confirm_password">
                                                    <label>{{translate('Confirm_Password')}}</label>
                                                    <span class="material-icons togglePassword">visibility_off</span>
                                                    <span class="material-icons">lock</span>
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                    <div>
                                        <div class="d-flex gap-1 flex-column">
                                            <h4>{{translate('Set Permissions')}}</h4>
                                            <p class="fs-12">{{translate('Set what individuals on this role can do')}}</p>
                                        </div>
                                    </div>
                                    <section class="step2 active">
                                        <div class="d-flex flex-column gap-1 mb-20">
                                            <h4>{{translate('Set_Permission')}}</h4>
                                            <p>{{translate('Modify what individuals on this role can do')}}</p>
                                        </div>
                                        <div class="role-access-permission">
                                        </div>
                                    </section>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{asset('public/assets/provider-module')}}/plugins/jquery-steps/jquery.steps.min.js"></script>
    <script src="{{asset('public/assets/provider-module')}}/plugins/jquery-validation/jquery.validate.min.js"></script>
    <script>
        "use strict";

        $(document).ready(function () {
            const id = '{{$employee->id}}';
            const roleId = $('.role-btn').val();
            permission(roleId, id);

            $('.role-btn').change(function () {
                const roleId = $(this).val();
                permission(roleId, id);
            });
        });

        function permission(roleId, id) {
            $.ajax({
                url: '{{route('admin.employee.ajax.employee.role.access')}}',
                method: 'GET',
                data: {role_id: roleId, id: id},
                success: function (response) {
                    $('.role-access-permission').html(response.html);
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        }


        var form = $("#add-new-employee-form");
        form.children("div").steps({
            headerTag: "div",
            bodyTag: "section",
            transitionEffect: "slideLeft",
            startIndex: 1,
            onStepChanging: function (event, currentIndex, newIndex) {
                return true;
            },
            onFinishing: function (event, currentIndex) {

                form.submit();
            },
            onFinished: function (event, currentIndex) {
                form.submit();
            }
        });
    </script>

    <script src="{{asset('public/assets/admin-module')}}/js/spartan-multi-image-picker.js"></script>
    <script>
        "use strict";

        $('#zone_selector__select').on('change', function () {
            var selectedValues = $(this).val();
            if (selectedValues !== null && selectedValues.includes('all')) {
                $(this).find('option').not(':disabled').prop('selected', 'selected');
                $(this).find('option[value="all"]').prop('selected', false);
            }
        });

        const identificationImageCount = {{ count($employee->identification_image) }};
        let maxCount;

        if (identificationImageCount === 0) {
            maxCount = 2;
        } else if (identificationImageCount === 1) {
            maxCount = 1;
        } else if (identificationImageCount === 2) {
            maxCount = 0;
        }

        $("#multi_image_picker").spartanMultiImagePicker({
                fieldName: 'identity_images[]',
                maxCount: maxCount,
                rowHeight: '170px',
                groupClassName: 'item',
                maxFileSize: '2097152',
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
                onRemoveRow: function (index) {
                },
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
            }
        );
    </script>

    <script src="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/js/section/employee/custom.js"></script>
@endpush
