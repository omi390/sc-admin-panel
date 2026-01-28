@extends('adminmodule::layouts.master')

@section('title', translate('configure_reward_points'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('assets/admin-module/plugins/select2/select2.min.css') }}"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{ translate('Configure Reward Points') }}</h2>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('admin.reward-point.config.store') }}" method="post">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-12 mb-4">
                                        <label class="form-label">{{ translate('Sub Categories') }} <span class="text-danger">*</span></label>
                                        <select name="sub_category_ids[]" class="js-select theme-input-style w-100" multiple="multiple" required>
                                            @foreach($subCategories as $cat)
                                                <option value="{{ $cat->id }}"
                                                    {{ in_array($cat->id, $existingConfigIds) ? '' : '' }}>
                                                    {{ $cat->name ?? $cat->id }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">{{ translate('Select one or more sub categories. Existing configs for selected categories will be updated.') }}</small>
                                    </div>

                                    <div class="col-lg-4 mb-4">
                                        <div class="form-floating form-floating__icon">
                                            <input type="number" name="reward_points" class="form-control"
                                                   placeholder="0" step="0.001" min="0" value="{{ old('reward_points', 0) }}" required>
                                            <label>{{ translate('Reward Points') }}</label>
                                            <span class="material-icons">stars</span>
                                        </div>
                                        @error('reward_points')
                                        <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-lg-4 mb-4">
                                        <div class="form-floating form-floating__icon">
                                            <input type="number" name="max_uses" class="form-control"
                                                   placeholder="0" min="0" value="{{ old('max_uses', 0) }}" required>
                                            <label>{{ translate('Max Uses') }} (0 = {{ translate('unlimited') }})</label>
                                            <span class="material-icons">repeat</span>
                                        </div>
                                        @error('max_uses')
                                        <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-lg-4 mb-4">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                                   id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">{{ translate('Active') }}</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex gap-4 flex-wrap justify-content-end">
                                    <a href="{{ route('admin.reward-point.config.list') }}" class="btn btn--secondary">{{ translate('Cancel') }}</a>
                                    <button type="submit" class="btn btn--primary">{{ translate('Save') }}</button>
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
    <script src="{{ asset('assets/admin-module/plugins/select2/select2.min.js') }}"></script>
    <script>
        "use strict";
        $(document).ready(function () {
            $('.js-select').select2({
                placeholder: '{{ translate('Select sub categories') }}'
            });
        });
    </script>
@endpush
