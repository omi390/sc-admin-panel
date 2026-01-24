@extends('adminmodule::layouts.master')

@section('title',translate('Update Subscription Package'))

@push('css_or_js')

@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex gap-2 align-items-center justify-content-between mb-30">
                <div class="page-title-wrap">
                    <h2 class="page-title mb-2">{{translate('Update Subscription Package')}}</h2>
                    <p>{{translate('Update Subscriptions Packages for Subscription Business Model')}}</p>
                </div>

                <div class="ripple-animation" data-bs-toggle="modal" data-bs-target="#documentationModal" type="button">
                    <img src="{{asset('/public/assets/admin-module/img/info.svg')}}" class="svg" alt="">
                </div>
            </div>

            <form action="" method="post">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="card border shadow-none">
                            <div class="card-body">
                                <div class="">
                                    <h4 class="mb-1">{{translate('Package Information')}}</h4>
                                    <p class="fs-12">{{translate('Update Subscriptions Packages for Subscription Business Model')}}</p>
                                </div>

                                <hr>

                                <div class="row mt-4">
                                    <div class="col-lg-4 col-sm-6">
                                        <div class="form-floating form-floating__icon mb-30">
                                            <input type="text" class="form-control" name="name" value="{{ $subscriptionPackage->name }}" placeholder="name" required="">
                                            <label>{{translate('Package Name')}}</label>
                                            <span class="material-icons">account_circle</span>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6">
                                        <div class="form-floating form-floating__icon mb-30">
                                            <input type="number" class="form-control" name="price" value="{{ $subscriptionPackage->price }}" placeholder="price" required="">
                                            <label>{{translate('Package Price ($)')}}</label>
                                            <span class="material-icons">account_circle</span>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6">
                                        <div class="form-floating form-floating__icon mb-30">
                                            <input type="number" class="form-control" name="duration" value="{{ $subscriptionPackage->duration }}" placeholder="duration" required="">
                                            <label>{{translate('Package Validity Days')}}</label>
                                            <span class="material-icons">account_circle</span>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6">
                                        <div class="form-floating">
                                            <textarea name="description" id="package_info" rows="3" class="form-control" placeholder="Package Info" required>{{ $subscriptionPackage->description }}</textarea>
                                            <label>{{translate('Package Info')}}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border shadow-none mt-3">
                            <div class="card-body">
                                <div class="d-flex gap-2 justify-content-between align-items-center">
                                    <div class="">
                                        <h4 class="mb-1">{{translate('Package Available Features')}}</h4>
                                        <p class="fs-12">{{translate('Mark the feature you want to give in this package')}}</p>
                                    </div>

                                    <div class="bg--secondary px-2 py-1 rounded d-flex gap-1 align-items-center">
                                        <input class="select-all-checkbox" type="checkbox" value="" id="select_all">
                                        <label class="user-select-none" for="select_all">Select All</label>
                                    </div>
                                </div>

                                <hr>

                                <div class="grid-columns mt-4">
                                    @foreach(PACKAGE_FEATURES as $feature)
                                        <div class="d-flex gap-1 align-items-center">
                                            @php
                                                $featureExists = $subscriptionPackage->subscriptionPackageFeature->contains(function ($value) use ($feature) {
                                                    return $value->feature == $feature['key'];
                                                });
                                            @endphp
                                            <input class="feature-checkbox" type="checkbox" id="{{ $feature['key'] }}" name="feature[{{ $feature['key'] }}]"
                                                   @if($featureExists) checked @endif>
                                            <label class="user-select-none flex-grow-1" for="{{ $feature['key'] }}">{{ $feature['value'] }}</label>
                                        </div>
                                    @endforeach

                                </div>
                            </div>
                        </div>

                        <div class="card border shadow-none mt-3">
                            <div class="card-body">
                                <div class="">
                                    <h4 class="mb-1">{{translate('Set limit')}}</h4>
                                    <p class="fs-12">{{translate('Set maximum booking request received & service provide limit for this package')}}</p>
                                </div>

                                <hr>

                                <div class="d-flex flex-wrap gap-3 mt-4">
                                    <div class="bg--secondary rounded p-lg-4 p-3">
                                        <h5 class="mb-4">{{translate('Maximum Booking Request Limit')}}</h5>

                                        <div class="d-flex flex-wrap align-items-center gap-3 limit-item-card">
                                            <div class="custom-radio">
                                                <input type="radio" class="limit-input" value="" name="request_limit[booking][is_limited]" id="unlimited" {{ $subscriptionPackage->subscriptionPackageLimit->where('key', 'booking')->first() && !$subscriptionPackage->subscriptionPackageLimit->where('key', 'booking')->first()->is_limited ? 'checked' : '' }}>
                                                <label for="unlimited">{{translate('Unlimited (Default)')}}</label>
                                            </div>
                                            <div class="custom-radio">
                                                <input type="radio" class="limit-input" value="Use_Limit" name="request_limit[booking][is_limited]" id="use_limit" {{ $subscriptionPackage->subscriptionPackageLimit->where('key', 'booking')->first() && $subscriptionPackage->subscriptionPackageLimit->where('key', 'booking')->first()->is_limited ? 'checked' : '' }}>
                                                <label for="use_limit">{{translate('Use Limit')}}</label>
                                            </div>
                                            <div class="booking-use_limit d-none">
                                                <input name="request_limit[booking][limit_count]" type="number" class="form-control booking-limit-count" value="{{ $subscriptionPackage->subscriptionPackageLimit->where('key', 'booking')->first() ? $subscriptionPackage->subscriptionPackageLimit->where('key', 'booking')->first()->limit_count : '' }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg--secondary rounded p-lg-4 p-3 limit-item-card">
                                        <h5 class="mb-4">{{translate('Maximum Service Sub Category Limit')}}</h5>

                                        <div class="d-flex flex-wrap align-items-center gap-3">
                                            <div class="custom-radio">
                                                <input type="radio" class="limit-input" value="Use_unlimit" name="request_limit[category][is_limited]" id="unlimited2" {{ $subscriptionPackage->subscriptionPackageLimit->where('key', 'category')->first()->is_limited ? '' : 'checked' }}>
                                                <label for="unlimited2">{{translate('Unlimited (Default)')}}</label>
                                            </div>
                                            <div class="custom-radio">
                                                <input type="radio" class="limit-input" value="Use_Limit" name="request_limit[category][is_limited]" id="use_limit2" {{ $subscriptionPackage->subscriptionPackageLimit->where('key', 'category')->first()->is_limited ? 'checked' : '' }}>
                                                <label for="use_limit2">{{translate('Use Limit')}}</label>
                                            </div>
                                            <div class="category-use_limit2 d-none">
                                                <input name="request_limit[category][limit_count]" type="number" class="form-control category-limit-count" value="{{ $subscriptionPackage->subscriptionPackageLimit->where('key', 'category')->first()->limit_count }}" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-end gap-3">
                            <button type="reset" class="btn btn--secondary reset_btn">{{translate('Reset')}}</button>
                            <button type="submit" class="btn btn--primary">{{translate('Submit')}}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="documentationModal" tabindex="-1" aria-labelledby="documentationModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 pb-1">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-column align-items-center text-center gap-2">
                        <h3>{{ translate('Subscription Packages')}}</h3>
                        <p>{{translate('Here you can view all the data placements in a package card in the subscription UI in the
                            user app and website')}}</p>
                        <img src="{{asset('public/assets/admin-module/img/tutorial.svg')}}" class="tutorial-svg svg max-w-100" alt="">
                    </div>
                    <div class="d-flex justify-content-end mt-5">
                        <button type="button" class="btn btn--primary" data-bs-dismiss="modal">
                            {{translate('Okay')}}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        "use strict";
        $(document).ready(function() {

            var selectAllCheckbox = document.getElementById('select_all');
            var featureCheckboxes = document.querySelectorAll('.feature-checkbox');

            function updateSelectAllCheckbox() {
                var allChecked = true;
                featureCheckboxes.forEach(function(checkbox) {
                    if (!checkbox.checked) {
                        allChecked = false;
                    }
                });
                selectAllCheckbox.checked = allChecked;
            }

            featureCheckboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    updateSelectAllCheckbox();
                });
            });

            selectAllCheckbox.addEventListener('change', function() {
                var isChecked = this.checked;
                featureCheckboxes.forEach(function(checkbox) {
                    checkbox.checked = isChecked;
                });
            });

            updateSelectAllCheckbox();

            var initialRadioState = {
                booking: {
                    unlimited: $('#unlimited').prop('checked'),
                    use_limit: $('#use_limit').prop('checked')
                },
                category: {
                    unlimited: $('#unlimited2').prop('checked'),
                    use_limit: $('#use_limit2').prop('checked')
                }
            };
            $('#unlimited').trigger('change');
            $('#use_limit').trigger('change');
            $('#unlimited2').trigger('change');
            $('#use_limit2').trigger('change');

            $('.reset_btn').click(function() {
                $('#unlimited').prop('checked', initialRadioState.booking.unlimited);
                $('#use_limit').prop('checked', initialRadioState.booking.use_limit);
                $('#unlimited2').prop('checked', initialRadioState.category.unlimited);
                $('#use_limit2').prop('checked', initialRadioState.category.use_limit);

                $('#unlimited').trigger('change');
                $('#use_limit').trigger('change');
                $('#unlimited2').trigger('change');
                $('#use_limit2').trigger('change');
            });

            $('.limit-input').on('change', function() {
                var closestLimitItemCard = $(this).closest('.limit-item-card');
                var isChecked = $(this).is(':checked');
                if (isChecked) {
                    if ($(this).val() == 'Use_Limit') {
                        closestLimitItemCard.find('.category-use_limit2, .booking-use_limit').removeClass('d-none');
                        closestLimitItemCard.find('.category-limit-count, .booking-limit-count').prop('required', true);
                    } else {
                        closestLimitItemCard.find('.category-use_limit2, .booking-use_limit').addClass('d-none');
                        closestLimitItemCard.find('.category-limit-count, .booking-limit-count').prop('required', false);
                    }
                }
            }).trigger('change');
        });
    </script>
@endpush
