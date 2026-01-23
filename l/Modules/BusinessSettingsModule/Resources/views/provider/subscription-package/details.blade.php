@extends('providermanagement::layouts.master')

@section('title',translate('My Business Plan'))

@push('css_or_js')

@endpush

@section('content')
    <div class="main-content">
        @if($subscriptionDetails)
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{translate('My Business Plan')}}</h2>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                <ul class="nav nav--tabs">
                    <li class="nav-item">
                        <a class="nav-link {{request()->is('provider/subscription-package/details') ? 'active' : ''}}" href="{{ route('provider.subscription-package.details') }}">{{translate('Package_Details')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{request()->is('provider/subscription-package/transactions') ? 'active' : ''}}" href="{{ route('provider.subscription-package.transactions') }}">{{translate('Transactions')}}</a>
                    </li>
                </ul>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <img width="20" src="{{asset('public/assets/admin-module')}}/img/icons/billing.svg" class="svg" alt="">
                        <h3>{{translate('Billing')}}</h3>
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-4 col-sm-6">
                            <div class="overview-card after-w50 d-flex gap-3 align-items-center p-lg-4">
                                <div class="img-circle">
                                    <img width="34" src="{{asset('public/assets/admin-module/img/icons/b1.png')}}" alt="{{ translate('basic') }}">
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div>{{translate('Expire Date')}}</div>
                                    <h3 class="overview-card__title">{{ \Carbon\Carbon::parse($subscriptionDetails?->package_end_date)->format('d M Y') }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6">
                            <div class="overview-card style__three after-w50 d-flex gap-3 align-items-center p-lg-4">
                                <div class="img-circle">
                                    <img width="34" src="{{asset('public/assets/admin-module/img/icons/b2.png')}}" alt="{{ translate('basic') }}">
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div>{{translate('Next renewal Bill')}} <small>({{translate('Vat included')}})</small></div>
                                    <h3 class="overview-card__title">{{with_currency_symbol( $renewalPrice )}}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6">
                            <div class="overview-card style__two after-w50 d-flex gap-3 align-items-center p-lg-4">
                                <div class="img-circle">
                                    <img width="34" src="{{asset('public/assets/admin-module/img/icons/b3.png')}}" alt="{{ translate('basic') }}">
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div>{{translate('Total Subscription Taken')}}</div>
                                    <h3 class="overview-card__title">{{ $totalPurchase }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form action="#">
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <img width="20" src="{{asset('public/assets/admin-module/img/icons/ov11.png')}}" alt="">
                            <h3>{{translate('Package Overview')}}</h3>
                        </div>

                        <div class="c1-light-bg radius-10 p-lg-4 p-3">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-30">
                                <div class="">
                                    <h4 class="h4 mb-1 c1 fw-bold">{{ $subscriptionDetails?->package_name }}</h4>
                                    <h6>{{ $subscriptionDetails?->package->description }}</h6>
                                </div>
                                <div class="">
                                    <strong class="h4 title-color">{{with_currency_symbol($subscriptionDetails?->package_price - $subscriptionDetails?->vat_amount)}}/ </strong> <span class="h6 fw-medium">{{ $monthsDifference }} {{translate('days')}}</span>
                                </div>
                            </div>
                            <div class="grid-columns">

                                @foreach(PACKAGE_FEATURES as $feature)
                                    @php
                                        $featureExists = $subscriptionDetails?->feature->contains(function ($value) use ($feature) {
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

                                @if($isBookingLimit == 0)
                                    <div class="d-flex gap-2 lh-1 align-items-center">
                                        <span class="material-icons c1 fs-16">check_circle</span>
                                        <span>{{ translate('Unlimited Booking') }}</span>
                                    </div>
                                @else
                                    <div class="d-flex gap-2 lh-1 align-items-center">
                                        <span class="material-icons c1 fs-16">check_circle</span>
                                        <span>{{$bookingCheck->limit_count}}{{ translate(' Booking') }} <small>( {{ $subscriptionDetails->feature_limit_left['booking'] }}{{translate(' left )') }}</small></span>
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
                                        <span>{{$categoryCheck->limit_count}}{{ translate(' Service Sub Category') }} <small>( {{ $subscriptionDetails->feature_limit_left['category'] }}{{translate(' left )') }}</small></span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-3 d-flex flex-wrap justify-content-end gap-3">
                            @if($subscriptionDetails?->is_canceled == 0 && $subscriptionDetails->package_end_date > \Carbon\Carbon::now()->subDay())
                                <button type="button" class="btn btn--danger" data-bs-toggle="modal" data-bs-target="#confirmationModal"> {{translate('Cancel Subscription')}}</button>
                            @endif
                            <button type="button" class="btn btn--primary" data-bs-toggle="modal" data-bs-target="#priceModal">{{translate('Change/Renew Subscription Plan')}}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        @else
            <div class="container-fluid">
                <div class="page-title-wrap mb-3">
                    <h2 class="page-title">{{translate('My Business Plan')}}</h2>
                </div>
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <img width="20" src="{{asset('public/assets/admin-module/img/icons/ov11.png')}}" alt="">
                            <h3>{{translate('Package Overview')}}</h3>
                        </div>

                        <div class="c1-light-bg radius-10 p-lg-4 p-3">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-30">
                                <div class="">
                                    <h4 class="h4 mb-1 c1 fw-bold">{{ translate('Commission Base Plan') }}</h4>
                                    <h4 class="mb-1">{{ $commission }}% {{translate('Commission per booking order')}}</h4>
                                    <h5 class="">{{ translate('Provider will pay')}} {{ $commission }}% {{ translate('commission to admin from each booking. You will get access of all the features and options  in store panel , app and interaction with user.') }}</h5>
                                </div>
                            </div>
                        </div>
                        @if($subscriptionStatus)
                            <div class="text-end pt-3">
                                <button type="button" class="btn btn--primary" data-bs-toggle="modal" data-bs-target="#priceModal">{{translate('Change Business Plan')}}</button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    <?php
    $daysDifference = 0 ;
    if ($subscriptionDetails){
        $endDate = \Carbon\Carbon::parse($subscriptionDetails->package_end_date);
        $today = \Carbon\Carbon::today()->subDay();
        $daysDifference = $endDate->diffInDays($today);
    }
    ?>
    <!-- Off Status Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-30">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="d-flex flex-column gap-2 align-items-center text-center">
                        <img class="mb-3" src="{{asset('public/assets/admin-module')}}/img/ad_delete.svg" alt="">
                        <h3 class="mb-2">{{translate('Are You Sure?')}}</h3>
                        <p>If you cancel the subscription, after {{$daysDifference}} days the Provider will no longer be able to run the
                        business before subscribe a new plan</p>
                        <div class="d-flex gap-3 justify-content-center flex-wrap">
                            <button type="button" class="btn btn-soft--danger text-capitalize" data-bs-dismiss="modal" aria-label="Close">{{translate('Not Now')}}</button>
                            <form action="{{ route('provider.subscription-package.cancel') }}" method="post">
                                @csrf
                                <input type="hidden" name="package_id" value="{{ $subscriptionDetails?->subscription_package_id }}">
                                <button type="submit" class="btn btn--danger text-capitalize">{{translate('Yes, Cancel')}}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
