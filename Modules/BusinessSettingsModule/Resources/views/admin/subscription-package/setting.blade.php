@extends('adminmodule::layouts.master')

@section('title',translate('Subscription Setting'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/toaster/simple-notify.min.css')}}"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h3>{{translate('Subscription Setting')}}</h3>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                        <div class="">
                            <h4 class="mb-1">{{translate('Offer Free Trial')}}</h4>
                            <p class="fs-12">{{translate('You can offer providers a free trial to experience the system overall.')}}</p>
                        </div>
                        @can('subscription_settings_manage_status')
                            <div class="d-flex gap-3 align-items-center">
                                <h5 class="text-muted">{{translate('Status')}}:</h5>
                                <label class="switcher">
                                    <input class="switcher_input" type="checkbox" data-bs-toggle="modal"
                                           data-bs-target="#offStatus" name="free_trial_status"
                                           {{ $freeTrialStatus ? 'checked' : '' }} data-key="free_trial_period"
                                           data-id="free_trial_period" id="free_trial_period">
                                    <span class="switcher_control"></span>
                                </label>
                            </div>
                        @endcan
                    </div>

                    <div class="border rounded p-3 p-lg-4 mb-30">
                        <form action="" method="post">
                            @csrf
                            <input type="hidden" name="trial" value="trial">
                            <div class="d-flex flex-wrap gap-3 gap-lg-5">
                                <div class="input-wrap form-floating form-floating__icon flex-grow-1 mw-632">
                                    <input type="number" class="form-control" name="free_trial_period" min="1" max="99999999999" placeholder=""
                                           value="{{ $freeTrialPeriod }}" required="">
                                    <label>{{translate('Free Trial Period')}}</label>
                                    <span class="material-icons">date_range</span>
                                </div>
                                <div class="min-w180">
                                    <select name="free_trial_type" id="free_trial_type" class="js-select form-select"
                                            required>
                                        <option
                                            value="day" {{ $freeTrialType == 'day' ? 'selected' : '' }}>{{translate('Day')}}</option>
                                        <option
                                            value="month" {{ $freeTrialType == 'month' ? 'selected' : '' }}>{{translate('Month')}}</option>
                                    </select>
                                </div>
                                @can('subscription_settings_update')
                                    <button type="submit"
                                            class="btn btn--primary px-xl-5 ms--auto">{{translate('Submit')}}</button>
                                @endcan
                            </div>
                        </form>
                    </div>

                    <div class="mb-4">
                        <h4 class="mb-1">{{translate('Show Deadline Warning')}}</h4>
                        <p class="fs-12">{{translate('Select the number of days before the warning will be shown with a countdown to the end of all packages')}}</p>
                    </div>

                    <div class="border rounded p-3 p-lg-4">
                        <form action="" method="post">
                            @csrf
                            <input type="hidden" name="warning" value="warning">
                            <div class="d-flex flex-wrap gap-3 gap-lg-5">
                                <div class="input-wrap form-floating form-floating__icon flex-grow-1">
                                    <input type="number" class="form-control" name="deadline_warning" placeholder="Days" min="1"
                                           max="99999999999" value="{{ $deadlineWarning }}" required="">
                                    <label>{{translate('Days')}}</label>
                                    <span class="material-icons">date_range</span>
                                </div>
                                <div class="input-wrap form-floating form-floating__icon flex-grow-1">
                                    <input type="text" class="form-control" name="deadline_warning_message"
                                           placeholder="Type Message" value="{{ $deadlineWarningMessage }}" required="">
                                    <label>{{translate('Type Message')}}</label>
                                    <span class="material-icons">title</span>
                                </div>
                                @can('subscription_settings_update')
                                    <button type="submit"
                                            class="btn btn--primary px-xl-5">{{translate('Submit')}}</button>
                                @endcan
                            </div>
                        </form>
                    </div>

                    <div class="mb-4 mt-4">
                        <h4 class="mb-1">{{translate('Return Money Restriction')}}</h4>
                        <p class="fs-12">{{translate('Setup  the amount after which if any provider change / migrate the subscription plan you won’t return any money back')}}</p>
                    </div>

                    <div class="border rounded p-3 p-lg-4">
                        <form action="" method="post">
                            @csrf
                            <input type="hidden" name="return" value="return">
                            <div class="d-flex flex-wrap gap-3 gap-lg-5">
                                <div class="input-wrap form-floating form-floating__icon flex-grow-1">
                                    <input type="text" class="form-control" name="usage_time" value="{{ $usageTime }}"
                                           required="" placeholder="">
                                    <label>{{translate('Select subscription usage time (%)')}}</label>
                                    <span class="material-icons">schedule</span>
                                </div>
                                @can('subscription_settings_update')
                                    <button type="submit"
                                            class="btn btn--primary px-xl-5">{{translate('Submit')}}</button>
                                @endcan
                            </div>
                        </form>
                    </div>

                    <div class="mb-4 mt-4">
                        <h4 class="mb-1">{{translate('Vat')}}</h4>
                        <p class="fs-12">{{translate('Set up the threshold amount after which, if any provider changes or migrates the subscription plan, no refund will be issued')}}</p>
                    </div>

                    <div class="border rounded p-3 p-lg-4">
                        <form action="" method="post">
                            @csrf
                            <input type="hidden" name="vat" value="vat">
                            <div class="d-flex flex-wrap gap-3 gap-lg-5">
                                <div class="input-wrap form-floating form-floating__icon flex-grow-1">
                                    <input type="text" class="form-control" name="subscription_vat"
                                           value="{{ $subscriptionVat }}" placeholder="" required="">
                                    <label>{{translate('Select subscription vat (%)')}}</label>
                                    <span class="material-icons">price_change</span>
                                </div>
                                @can('subscription_settings_update')
                                    <button type="submit"
                                            class="btn btn--primary px-xl-5">{{translate('Submit')}}</button>
                                @endcan
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="offStatus" tabindex="-1" aria-labelledby="offStatusLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-30">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="d-flex flex-column gap-2 align-items-center text-center">
                        <img class="mb-3" src="{{asset('public/assets/admin-module')}}/img/ad_delete.svg" alt="">
                        <h3 class="mb-2">{{ translate('Are You Sure ?')}}</h3>
                        <p>{{ translate('want_to_update_status')}}</p>
                        <div class="d-flex gap-3 justify-content-center flex-wrap">
                            <form action="" method="post">
                                @csrf
                                <input type="hidden" name="key">
                                <button type="submit"
                                        class="btn btn--primary text-capitalize">{{ translate('Update')}}</button>
                            </form>
                            <button type="button" class="btn btn-dark text-capitalize" data-bs-dismiss="modal"
                                    aria-label="Close">{{ translate('Close')}}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        "use strict";

        $('#offStatus').on('show.bs.modal', function (event) {
            const input = $(event.relatedTarget);
            const key = input.data('key');
            const editModel = $(this);
            editModel.find('input[name=key]').val(key);
            editModel.find('form');
        });

        $('#offStatus').on('hidden.bs.modal', function () {
            if (currentCheckbox) {
                const key = currentCheckbox.is(':checked') === true ? 1 : 0;
                const id = currentCheckbox.data('id');
                if (status === 1) {
                    $(`#${id}`).prop('checked', false);
                }
                if (status === 0) {
                    $(`#${id}`).prop('checked', true);
                }
                currentCheckbox = null;
            }
        });
    </script>
@endpush
