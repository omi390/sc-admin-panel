@extends('adminmodule::layouts.master')

@section('title', translate('role_update'))

@push('css_or_js')
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('role_update')}}</h2>
                    </div>

                    <div class="card mb-30">
                        <div class="card-body p-30">
                            <form action="{{route('admin.role.update',[$role->id])}}" method="POST">
                                @csrf
                                @method('put')
                                <div class="row">
                                    <div class="col-lg-6 mb-4 mb-lg-0">
                                        <div class="form-floating form-floating__icon mb-30">
                                            <input type="text" class="form-control" name="role_name" value="{{$role->role_name}}"
                                                   placeholder="{{translate('role_name')}} *" required="">
                                            <label>{{translate('role_name')}} *</label>
                                            <span class="material-icons">subtitles</span>
                                        </div>
                                    </div>

                                    <div class="col-12 access-checkboxes">
                                        <div class="mb-4">
                                            <div class="d-flex gap-4 align-items-center mb-1">
                                                <h4>{{translate('Permissions / Accesses')}}</h4>

                                                <div class="bg--secondary px-2 py-1 rounded d-flex gap-1 align-items-center">
                                                    <input class="" type="checkbox" value="" id="select_all">
                                                    <label class="user-select-none" for="select_all">{{translate('Select_All')}}</label>
                                                </div>
                                            </div>
                                            <p>{{translate('Select the  options you want to give access to this role')}}</p>
                                        </div>
                                        <div class="row gy-3">
                                            @foreach(SYSTEM_MODULES as $module)
                                                @php
                                                    $matchedRoleAccess = $roleAccess->where('section_name', $module['key'])->first();
                                                @endphp
                                            @if(!isset($module['submodules']) && empty($module['submodules']))
                                                    <div class="col-sm-6 col-lg-3">
                                                        <div class="bg--secondary px-3 py-2 rounded d-flex gap-1 align-items-center">
                                                            <label class="user-select-none flex-grow-1" for="{{ $module['key'] }}">{{ $module['value'] }}</label>
                                                            <input type="checkbox" name="modules[{{ $module['key'] }}]" id="{{ $module['key'] }}" @if($matchedRoleAccess) checked @endif>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>

                                        <hr class="my-4">

                                        <div class="row g-4 mb-4">
                                            @foreach(SYSTEM_MODULES as $module)
                                                @if(isset($module['submodules']) && !empty($module['submodules']))
                                                    <div class="col-md-6">
                                                        <div class="card checkbox_card overflow-hidden">
                                                            <div class="checkbox_card__head bg--secondary p-3 d-flex gap-1 align-items-center">
                                                                <label class="user-select-none flex-grow-1" for="{{ $module['key'] }}">{{ $module['value'] }}</label>
                                                                <input class="" type="checkbox" name="section_modules[{{ $module['key'] }}]" id="{{ $module['key'] }}">
                                                            </div>

                                                            <div class="card-body">
                                                                <div class="grid-columns">
                                                                    @foreach($module['submodules'] as $submodule)
                                                                        @php
                                                                            $matchedRoleAccess = $roleAccess->where('section_name', $submodule['key'])->first();
                                                                        @endphp
                                                                        <div class="d-flex gap-1 align-items-center">
                                                                            <input class="mb-1" type="checkbox" name="modules[{{ $submodule['key'] }}]" id="{{ $submodule['key'] }}" @if($matchedRoleAccess) checked @endif>
                                                                            <label class="user-select-none flex-grow-1" for="{{ $submodule['key'] }}">{{ $submodule['value'] }}</label>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                    <h4 class="col-12 mb-3 mt-4">{{translate('Manage Access')}}</h4>
                                    <div class="table-responsive">
                                        <table class="table align-middle border-bottom">
                                            <thead class="text-nowrap">
                                            <tr>
                                                <th class="text-center">{{translate('Add')}}</th>
                                                <th class="text-center">{{translate('Update')}}</th>
                                                <th class="text-center">{{translate('Delete')}}</th>
                                                <th class="text-center">{{translate('Export')}}</th>
                                                <th class="text-center">{{translate('Status on/Off')}}</th>
                                                <th class="text-center">{{translate('Approve or Deny')}}</th>
                                                <th class="text-center">{{translate('Assign Serviceman')}}</th>
                                                <th class="text-center">{{translate('Give FeedBack')}}</th>
                                                <th class="text-center">{{translate('Take Backup')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>
                                                    <label class="switcher mx-auto">
                                                        <input class="switcher_input" name="add" type="checkbox" @if($roleAccessBtn?->can_add) checked @endif>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="switcher mx-auto">
                                                        <input class="switcher_input" name="update" type="checkbox" @if($roleAccessBtn?->can_update) checked @endif>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="switcher mx-auto">
                                                        <input class="switcher_input" name="delete" type="checkbox" @if($roleAccessBtn?->can_delete) checked @endif>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>

                                                <td>
                                                    <label class="switcher mx-auto">
                                                        <input class="switcher_input" name="export" type="checkbox" @if($roleAccessBtn?->can_export) checked @endif>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="switcher mx-auto">
                                                        <input class="switcher_input" name="status" type="checkbox" @if($roleAccessBtn?->can_manage_status) checked @endif>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="switcher mx-auto">
                                                        <input class="switcher_input" name="approve_or_deny" type="checkbox" @if($roleAccessBtn?->can_approve_or_deny) checked @endif>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="switcher mx-auto">
                                                        <input class="switcher_input" name="assign_serviceman" type="checkbox" @if($roleAccessBtn?->can_assign_serviceman) checked @endif>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="switcher mx-auto">
                                                        <input class="switcher_input" name="give_feedback" type="checkbox" @if($roleAccessBtn?->can_give_feedback) checked @endif>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="switcher mx-auto">
                                                        <input class="switcher_input" name="take_backup" type="checkbox" @if($roleAccessBtn?->can_take_backup) checked @endif>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="alert-show">
                                        <div class="alert alert-danger d-flex align-items-center alert-dismissible fade show mt-3 mb-0" role="alert">
                                            <div class="media gap-2">
                                                <img src="{{asset('public/assets/admin-module/img/WarningOctagon.svg')}}" class="svg" alt="">
                                                <div class="media-body">
                                                    {{translate('If no access is selected, employees with this role can only view the section for which permissions are granted; they cannot perform any actions.')}}
                                                </div>
                                            </div>
                                            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex justify-content-end gap-20 mt-5">
                                            <button class="btn btn--secondary"
                                                    type="reset">{{translate('reset')}}</button>
                                            <button class="btn btn--primary"
                                                    type="submit">{{translate('submit')}}</button>
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

@push('script')
    <script src="{{asset('public/assets/admin-module')}}/js/custom.js"></script>

    <script>
        "use strict";

        $('#select_all').on('change', function () {
            $(this).closest('.access-checkboxes').find('input[type="checkbox"]').prop('checked', this.checked);
        });

        function checkAllCheckboxes(card) {
            var allChecked = true;
            card.find('.card-body input[type="checkbox"]').each(function () {
                if (!$(this).prop('checked')) {
                    allChecked = false;
                    return false;
                }
            });
            return allChecked;
        }

        $('.checkbox_card__head input[type="checkbox"]').on('change', function () {
            var card = $(this).closest('.checkbox_card');
            card.find('input[type="checkbox"]').prop('checked', this.checked);
        });

        $('.checkbox_card input[type="checkbox"]').on('change', function () {
            var card = $(this).closest('.checkbox_card');
            var allChecked = checkAllCheckboxes(card);
            card.find('.checkbox_card__head input[type="checkbox"]').prop('checked', allChecked);
        });

        $('.checkbox_card').each(function () {
            var card = $(this);
            var allChecked = checkAllCheckboxes(card);
            card.find('.checkbox_card__head input[type="checkbox"]').prop('checked', allChecked);
        });

        function areAllInputsUnchecked() {
            var checkboxes = document.querySelectorAll('.table .switcher_input');
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    return false;
                }
            }
            return true;
        }

        function toggleAlertShow() {
            var alertShowDiv = document.querySelector('.alert-show');
            if (areAllInputsUnchecked()) {
                alertShowDiv.style.display = 'block';
            } else {
                alertShowDiv.style.display = 'none';
            }
        }

        var checkboxes = document.querySelectorAll('.table .switcher_input');
        checkboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                toggleAlertShow();
            });
        });
        toggleAlertShow();
    </script>
@endpush
