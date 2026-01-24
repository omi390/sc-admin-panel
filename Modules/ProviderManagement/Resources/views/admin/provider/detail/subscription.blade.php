@extends('adminmodule::layouts.master')

@section('title',translate('provider_details'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{translate('Provider_Details')}}</h2>
            </div>

            <div class="mb-3">
                <ul class="nav nav--tabs nav--tabs__style2">
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='overview'?'active':''}}"
                           href="{{url()->current()}}?web_page=overview">{{translate('Overview')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='subscribed_services'?'active':''}}"
                           href="{{url()->current()}}?web_page=subscribed_services">{{translate('Subscribed_Services')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='bookings'?'active':''}}"
                           href="{{url()->current()}}?web_page=bookings">{{translate('Bookings')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='serviceman_list'?'active':''}}"
                           href="{{url()->current()}}?web_page=serviceman_list">{{translate('Service_Man_List')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='settings'?'active':''}}"
                           href="{{url()->current()}}?web_page=settings">{{translate('Settings')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='bank_information'?'active':''}}"
                           href="{{url()->current()}}?web_page=bank_information">{{translate('Bank_Information')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='reviews'?'active':''}}"
                           href="{{url()->current()}}?web_page=reviews">{{translate('Reviews')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='subscription'?'active':''}}"
                           href="{{url()->current()}}?web_page=subscription&provider_id={{ request()->provider_id }}">{{translate('Business Plan')}}</a>
                    </li>
                </ul>
            </div>
            @if($subscriptionDetails)
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
                                    <strong class="h4 title-color">{{with_currency_symbol($subscriptionDetails?->package_price - $subscriptionDetails?->vat_amount)}}/ </strong> <span class="h6 fw-medium">{{ $daysDifference }} {{translate('days')}}</span>
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

                        <div class="mt-3 d-flex flex-wrap justify-content-end gap-3">
                            @if($subscriptionDetails->package_end_date > \Carbon\Carbon::now()->subDay())
                                @if($subscriptionDetails?->is_canceled == 0)
                                    <button type="button" class="btn btn--danger" data-bs-toggle="modal" data-bs-target="#confirmationModal">{{translate('Cancel Subscription')}}</button>
                                @endif
                            @endif
                            <button type="button" class="btn btn--primary" data-bs-toggle="modal" data-bs-target="#priceModal">{{translate('Change/Renew Subscription Plan')}}</button>
                        </div>
                    </div>
                </div>
            </form>
            @else
                <div class="container-fluid">
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
    </div>
    @include('providermanagement::admin.partials.details.subscription-modal')
@endsection

