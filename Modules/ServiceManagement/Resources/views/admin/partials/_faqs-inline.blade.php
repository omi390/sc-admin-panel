{{-- FAQs inline on service form: title, description (service_id set on save) --}}
<div class="mb-3">
    <label class="form-label fw-bold">{{ translate('faq') }}</label>
    <p class="text-muted small mb-3">{{ translate('add_faqs_with_title_and_description') }}</p>
</div>

<div id="service-faqs-inline-list">
    @if(isset($faqs) && $faqs->count() > 0)
        @foreach($faqs as $index => $faq)
            <div class="service-faq-inline-row card card-body mb-3 border" data-index="{{ $index }}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <label class="form-label mb-0">{{ translate('faq') }} {{ $index + 1 }}</label>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-faq-inline-row" aria-label="{{ translate('remove') }}">
                        <span class="material-icons">delete</span>
                    </button>
                </div>
                <div class="mb-2">
                    <label class="form-label small">{{ translate('title') }}</label>
                    <input type="text" class="form-control" name="service_faqs[{{ $index }}][title]"
                           placeholder="{{ translate('title') }}" value="{{ $faq->title ?? $faq->question ?? '' }}">
                </div>
                <div>
                    <label class="form-label small">{{ translate('description') }}</label>
                    <textarea class="form-control" name="service_faqs[{{ $index }}][description]"
                              rows="3" placeholder="{{ translate('description') }}">{{ $faq->description ?? $faq->answer ?? '' }}</textarea>
                </div>
            </div>
        @endforeach
    @endif
</div>

<button type="button" class="btn btn--primary mb-3" id="add-service-faq-inline-btn">
    <span class="material-icons">add</span>
    {{ translate('add_faq') }}
</button>

<template id="service-faq-inline-row-template">
    <div class="service-faq-inline-row card card-body mb-3 border" data-index="__INDEX__">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <label class="form-label mb-0">{{ translate('faq') }} <span class="faq-inline-num">__NUM__</span></label>
            <button type="button" class="btn btn-sm btn-outline-danger remove-faq-inline-row" aria-label="{{ translate('remove') }}">
                <span class="material-icons">delete</span>
            </button>
        </div>
        <div class="mb-2">
            <label class="form-label small">{{ translate('title') }}</label>
            <input type="text" class="form-control" name="service_faqs[__INDEX__][title]" placeholder="{{ translate('title') }}">
        </div>
        <div>
            <label class="form-label small">{{ translate('description') }}</label>
            <textarea class="form-control" name="service_faqs[__INDEX__][description]" rows="3" placeholder="{{ translate('description') }}"></textarea>
        </div>
    </div>
</template>
