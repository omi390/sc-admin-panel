@extends('adminmodule::layouts.master')

@if($subscriptionDetails?->provider)
    @section('title', $subscriptionDetails->provider->company_name .'s Subscription')
@else
    @section('title', 'Subscriptions Details')
@endif

@push('css_or_js')

@endpush

@section('content')
    @if (!is_null($subscriptionDetails))
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-title-wrap mb-3">
                    @if($subscriptionDetails->provider)
                        <h2 class="page-title">{{$subscriptionDetails?->provider?->company_name}}'s Subscription</h2>
                    @else
                        <h2 class="page-title"> {{ translate('Subscriptions Details') }}</h2>
                    @endif
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                    <ul class="nav nav--tabs">
                        <li class="nav-item">
                            <a class="nav-link {{request()->is('admin/subscription/subscriber/details/*') ? 'active' : ''}}" href="{{ route('admin.subscription.subscriber.details',[request('id')]) }}?provider_id={{ $subscriptionDetails?->provider_id }}">{{translate('Package_Details')}}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{request()->is('admin/subscription/subscriber/transactions') ? 'active' : ''}}" href="{{ route('admin.subscription.subscriber.transactions') }}?provider_id={{ $subscriptionDetails?->provider_id }}&package_id={{ request('id') }}">{{translate('Transactions')}}</a>
                        </li>
                    </ul>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <img width="20" src="{{asset('public/assets/admin-module')}}/img/icons/newly-joined.png" alt="">
                            <h3>{{translate('Provider Info')}}</h3>
                        </div>

                        @if($subscriptionDetails->provider)
                            <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="information-details-box media flex-column flex-sm-row gap-20">
                                    <div class="media flex-wrap gap-3">
                                        <img width="140"
                                             src="{{ $subscriptionDetails?->provider?->logo_full_path }}" alt="{{translate('image')}}">
                                        <div class="media-body">
                                            <h4 class="information-details-box__title">{{Str::limit($subscriptionDetails?->provider?->company_name??'', 30)}}</h4>

                                            <ul class="contact-list">
                                                <li>
                                                    <span class="material-symbols-outlined">phone_iphone</span>
                                                    <a href="tel:{{ $subscriptionDetails?->provider?->company_phone }}">{{ $subscriptionDetails?->provider?->company_phone }}</a>
                                                </li>
                                                <li>
                                                    <span class="material-symbols-outlined">mail</span>
                                                    <a class="fs-12 title-color " href="mailto:{{ $subscriptionDetails?->provider?->company_email }}">{{ $subscriptionDetails?->provider?->company_email }}</a>
                                                </li>
                                                <li>
                                                    <span class="material-symbols-outlined">map</span>
                                                    {{ $subscriptionDetails?->provider?->company_address }}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="information-details-box h-100">
                                    <h4 class="information-details-box__title c1">{{translate('Contact Person Information')}}
                                    </h4>
                                    <h3 class="information-details-box__subtitle">{{ $subscriptionDetails?->provider?->contact_person_name }}</h3>

                                    <ul class="contact-list">
                                        <li>
                                            <span class="material-symbols-outlined">phone_iphone</span>
                                            <a href="tel:{{ $subscriptionDetails?->provider?->contact_person_phone }}">{{ $subscriptionDetails?->provider?->contact_person_phone }}</a>
                                        </li>
                                        <li>
                                            <span class="material-symbols-outlined">mail</span>
                                            <a href="mailto:{{ $subscriptionDetails?->provider?->contact_person_email }}">{{ $subscriptionDetails?->provider?->contact_person_email }}</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @else
                            <div class="text-center">
                                <h2>{{ translate('Provider Deleted') }}</h2>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <img width="20" src="{{asset('public/assets/admin-module')}}/img/icons/billing.svg" class="svg" alt="">
                            <h3>{{ translate('Billing') }}</h3>
                        </div>

                        <div class="row g-4">
                            <div class="col-lg-4 col-sm-6">
                                <div class="overview-card after-w50 d-flex gap-3 align-items-center p-lg-4">
                                    <div class="img-circle">
                                        <img width="34" src="{{asset('public/assets/admin-module/img/icons/b1.png')}}" alt="{{ translate('basic') }}">
                                    </div>
                                    <div class="d-flex flex-column gap-2">
                                        <div>{{translate('Expire Date')}}</div>
                                        <h3 class="overview-card__title">{{ \Carbon\Carbon::parse($subscriptionDetails?->package_end_date)->format('d M Y') }}</h3>
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
                                        <h3 class="overview-card__title">{{ $totalPurchase}}</h3>
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
                                <h3>Package Overview</h3>
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
                            @can('subscriber_update')
                                @if($subscriptionDetails->provider)
                                    <div class="mt-3 d-flex flex-wrap justify-content-end gap-3">
                                        @if($subscriptionDetails->package_end_date > \Carbon\Carbon::now()->subDay())
                                            @if($subscriptionDetails?->is_canceled == 0)
                                                <button type="button" class="btn btn--danger" data-bs-toggle="modal" data-bs-target="#subscriberConfirmationModal">{{translate('Cancel Subscription')}}</button>
                                            @endif
                                        @endif
                                        <button type="button" class="btn btn--primary" data-bs-toggle="modal" data-bs-target="#priceModal">{{translate('Change/Renew Subscription Plan')}}</button>
                                    </div>
                                @endif
                            @endcan
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal fade" id="subscriberConfirmationModal" tabindex="-1" aria-labelledby="subscriberConfirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body p-30">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        <div class="d-flex flex-column gap-2 align-items-center text-center">
                                <?php
                                $daysDifference = 0;
                                if ($subscriptionDetails){
                                    $endDate = \Carbon\Carbon::parse($subscriptionDetails->package_end_date);
                                    $today = \Carbon\Carbon::today()->subDay();
                                    $daysDifference = $endDate->diffInDays($today);
                                }
                                ?>
                            <img class="mb-3" src="{{asset('public/assets/admin-module')}}/img/ad_delete.svg" alt="">
                            <h3 class="mb-2">{{translate('Are You Sure?')}}</h3>
                            <p>If you cancel the subscription, after {{$daysDifference}} days the Provider will no longer be able to run the
                        business before subscribe a new plan</p>
                            <div class="d-flex gap-3 justify-content-center flex-wrap">
                                <button type="button" class="btn btn-soft--danger text-capitalize" data-bs-dismiss="modal" aria-label="Close">{{translate('Not Now')}}</button>
                                <form action="{{ route('admin.subscription.subscriber.cancel') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="package_id" value="{{ $subscriptionDetails?->subscription_package_id }}">
                                    <input type="hidden" name="provider_id" value="{{ $subscriptionDetails?->provider_id }}">
                                    <button type="submit" class="btn btn--danger text-capitalize">{{translate('Yes, Cancel')}}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('providermanagement::admin.partials.details.subscription-modal')
    @else
        <script>
            window.location.href = "{{ url('/admin/subscription/subscriber/list') }}";
        </script>
    @endif
@endsection
