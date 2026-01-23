@extends('adminmodule::layouts.master')

@section('title',translate('Subscription Package list'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('/public/assets/landing/css/owl.min.css')}}" />
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.css"/>

    <style>
        .modal-status {
            position: absolute;
            inset-block-start: 0;
            inset-inline-start: 0;
            inline-size: 2.25rem;
            block-size: 1.125rem;
            transition: background-color 0.15s ease-in;
            background-color: #ced7dd;
        }
        .modal-status::after {
            content: "";
            position: absolute;
            inset-block-start: 0.0625rem;
            inset-inline-start: 0.0625rem;
            inline-size: 1rem;
            block-size: 1rem;
            transition: left 0.15s ease-in;
            background-color: var(--absolute-white);
            border-radius: 50rem;
        }
        .modal-status:checked ~ .switcher_control {
            background-color: var(--c1);
        }
        .modal-status:checked ~ .switcher_control:after {
            inset-inline-start: 1.1875rem;
        }
    </style>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap d-flex gap-2 align-items-center mb-30">
                <h2 class="page-title">{{translate('Subscription_Package_List')}}</h2>
                @if($subscriptionPackage->count() > 0)
                    <span class="badge badge-primary fw-semi bold">{{ $subscriptionPackage->total() }}</span>
                @endif
            </div>
            @if($subscriptionPackageCount > 0)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <div class="">
                            <h4 class="mb-1">{{ translate('Overview') }}</h4>
                            <p class="fs-12">{{ translate('See overview of all the packages') }}</p>
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
                    <div class="billing-card-slider owl-theme owl-carousel">
                        @php
                            $styles = ['overview-card', 'overview-card style__two', 'overview-card style__three', 'overview-card style__four'];
                        @endphp
                        @forelse($subscriptionPackage as $key => $subscription)
                            <div class="billing-card-slider-item">
                                @php
                                    $styleClass = $styles[$key % count($styles)];
                                @endphp
                                <div class="{{ $styleClass }} text-center d-flex flex-column gap-2 align-items-center">
                                    <div class="img-circle mx-auto mb-2">
                                        <img src="{{asset('public/assets/admin-module/img/icons/oc1.svg')}}" class="svg" alt="{{ translate('basic') }}">
                                    </div>
                                    <h5>{{ $subscription?->name }}</h5>
                                    <h5 class="h3 fw-bold overview-card__title">{{ with_currency_symbol($subscription?->price) }}</h5>
                                </div>
                            </div>
                        @empty
                            <div class="text-center">
                                <p>{{translate('no_data_available')}}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                        <form action="{{url()->current()}}" class="search-form search-form_style-two" method="get">
                            <div class="input-group search-form__input_group">
                                <span class="search-form__icon">
                                    <span class="material-icons">search</span>
                                </span>
                                <input type="hidden" name="date_range" value="{{ $queryParams['date_range'] }}">
                                <input type="search" class="theme-input-style search-form__input" name="search" value="{{$search}}" placeholder="{{translate('Search Packages')}}">
                            </div>
                            <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                        </form>

                        <div class="d-flex flex-wrap align-items-center gap-3">
                            @can('subscription_package_add')
                            <a type="button" href="{{ route('admin.subscription.package.create') }}" class="btn btn--primary">
                                <span class="material-icons">add</span>
                                {{translate('Add Subcription Package')}}
                            </a>
                            @endcan

                            @can('subscription_package_export')
                            <div class="dropdown">
                                <button type="button" class="btn btn--secondary text-capitalize dropdown-toggle" data-bs-toggle="dropdown">
                                    <span class="material-icons">file_download</span>
                                    {{translate('download')}}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                    <a class="dropdown-item" href="{{route('admin.subscription.package.download')}}?search={{$search}}">
                                        {{translate('excel')}}
                                    </a>
                                </ul>
                            </div>
                            @endcan
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="example" class="table align-middle">
                            <thead class="align-middle">
                                <tr>
                                    <th>{{translate('Sl')}}</th>
                                    <th>{{translate('Package Name')}}</th>
                                    <th>{{translate('Pricing')}}</th>
                                    <th>{{translate('Duration')}}</th>
                                    <th>{{translate('Current Subscriber')}}</th>
                                    <th>{{translate('Status')}}</th>
                                    @canany(['subscription_package_edit', 'subscription_package_view'])
                                        <th class="text-center">{{translate('Action')}}</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($subscriptionPackage as $key => $subscription)
                                <tr>
                                    <td>{{ $subscriptionPackage->firstItem() + $key }}</td>
                                    <td>{{ $subscription?->name }}</td>
                                    <td>{{ with_currency_symbol($subscription?->price) }}</td>
                                    <td>{{ $subscription?->duration }} days</td>
                                    <td>{{ $subscription->subscriber_count }}</td>
                                    <td>
                                        <label class="switcher">
                                            <input class="@if(!$subscription?->is_active) switcher_input @else modal-status @endif"
                                                   type="checkbox"
                                                   @if($subscription?->is_active)
                                                       data-bs-toggle="modal"
                                                   data-bs-target="#offStatus"
                                                   @endif
                                                   id="{{explode(' ', trim($subscription?->name))[0]}}"
                                                   {{$subscription?->is_active ? 'checked' : ''}}
                                                   data-status="{{$subscription?->id}}"
                                                   data-id="{{explode(' ', trim($subscription?->name))[0]}}"
                                                   data-name="{{ $subscription?->name }}">

                                            <span class="switcher_control"></span>
                                        </label>
                                    </td>

                                    @canany(['subscription_package_edit', 'subscription_package_view'])
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                @can('subscription_package_update')
                                                    <a href="{{ route('admin.subscription.package.edit',[$subscription->id]) }}" class="action-btn btn--success" style="--size: 30px">
                                                        <span class="material-icons">edit</span>
                                                    </a>
                                                @endcan
                                                @can('subscription_package_view')
                                                    <a href="{{ route('admin.subscription.package.details',[$subscription->id]) }}" class="action-btn btn--light-primary" style="--size: 30px">
                                                        <span class="material-icons">visibility</span>
                                                    </a>
                                                @endcan
                                            </div>
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14"><p class="text-center">{{translate('no_data_available')}}</p></td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end">
                        {!! $subscriptionPackage->links() !!}
                    </div>
                </div>
            </div>
            @else
            <div class="card mt-3">
                <div class="card-body py-5">
                    <div class="d-flex flex-column align-items-center text-center gap-2">
                        <img src="{{asset('public/assets/admin-module/img/create-plan.svg')}}" class="svg mb-3" alt="">
                        <h3>{{ translate('Create Subscription Plan')}}</h3>
                        <p class="max-w500">{{ translate('Add new subscription packages to the list. So that Providers get more options to join the business for the growth and success.')}}</p>
                        <a type="button" href="{{ route('admin.subscription.package.create') }}" class="btn btn--primary">
                            <span class="material-icons">add</span>
                            {{translate('Add Subcription Package')}}
                        </a>
                    </div>
                </div>
            </div>
            @endif
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
                                <button type="submit" class="btn btn-outline--primary text-capitalize">{{ translate('Allow provider to change')}}</button>
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
                                        <?php
                                        $ActiveSubscriptionPackages = Modules\BusinessSettingsModule\Entities\SubscriptionPackage::ofStatus(1)->get();
                                        ?>
                                    <label class="test-start my-2">{{ translate('Business Plan') }}</label>
                                    <select class="form-select mb-3 js-select-modal" name="new_subscription" id="choose_subscription">
                                        <option value="" selected>{{ translate('select_plan') }}</option>
                                        @foreach($ActiveSubscriptionPackages as $package)
                                        <option value="{{ $package->id }}">{{ $package->name}}</option>
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

    <script src="{{asset('/public/assets/landing/js/owl.min.js')}}"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            $('.billing-card-slider').owlCarousel({
                loop: false,
                margin: 30,
                nav: false,
                dots: false,
                autoWidth: true
            })
        })
    </script>

    <script>
        "use strict";
        $('.js-select-modal').select2({
            dropdownParent: $('#chooseSubscription')
        });

            let currentCheckbox;

            $('.modal-status').on('click', function(event) {
                currentCheckbox = $(this);
            });

            $('.switcher_input').on('click', function () {
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
