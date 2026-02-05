{{-- Policy: service_id, title, description --}}
<div class="mb-3">
    <label class="form-label fw-bold">{{ translate('policy') }}</label>
    <p class="text-muted small mb-3">{{ translate('add_multiple_policies_with_title_and_description') }}</p>
</div>

<div id="service-policies-list">
    @if(isset($policies) && $policies->count() > 0)
        @foreach($policies as $index => $policy)
            <div class="service-policy-row card card-body mb-3 border" data-index="{{ $index }}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <label class="form-label mb-0">{{ translate('policy') }} {{ $index + 1 }}</label>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-policy-row" aria-label="{{ translate('remove') }}">
                        <span class="material-icons">delete</span>
                    </button>
                </div>
                <div class="mb-2">
                    <label class="form-label small">{{ translate('title') }}</label>
                    <input type="text" class="form-control" name="service_policies[{{ $index }}][title]"
                           placeholder="{{ translate('title') }}" value="{{ $policy->title ?? '' }}">
                </div>
                <div>
                    <label class="form-label small">{{ translate('description') }}</label>
                    <textarea class="form-control" name="service_policies[{{ $index }}][description]"
                              rows="4" placeholder="{{ translate('description') }}">{{ $policy->description ?? '' }}</textarea>
                </div>
            </div>
        @endforeach
    @endif
</div>

<button type="button" class="btn btn--primary mb-3" id="add-service-policy-btn">
    <span class="material-icons">add</span>
    {{ translate('add_policy') }}
</button>

<template id="service-policy-row-template">
    <div class="service-policy-row card card-body mb-3 border" data-index="__INDEX__">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <label class="form-label mb-0">{{ translate('policy') }} <span class="policy-num">__NUM__</span></label>
            <button type="button" class="btn btn-sm btn-outline-danger remove-policy-row" aria-label="{{ translate('remove') }}">
                <span class="material-icons">delete</span>
            </button>
        </div>
        <div class="mb-2">
            <label class="form-label small">{{ translate('title') }}</label>
            <input type="text" class="form-control" name="service_policies[__INDEX__][title]" placeholder="{{ translate('title') }}">
        </div>
        <div>
            <label class="form-label small">{{ translate('description') }}</label>
            <textarea class="form-control" name="service_policies[__INDEX__][description]" rows="4" placeholder="{{ translate('description') }}"></textarea>
        </div>
    </div>
</template>
