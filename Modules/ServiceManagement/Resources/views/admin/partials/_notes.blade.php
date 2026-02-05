{{-- Service notes: title, description, image --}}
<div class="mb-3">
    <label class="form-label fw-bold">{{ translate('service_note') }}</label>
    <p class="text-muted small mb-3">{{ translate('add_multiple_notes_with_title_description_image') }}</p>
</div>

<div id="service-notes-list">
    @if(isset($notes) && $notes->count() > 0)
        @foreach($notes as $index => $note)
            <div class="service-note-row card card-body mb-3 border" data-index="{{ $index }}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <label class="form-label mb-0">{{ translate('note') }} {{ $index + 1 }}</label>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-note-row" aria-label="{{ translate('remove') }}">
                        <span class="material-icons">delete</span>
                    </button>
                </div>
                <div class="mb-2">
                    <label class="form-label small">{{ translate('title') }}</label>
                    <input type="text" class="form-control" name="service_notes[{{ $index }}][title]"
                           placeholder="{{ translate('title') }}" value="{{ $note->title ?? '' }}">
                </div>
                <div class="mb-2">
                    <label class="form-label small">{{ translate('description') }}</label>
                    <textarea class="form-control" name="service_notes[{{ $index }}][description]"
                              rows="3" placeholder="{{ translate('description') }}">{{ $note->description ?? '' }}</textarea>
                </div>
                <div>
                    <label class="form-label small">{{ translate('image_url') }}</label>
                    <input type="url" class="form-control" name="service_notes[{{ $index }}][image]"
                           placeholder="https://..." value="{{ $note->image ?? '' }}">
                </div>
            </div>
        @endforeach
    @endif
</div>

<button type="button" class="btn btn--primary mb-3" id="add-service-note-btn">
    <span class="material-icons">add</span>
    {{ translate('add_note') }}
</button>

<template id="service-note-row-template">
    <div class="service-note-row card card-body mb-3 border" data-index="__INDEX__">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <label class="form-label mb-0">{{ translate('note') }} <span class="note-num">__NUM__</span></label>
            <button type="button" class="btn btn-sm btn-outline-danger remove-note-row" aria-label="{{ translate('remove') }}">
                <span class="material-icons">delete</span>
            </button>
        </div>
        <div class="mb-2">
            <label class="form-label small">{{ translate('title') }}</label>
            <input type="text" class="form-control" name="service_notes[__INDEX__][title]" placeholder="{{ translate('title') }}">
        </div>
        <div class="mb-2">
            <label class="form-label small">{{ translate('description') }}</label>
            <textarea class="form-control" name="service_notes[__INDEX__][description]" rows="3" placeholder="{{ translate('description') }}"></textarea>
        </div>
        <div>
            <label class="form-label small">{{ translate('image_url') }}</label>
            <input type="url" class="form-control" name="service_notes[__INDEX__][image]" placeholder="https://...">
        </div>
    </div>
</template>
