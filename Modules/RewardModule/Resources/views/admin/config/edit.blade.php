@extends('adminmodule::layouts.master')

@section('title', translate('edit_reward_point_config'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{ translate('Edit Reward Point Config') }}</h2>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3 p-3 bg-light rounded">
                                <p class="mb-1"><strong>{{ translate('Service Variant') }}:</strong> {{ $config->serviceVariant->variant ?? 'N/A' }}</p>
                                @if($config->serviceVariant && $config->serviceVariant->service)
                                    <p class="mb-1"><strong>{{ translate('Service') }}:</strong> {{ $config->serviceVariant->service->name ?? '' }}</p>
                                @endif
                                @if($config->serviceVariant && $config->serviceVariant->provider)
                                    <p class="mb-0"><strong>{{ translate('Provider') }}:</strong> {{ $config->serviceVariant->provider->name ?? '' }}</p>
                                @endif
                            </div>
                            <form action="{{ route('admin.reward-point.config.update', $config->id) }}" method="post">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-lg-4 mb-4">
                                        <div class="form-floating form-floating__icon">
                                            <input type="number" name="reward_points" class="form-control"
                                                   step="0.001" min="0" value="{{ old('reward_points', $config->reward_points) }}" required>
                                            <label>{{ translate('Reward Points') }}</label>
                                            <span class="material-icons">stars</span>
                                        </div>
                                        @error('reward_points')
                                        <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-lg-4 mb-4">
                                        <div class="form-floating form-floating__icon">
                                            <input type="number" name="minimum_order_amount" class="form-control"
                                                   step="0.01" min="0" value="{{ old('minimum_order_amount', $config->minimum_order_amount ?? 0) }}" required>
                                            <label>{{ translate('Minimum Order Amount') }} ({{ currency_symbol() }})</label>
                                            <span class="material-icons">attach_money</span>
                                        </div>
                                        <small class="text-muted">{{ translate('Reward points will be added only if order amount is above this value') }}</small>
                                        @error('minimum_order_amount')
                                        <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-lg-4 mb-4">
                                        <div class="form-floating form-floating__icon">
                                            <input type="number" name="max_uses" class="form-control"
                                                   min="0" value="{{ old('max_uses', $config->max_uses) }}" required>
                                            <label>{{ translate('Max Uses') }} (0 = {{ translate('unlimited') }})</label>
                                            <span class="material-icons">repeat</span>
                                        </div>
                                        <small class="text-muted">{{ translate('Current uses') }}: {{ $config->current_uses }}</small>
                                        @error('max_uses')
                                        <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-lg-4 mb-4">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                                   id="is_active" {{ old('is_active', $config->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">{{ translate('Active') }}</label>
                                        </div>
                                    </div>

                                    <div class="col-lg-12 mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="reset_current_uses" value="1"
                                                   id="reset_current_uses">
                                            <label class="form-check-label" for="reset_current_uses">
                                                {{ translate('Reset current uses counter to 0') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex gap-4 flex-wrap justify-content-end">
                                    <a href="{{ route('admin.reward-point.config.list') }}" class="btn btn--secondary">{{ translate('Cancel') }}</a>
                                    <button type="submit" class="btn btn--primary">{{ translate('Update') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
