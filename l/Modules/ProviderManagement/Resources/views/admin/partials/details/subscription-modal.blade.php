@php
    $daysDifference = NULL;
    $provider = Modules\ProviderManagement\Entities\Provider::where('id', request()->provider_id)->first();

    if ($provider) {
        $providerId = $provider->id;
        $commission = $provider->commission_status == 1 ? $provider->commission_percentage : business_config('default_commission', 'business_information')->live_values;

        $subscriptionPackages = Modules\BusinessSettingsModule\Entities\SubscriptionPackage::with('subscriptionPackageFeature', 'subscriptionPackageLimit')
            ->OfStatus(1)->get();
        $formattedPackages = $subscriptionPackages->map(function ($subscriptionPackage) {
            return formatSubscriptionPackage($subscriptionPackage, PACKAGE_FEATURES);
        });

        $packageSubscriber = Modules\BusinessSettingsModule\Entities\PackageSubscriber::where('provider_id', $providerId)->first();
        if ($packageSubscriber){
            $endDate = \Carbon\Carbon::parse($packageSubscriber->package_end_date);
            $today = \Carbon\Carbon::today()->subDay();
            $daysDifference = $endDate->diffInDays($today);
        }
        $commissionStatus = (int)((business_config('provider_commision', 'provider_config'))->live_values);
    }
@endphp

