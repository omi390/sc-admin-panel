@extends('adminmodule::layouts.master')

@section('title',translate('Subscription Package Details'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.css"/>

@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-30">
                <h2 class="page-title">{{translate('Subscription_Package')}}</h2>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                <ul class="nav nav--tabs">
                    <li class="nav-item">
                        <a class="nav-link {{request()->is('admin/subscription/package/details/*') ? 'active' : ''}}" href="{{ route('admin.subscription.package.details',[request('id')]) }}">{{translate('Package_Details')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{request()->is('admin/subscription/package/transactions') ? 'active' : ''}}" href="{{ route('admin.subscription.package.transactions') }}?package_id={{ request('id') }}">{{translate('Transactions')}}</a>
                    </li>
                </ul>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <div class="">
                            <h4 class="mb-1">{{translate('Overview')}}</h4>
                            <p class="fs-12">{{translate('See overview of all the packages')}}</p>
                        </div>
                        <div class="min-w180">
                            <form id="dateRangeForm" action="{{ url()->current() }}" method="get">
                                <select name="date_range" id="date_range" class="js-select form-select">
                                    <option value="all_time" selected >{{translate('All_Time')}}</option>
                                    <option value="this_year" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='this_year'?'selected':''}}>{{translate('This_year')}}</option>
                                    <option value="this_month" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='this_month'?'selected':''}}>{{translate('This_Month')}}</option>
                                    <option value="this_week"  {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='this_week'?'selected':''}}>{{translate('This_Week')}}</option>
                                </select>
                            </form>
                        </div>
                    </div>

                    <div class="row mb-4 g-4">
                        <div class="col-lg-3 col-sm-6">
                            <div class="business-summary style__two">
                                <h2>{{ $packageSubscribers->count() }}</h2>
                                <h3>{{translate('Total Subscription')}}</h3>
                                <img width="35" src="{{asset('public/assets/admin-module')}}/img/icons/ov1.png"
                                     class="absolute-img"
                                     alt="">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="business-summary style__two success">
                                <h2>{{ $packageSubscribers->where('package_end_date', '>' , \Carbon\Carbon::now()->subDay())->count() }}</h2>
                                <h3>{{translate('Active_Subscriptions')}}</h3>
                                <img width="35" src="{{asset('public/assets/admin-module')}}/img/icons/ov2.png"
                                     class="absolute-img" alt="">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="business-summary style__two danger">
                                <h2>{{ $packageSubscribers->where('package_end_date', '<' , \Carbon\Carbon::now()->subDay())->count() }}</h2>
                                <h3>{{translate('Expired_Subscription')}}</h3>
                                <img width="35" src="{{asset('public/assets/admin-module')}}/img/icons/ov3.png"
                                     class="absolute-img"
                                     alt="">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="business-summary style__two warning">
                                <h2>{{$warningSubscribersCount}}</h2>
                                <h3 class="d-flex gap-2 align-items-center">
                                    {{translate('Expiring_Soon ')}}
                                    <i class="material-symbols-outlined cursor-pointer title-color" data-bs-toggle="tooltip" title="Expired soon warning is base on upcoming {{$deadlineWarning}} days.">info</i>

                                </h3>
                                <img width="35" src="{{asset('public/assets/admin-module')}}/img/icons/ov4.png"
                                     class="absolute-img"
                                     alt="">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-4 g-4">
                        <div class="col-lg-4 col-sm-6">
                            <div class="statistics-card p-3 style__two">
                                <div class="d-flex justify-content-between gap-2">
                                    <div class="media gap-2 align-items-center">
                                        <img width="20" src="{{asset('public/assets/admin-module')}}/img/icons/ov5.png" alt="">
                                        <h6 class="meida-body title-color">{{translate('In Free Trial')}}</h6>
                                    </div>
                                    <h4 class="text-success h5 fw-bold mb-0">{{ $freeTrialCount }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6">
                            <div class="statistics-card p-3 style__two">
                                <div class="d-flex justify-content-between gap-2">
                                    <div class="media gap-2 align-items-center">
                                        <img width="20" src="{{asset('public/assets/admin-module')}}/img/icons/ov6.png" alt="">
                                        <h6 class="meida-body title-color">{{translate('Total Renewed')}}</h6>
                                    </div>
                                    <h4 class="c1 h5 fw-bold mb-0">{{ with_currency_symbol($totalRenewPrice) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6">
                            <div class="statistics-card p-3 style__two">
                                <div class="d-flex justify-content-between gap-2">
                                    <div class="media gap-2 align-items-center">
                                        <img width="20" src="{{asset('public/assets/admin-module')}}/img/icons/ov7.png" alt="">
                                        <h6 class="meida-body title-color">{{translate('Total Earning')}}</h6>
                                    </div>
                                    <h4 class="text-danger h5 fw-bold mb-0">{{ with_currency_symbol($totalEarning) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h4 class="d-flex align-items-center gap-2">
                            <img width="20" src="{{asset('public/assets/admin-module/img/icons/ov11.png')}}" alt="">
                            {{translate('Package_Overview')}}
                        </h4>
                        <div class="d-flex align-items-center flex-wrap justify-content-between gap-3">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                {{translate('Status')}}
                                <label class="switcher">
                                    <input
                                        class="switcher_input @if(!$subscriptionPackage->is_active) switch-alert @else modal-status @endif"
                                           type="checkbox"
                                           @if($subscriptionPackage->is_active)data-bs-toggle="modal"
                                           data-bs-target="#offStatus" @endif
                                           id="{{explode(' ', trim($subscriptionPackage->name))[0]}}"
                                           {{$subscriptionPackage->is_active? 'checked' :''}}
                                           data-status="{{$subscriptionPackage->id}}"
                                           data-id="{{explode(' ', trim($subscriptionPackage->name))[0]}}"
                                           data-name="{{ $subscriptionPackage?->name }}">
                                    <span class="switcher_control"></span>
                                </label>
                            </div>
                            <a type="button" href="{{ route('admin.subscription.package.edit',[$subscriptionPackage->id]) }}" class="btn btn--primary">
                                <span class="material-symbols-outlined">border_color</span>
                                {{translate('Edit')}}
                            </a>
                        </div>
                    </div>

                    <div class="c1-light-bg radius-10 p-lg-4 p-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-30">
                            <div class="">
                                <h4 class="h4 mb-1 c1 fw-bold">{{ucwords($subscriptionPackage->name)}}</h4>
                                <h6>{{$subscriptionPackage->description}}</h6>
                            </div>
                            <div class="">
                                <strong class="h4 title-color">{{ with_currency_symbol($subscriptionPackage->price) }} / </strong> <span class="h6 fw-medium">{{$subscriptionPackage->duration}} days</span>
                            </div>
                        </div>

                        <div class="grid-columns">
                            @foreach(PACKAGE_FEATURES as $feature)
                                @php
                                    $featureExists = $subscriptionPackage->subscriptionPackageFeature->contains(function ($value) use ($feature) {
                                        return $value->feature == $feature['key'];
                                    });
                                @endphp

                                @if($featureExists)
                                    <div class="d-flex gap-2 lh-1 align-items-center">
                                        <span class="material-icons c1 fs-16">check_circle</span>
                                        <span>{{ $feature['value'] }}</span>
                                    </div>
                                @endif
                            @endforeach

                            @php
                                $bookingCheck = $subscriptionPackage->subscriptionPackageLimit->where('key', 'booking')->first();
                                $categoryCheck = $subscriptionPackage->subscriptionPackageLimit->where('key', 'category')->first();
                                $isBookingLimit = $bookingCheck?->is_limited;
                                $isCategoryLimit = $categoryCheck?->is_limited;
                            @endphp
                            @if($isBookingLimit == 0)
                                <div class="d-flex gap-2 lh-1 align-items-center">
                                    <span class="material-icons c1 fs-16">check_circle</span>
                                    <span>{{ translate('Unlimited Booking') }}</span>
                                </div>
                            @else
                                <div class="d-flex gap-2 lh-1 align-items-center">
                                    <span class="material-icons c1 fs-16">check_circle</span>
                                    <span>{{$bookingCheck->limit_count}}{{ translate(' Booking') }}</span>
                                </div>
                           @endif
                            @if($isCategoryLimit == 0)
                                <div class="d-flex gap-2 lh-1 align-items-center">
                                    <span class="material-icons c1 fs-16">check_circle</span>
                                    <span>{{ translate('Unlimited Service Sub Category') }}</span>
                                </div>
                            @else
                                <div class="d-flex gap-2 lh-1 align-items-center">
                                    <span class="material-icons c1 fs-16">check_circle</span>
                                    <span>{{$categoryCheck->limit_count}}{{ translate(' Service Sub Category') }}</span>
                                </div>
                            @endif
                        </div>
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
                        <h3 class="mb-2">{{ translate('Are You Sure You want To Off The Status?')}}</h3>
                        <p>{{ translate('You are about to deactivate a subscription package. You have the option to either switch all providers plans or allow providers to make changes. Please choose an option below to proceed.')}}</p>
                        <div class="d-flex gap-3 justify-content-center flex-wrap">
                            <form action="" method="post">
                                @csrf
                                <button type="submit" class="btn btn-outline--primary text-capitalize">{{ translate('Allow Provider to Change')}}</button>
                            </form>
                            <button type="button" class="btn btn--primary text-capitalize" data-bs-toggle="modal"
                                    data-bs-target="#chooseSubscription">{{ translate('Switch Plan')}}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="chooseSubscription" tabindex="-1" aria-labelledby="chooseSubscriptionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-30">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="d-flex flex-column gap-2 align-items-center text-center">
                        <img class="mb-3" src="{{asset('public/assets/admin-module')}}/img/ad_delete.svg" alt="">
                        <h3 class="mb-2">{{ translate('Switch existing business plan')}}</h3>
                        <p class="old-subscription-name" id="old_subscription_name"></p>
                        <form action="{{ route('admin.subscription.package.change-subscription') }}" method="post" class="w-100">
                            @csrf
                            <input type="hidden" name="old_subscription" id="old_subscription" value="">
                            <div class="choose-option">
                                <div class="text-start">
                                    <label class="test-start my-2">{{ translate('Business Plan') }}</label>
                                    <select class="form-select mb-3 js-select-modal" name="new_subscription" id="choose_subscription">
                                        <option selected>{{ translate('Select Plan') }}</option>
                                        @foreach($subscriptions as $subscription)
                                            <option value="{{ $subscription->id }}">{{ $subscription->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="d-flex gap-3 justify-content-center flex-wrap my-3">
                                    @csrf
                                    <button type="submit" class="btn btn--primary text-capitalize">{{ translate('Switch & Turn Off The Status')}}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('script')
    <script src="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.js"></script>

    <script>
        "use strict"

        let currentCheckbox;

        $('.modal-status').on('click', function(event) {
            currentCheckbox = $(this);
        });

        $(document).ready(function () {
            $('.zone__select').select2({
                placeholder: "{{translate('Select_Zone')}}",
            });
        });

        $('.js-select-modal').select2({
            dropdownParent: $('#chooseSubscription')
        });

        $('.switch-alert').on('click', function () {
            let itemId = $(this).data('status');
            let status = $(this).is(':checked') === true ? 1 : 0;
            let id = $(this).data('id');
            let route = '{{ route('admin.subscription.package.status-update', ['id' => ':itemId']) }}';
            route = route.replace(':itemId', itemId);
            route_alert_reload(route, '{{ translate('want_to_update_status') }}', true, status, id);
        })

        $('#offStatus').on('show.bs.modal', function (event) {
            const input = $(event.relatedTarget);
            const itemId = input.data('status');
            let route = '{{ route('admin.subscription.package.status-update', ['id' => ':itemId']) }}';
            route = route.replace(':itemId', itemId);
            const editModel = $(this);
            editModel.find('form').attr('action',route);
        });

        $('#offStatus').on('hidden.bs.modal', function () {
            if (currentCheckbox) {
                const status = currentCheckbox.is(':checked') === true ? 1 : 0;
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

        $('#date_range').change(function() {
            $('#dateRangeForm').submit();
        });

        $(document).ready(function () {
            $('.modal-status').on('click', function () {
                var statusId = $(this).data('status');
                var name = $(this).data('name');

                $('#old_subscription').val(statusId);
                $('#old_subscription_name').html(name);

                $('#choose_subscription').find('option').each(function () {
                    if ($(this).val() === statusId) {
                        $(this).remove();
                    }
                });
            });
        });
    </script>
@endpush
