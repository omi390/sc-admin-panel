{{-- Procedure steps: image url, title, description --}}
<div class="mb-3">
    <label class="form-label fw-bold">{{ translate('procedure') }}</label>
    <p class="text-muted small mb-3">{{ translate('add_multiple_procedure_steps_with_image_title_description') }}</p>
</div>

<div id="service-procedures-list">
    @if(isset($procedures) && $procedures->count() > 0)
        @foreach($procedures as $index => $procedure)
            <div class="service-procedure-row card card-body mb-3 border" data-index="{{ $index }}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <label class="form-label mb-0">{{ translate('step') }} {{ $index + 1 }}</label>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-procedure-row" aria-label="{{ translate('remove') }}">
                        <span class="material-icons">delete</span>
                    </button>
                </div>
                <div class="mb-2">
                    <label class="form-label small">{{ translate('image_url') }}</label>
                    <input type="url" class="form-control" name="service_procedures[{{ $index }}][image_url]"
                           placeholder="https://..." value="{{ $procedure->image_url ?? '' }}">
                </div>
                <div class="mb-2">
                    <label class="form-label small">{{ translate('title') }}</label>
                    <input type="text" class="form-control" name="service_procedures[{{ $index }}][title]"
                           placeholder="{{ translate('title') }}" value="{{ $procedure->title ?? '' }}">
                </div>
                <div>
                    <label class="form-label small">{{ translate('description') }}</label>
                    <textarea class="form-control" name="service_procedures[{{ $index }}][description]"
                              rows="3" placeholder="{{ translate('description') }}">{{ $procedure->description ?? '' }}</textarea>
                </div>
            </div>
        @endforeach
    @endif
</div>

<button type="button" class="btn btn--primary mb-3" id="add-service-procedure-btn">
    <span class="material-icons">add</span>
    {{ translate('add_step') }}
</button>

<template id="service-procedure-row-template">
    <div class="service-procedure-row card card-body mb-3 border" data-index="__INDEX__">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <label class="form-label mb-0">{{ translate('step') }} <span class="procedure-step-num">__NUM__</span></label>
            <button type="button" class="btn btn-sm btn-outline-danger remove-procedure-row" aria-label="{{ translate('remove') }}">
                <span class="material-icons">delete</span>
            </button>
        </div>
        <div class="mb-2">
            <label class="form-label small">{{ translate('image_url') }}</label>
            <input type="url" class="form-control" name="service_procedures[__INDEX__][image_url]" placeholder="https://...">
        </div>
        <div class="mb-2">
            <label class="form-label small">{{ translate('title') }}</label>
            <input type="text" class="form-control" name="service_procedures[__INDEX__][title]" placeholder="{{ translate('title') }}">
        </div>
        <div>
            <label class="form-label small">{{ translate('description') }}</label>
            <textarea class="form-control" name="service_procedures[__INDEX__][description]" rows="3" placeholder="{{ translate('description') }}"></textarea>
        </div>
    </div>
</template>
