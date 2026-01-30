{{-- Service Sections (admin only) - title + HTML description per section --}}
<div class="mb-3">
    <label class="form-label fw-bold">{{ translate('service_sections') }}</label>
    <p class="text-muted small mb-3">{{ translate('add_multiple_sections_with_title_and_description') }}</p>
</div>

<div id="service-sections-list">
    @if(isset($sections) && $sections->count() > 0)
        @foreach($sections as $index => $section)
            <div class="service-section-row card card-body mb-3 border" data-index="{{ $index }}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <label class="form-label mb-0">{{ translate('section_title') }}</label>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-section-row" aria-label="{{ translate('remove') }}">
                        <span class="material-icons">delete</span>
                    </button>
                </div>
                <input type="text" class="form-control mb-3 section-title" name="service_sections[{{ $index }}][title]"
                       placeholder="{{ translate('section_title') }}" value="{{ $section->title ?? '' }}">
                <label class="form-label">{{ translate('description') }}</label>
                <div class="section-description-wrap">
                    <textarea class="form-control ckeditor section-description-editor" name="service_sections[{{ $index }}][description]"
                              rows="4" placeholder="{{ translate('description') }}">{!! $section->description ?? '' !!}</textarea>
                </div>
            </div>
        @endforeach
    @endif
</div>

<div class="mb-3">
    <button type="button" class="btn btn--primary" id="add-service-section-btn">
        <span class="material-icons">add</span>
        {{ translate('add_section') }}
    </button>
</div>

{{-- Template for new section row (cloned by JS; __INDEX__ is replaced with current index) --}}
<template id="service-section-row-template">
    <div class="service-section-row card card-body mb-3 border" data-index="__INDEX__">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <label class="form-label mb-0">{{ translate('section_title') }}</label>
            <button type="button" class="btn btn-sm btn-outline-danger remove-section-row" aria-label="{{ translate('remove') }}">
                <span class="material-icons">delete</span>
            </button>
        </div>
        <input type="text" class="form-control mb-3 section-title" name="service_sections[__INDEX__][title]"
               placeholder="{{ translate('section_title') }}">
        <label class="form-label">{{ translate('description') }}</label>
        <div class="section-description-wrap">
            <textarea class="form-control ckeditor section-description-editor" id="section-desc-__INDEX__" name="service_sections[__INDEX__][description]"
                      rows="4" placeholder="{{ translate('description') }}"></textarea>
        </div>
    </div>
</template>