@if($provider)

    <!-- Price Modal -->
    <div class="modal fade" id="priceModal" tabindex="-1" aria-labelledby="priceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-body p-lg-5">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                    <div class="text-center mb-30">
                        <h3 class="mb-2 h5">{{translate('Change/Renew Subscription Plan')}}</h3>
                        <p class="text-muted">{{translate('Renew or shift your plan to get better experience!')}}</p>
                    </div>

                    <div class="overflow-x-auto price-box-wrap">
                        @if($packageSubscriber && $commissionStatus)
                            <div class="price-box d-flex flex-column rounded-3 border">
                                <div class="price-box__top px-2 py-4 text-center mb-3">
                                    <h5>{{translate('Commission Base')}}</h5>
                                </div>

                                <div class="text-center min-h-62 d-flex flex-column justify-content-center">
                                    <strong class="h3">{{$commission}}%</strong>
                                </div>

                                <div class="px-2">
                                    <hr>
                                </div>

                                <div class="p-3 flex-grow-1 d-flex flex-column">
                                    <div class="text-center mb-30 fs-12">
                                        {{translate('Provider will pay ')}}{{$commission}}% {{translate('commission to Admin from each booking. You will get access of all the features and options  in provider panel , app and interaction with user.')}}
                                    </div>

                                    <div class="d-flex justify-content-center pb-2 mt-auto">
                                        <a href="#" class="btn btn--primary text-capitalize" data-bs-toggle="modal" data-bs-target="#shiftToCommission">{{translate('Shift
                                        to this plan')}}</a>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @foreach($subscriptionPackages as $package)
                            @php
                                $isMatch = $packageSubscriber?->subscription_package_id == $package->id;
                            @endphp

                            <div class="price-box d-flex flex-column {{ $isMatch ? 'active' : '' }} rounded-3 border">
                                <div class="price-box__top px-2 py-4 text-center mb-3">
                                    <h5 class="line-clamp-1">{{ $package->name }}</h5>
                                </div>

                                <div class="text-center min-h-62 d-flex flex-column justify-content-center">
                                    <strong class="h3">{{with_currency_symbol($package->price)}}</strong>
                                    <div>{{ $package->duration }} {{translate('Days')}}</div>
                                </div>

                                <div class="px-2">
                                    <hr>
                                </div>

                                <div class="p-3 flex-grow-1 d-flex flex-column">
                                    <ul class="d-flex flex-column align-items-center gap-2 p-0 fs-12 mb-30">
                                        @foreach($package->feature_list as $feature)
                                            <li>{{ $feature }}</li>
                                        @endforeach
                                    </ul>

                                    <div class="d-flex justify-content-center pb-2 mt-auto">
                                        @if($isMatch && $packageSubscriber != null)
                                            <a class="btn btn-warning text-capitalize admin-renew-package" data-bs-toggle="modal" data-bs-target="#renewModal" data-id="{{ $package->id }}" data-provider="{{ $providerId }}">{{translate('Renew
                                            Package')}}</a>
                                        @elseif($packageSubscriber == null)
                                            <a href="#" class="btn btn--primary text-capitalize admin-purchase-package" data-bs-toggle="modal" data-bs-target="#purchaseModal" data-id="{{ $package->id }}" data-provider="{{ $providerId }}">{{translate('Purchase
                                           to this plan')}}</a>
                                        @else
                                            <a href="#" class="btn btn--primary text-capitalize admin-shift-package" data-bs-toggle="modal" data-bs-target="#shiftModal" data-id="{{ $package->id }}" data-provider="{{ $providerId }}">{{translate('Shift
                                           to this plan')}}</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Shift Modal -->
    <div class="modal fade" id="shiftModal" tabindex="-1" aria-labelledby="shiftModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-body p-lg-5">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                    <div class="text-center mb-30">
                        <h3 class="mb-2 h5">{{translate('Shift to New Subscription Plan')}}</h3>
                    </div>

                    <form action="{{ route('admin.provider.subscription-package.shift.payment') }}" method="post">
                        @csrf
                        <div class="admin-append-shift">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Renew Modal -->
    <div class="modal fade" id="renewModal" tabindex="-1" aria-labelledby="renewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-body p-lg-5">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                    <div class="text-center mb-30">
                        <h3 class="mb-2 h5">{{translate('Renew Subscription Plan')}}</h3>
                    </div>

                    <form action="{{ route('admin.provider.subscription-package.renew.payment') }}" method="post">
                        @csrf
                        <div class="admin-append-renew">

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Modal -->
    <div class="modal fade" id="purchaseModal" tabindex="-1" aria-labelledby="renewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-body p-lg-5">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                    <div class="text-center mb-30">
                        <h3 class="mb-2 h5">{{translate('Purchase Subscription Plan')}}</h3>
                    </div>

                    <form action="{{ route('admin.provider.subscription-package.purchase.payment') }}" method="post">
                        @csrf
                        <div class="admin-append-purchase">

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @if($packageSubscriber)
        <!-- Shit commission Modal -->
        <div class="modal fade" id="shiftToCommission" tabindex="-1" aria-labelledby="renewModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-body p-lg-5">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                        <div class="text-center mb-30">
                            <h3 class="mb-2 h5">{{translate('Shift to New Subscription Plan')}}</h3>
                        </div>

                        <form action="{{ route('admin.provider.subscription-package.to.commission') }}" method="post">
                            @csrf
                            <input type="hidden" name="provider_id" value="{{ $packageSubscriber->provider_id }}">
                            <div class="append-purchase">
                                <div class="d-flex gap-2 gap-sm-3 align-items-center max-w-600 mx-auto">
                                    <div class="price-box d-flex flex-column rounded-3 border flex-grow-1">
                                        <div class="price-box__top px-2 py-4 text-center mb-3">
                                            <h5>{{ $packageSubscriber?->package_name }}</h5>
                                        </div>

                                        <div class="text-center min-h-62 d-flex flex-column justify-content-center pb-3">
                                            <strong class="h3">{{with_currency_symbol($packageSubscriber->package_price)}}</strong>
                                            <div>{{ $packageSubscriber?->package->duration }}  {{translate('Days')}}</div>
                                        </div>
                                    </div>

                                    <div class="flex-shrink-0">
                                        <img width="40" src="{{asset('public/assets/admin-module/img/icons/shift.png')}}" alt="">
                                    </div>

                                    <div class="price-box d-flex flex-column active rounded-3 border flex-grow-1  w-25">
                                        <div class="price-box__top px-2 py-4 text-center mb-3">
                                            <h5>{{ ('Commission Base')}}</h5>
                                        </div>

                                        <div class="text-center min-h-62 d-flex flex-column justify-content-center pb-3">
                                            <strong class="h3">{{$commission}}%</strong>
                                            <div class="">
                                                {{translate('Admin gets ')}}{{$commission}}% {{translate('from each booking.')}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 pt-3">
                                <div class="d-flex flex-wrap gap-3 justify-content-end">
                                    <button type="button" class="btn btn--secondary text-capitalize" data-bs-dismiss="modal">{{translate('Cancel')}}</button>
                                    <button type="submit" class="btn btn--primary text-capitalize">
                                        {{translate('Shift Plan')}}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
                            <p></p>
                            <button type="button" class="btn btn-soft--danger text-capitalize" data-bs-dismiss="modal" aria-label="Close">{{translate('Not Now')}}</button>
                            <form action="{{ route('admin.provider.subscription-package.cancel') }}" method="post">
                                @csrf
                                <input type="hidden" name="package_id" value="{{ $subscriptionDetails?->subscription_package_id }}">
                                <input type="hidden" name="provider_id" value="{{ request()->id}}">
                                <button type="submit" class="btn btn--danger text-capitalize">{{translate('Yes, Cancel')}}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endif
