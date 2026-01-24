@extends('adminmodule::layouts.master')

@section('title',translate('Create Subscription Package'))

@push('css_or_js')

@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex gap-2 align-items-center justify-content-between mb-30">
                <div class="page-title-wrap">
                    <h2 class="page-title mb-2">{{translate('Create Subscription Package')}}</h2>
                    <p>{{translate('Create Subscriptions Packages for Subscription Business Model')}}</p>
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
                                    <p class="fs-12">{{translate('Give Subscriptions Package Information')}}</p>
                                </div>
                                <hr>
                                <div class="row mt-4">
                                    <div class="col-lg-4 col-sm-12">
                                        <div class="form-floating form-floating__icon mb-30">
                                            <input type="text" class="form-control" name="name" value="{{ old('name') }}" maxlength="100" placeholder="{{ translate('name') }}" required="">
                                            <label>{{translate('Package Name')}}</label>
                                            <span class="material-icons">title</span>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-12">
                                        <div class="form-floating form-floating__icon mb-30">
                                            <input type="number" class="form-control number-validation" name="price" value="{{ old('price') }}" min="0.01" step=".01" placeholder="{{ translate('price') }}" required="">
                                            <label>{{translate('Package Price')}} ({{currency_symbol()}})</label>
                                            <span class="material-icons">price_change</span>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-12">
                                        <div class="form-floating form-floating__icon mb-30">
                                            <input type="number" class="form-control number-validation" name="duration" value="{{ old('duration') }}" placeholder="duration" min="1" max="999999999" required="">
                                            <label>{{translate('Package Validity Days')}}</label>
                                            <span class="material-icons">date_range</span>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 col-sm-12">
                                        <div class="form-floating">
                                            <textarea name="description" id="package_info" rows="3" class="form-control" placeholder="{{ translate('Package Info') }}" maxlength="255" required>{{ old('description') }}</textarea>
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
                                        <p class="fs-12">{{ translate('Mark the feature you want to give in this package') }}</p>
                                    </div>

                                    <div class="bg--secondary px-2 py-1 rounded d-flex gap-1 align-items-center">
                                        <input class="select-all-checkbox" type="checkbox" value="" id="select_all">
                                        <label class="user-select-none" for="select_all">{{ translate('Select All') }}</label>
                                    </div>
                                </div>

                                <hr>

                                <div class="grid-columns mt-4">
                                    @foreach(PACKAGE_FEATURES as $feature)
                                        <div class="d-flex gap-1 align-items-center">
                                            <input class="feature-checkbox"
                                                   type="checkbox"
                                                   id="{{ $feature['key'] }}"
                                                   name="feature[{{ $feature['key'] }}]"
                                                   value="{{ $feature['key'] }}"
                                                {{ old('feature.'.$feature['key']) ? 'checked' : '' }}>
                                            <label class="user-select-none flex-grow-1" for="{{ $feature['key'] }}">{{ $feature['value'] }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="card border shadow-none mt-3">
                            <div class="card-body">
                                <div class="">
                                    <h4 class="mb-1">{{ translate('Set limit') }}</h4>
                                    <p class="fs-12">{{ translate('Set maximum booking request received & service provide limit for this package') }}</p>
                                </div>

                                <hr>

                                <div class="d-flex flex-wrap gap-3 mt-4">
                                    <div class="bg--secondary rounded p-lg-4 p-3">
                                        <h5 class="mb-4">{{ translate('Maximum Booking Request Limit') }}</h5>

                                        <div class="d-flex flex-wrap align-items-center gap-3">
                                            <div class="custom-radio">
                                                <input type="radio" name="request_limit[booking][limit_type]" id="unlimited" value="unlimited"
                                                    {{ old('request_limit.booking.limit_type', 'unlimited') == 'unlimited' ? 'checked' : '' }}>
                                                <label for="unlimited">{{ translate('Unlimited') }} ({{ translate('Default') }})</label>
                                            </div>
                                            <div class="custom-radio">
                                                <input type="radio" name="request_limit[booking][limit_type]" id="use_limit" value="limited"
                                                    {{ old('request_limit.booking.limit_type') == 'limited' ? 'checked' : '' }}>
                                                <label for="use_limit">{{ translate('Use Limit') }}</label>
                                            </div>
                                            <div class="booking-use_limit {{ old('request_limit.booking.limit_type') == 'limited' ? '' : 'd-none' }}">
                                                <input name="request_limit[booking][limit_count]" type="number" min="1" class="form-control booking-limit-count" placeholder="Ex: 1000"
                                                       value="{{ old('request_limit.booking.limit_count') }}"
                                                    {{ old('request_limit.booking.limit_type') == 'limited' ? 'required' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg--secondary rounded p-lg-4 p-3">
                                        <h5 class="mb-4">{{ translate('Maximum Service Sub Category Limit') }}</h5>

                                        <div class="d-flex flex-wrap align-items-center gap-3">
                                            <div class="custom-radio">
                                                <input type="radio" name="request_limit[category][limit_type]" id="unlimited2" value="unlimited"
                                                    {{ old('request_limit.category.limit_type', 'unlimited') == 'unlimited' ? 'checked' : '' }}>
                                                <label for="unlimited2">{{ translate('Unlimited') }} ({{ translate('Default') }})</label>
                                            </div>
                                            <div class="custom-radio">
                                                <input type="radio" name="request_limit[category][limit_type]" id="use_limit2" value="limited"
                                                    {{ old('request_limit.category.limit_type') == 'limited' ? 'checked' : '' }}>
                                                <label for="use_limit2">{{ translate('Use Limit') }}</label>
                                            </div>
                                            <div class="category-use_limit2 {{ old('request_limit.category.limit_type') == 'limited' ? '' : 'd-none' }}">
                                                <input name="request_limit[category][limit_count]" type="number" min="1" class="form-control category-limit-count" placeholder="Ex: 1000"
                                                       value="{{ old('request_limit.category.limit_count') }}"
                                                    {{ old('request_limit.category.limit_type') == 'limited' ? 'required' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-end gap-3">
                            <button type="button" class="btn btn--secondary" id="reset_button">{{translate('Reset')}}</button>
                            <button type="submit" class="btn btn--primary">{{translate('Submit')}}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="documentationModal" tabindex="-1" aria-labelledby="documentationModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 pb-1">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-column align-items-center text-center gap-2">
                        <h3>{{ translate('Subscription Packages') }}</h3>
                        <p>{{ translate('Here you can view all the data placements in a package card in the subscription UI in the user app and website') }}</p>
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

            $('#unlimited').change(function() {
                if ($(this).is(':checked')) {
                    $('.booking-use_limit').addClass('d-none');
                    $('.booking-limit-count').val('').removeAttr('required');
                }
            });

            $('#use_limit').change(function() {
                if ($(this).is(':checked')) {
                    $('.booking-use_limit').removeClass('d-none');
                    $('.booking-limit-count').attr('required', 'required');
                }
            });

            // Service Category Limit
            $('#unlimited2').change(function() {
                if ($(this).is(':checked')) {
                    $('.category-use_limit2').addClass('d-none');
                    $('.category-limit-count').val('').removeAttr('required');
                }
            });

            $('#use_limit2').change(function() {
                if ($(this).is(':checked')) {
                    $('.category-use_limit2').removeClass('d-none');
                    $('.category-limit-count').attr('required', 'required');
                }
            });

            $('#unlimited').trigger('change');
            $('#use_limit').trigger('change');
            $('#unlimited2').trigger('change');
            $('#use_limit2').trigger('change');

            $('#reset_button').click(function() {

                $('input[type="text"], input[type="number"], textarea').val('');
                $('input[type="checkbox"]').prop('checked', false);
                $('input[name="request_limit[booking][limit_type]"][value="unlimited"]').prop('checked', true);
                $('input[name="request_limit[category][limit_type]"][value="unlimited"]').prop('checked', true);
                $('.booking-use_limit, .category-use_limit2').addClass('d-none');
                $('.booking-limit-count, .category-limit-count').val('').removeAttr('required');

                $('#unlimited').trigger('change');
                $('#unlimited2').trigger('change');
            });
        });

    </script>
@endpush
