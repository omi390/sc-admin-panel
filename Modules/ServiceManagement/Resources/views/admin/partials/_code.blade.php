{{-- Code: code_text and code_img (url) on service --}}
<div class="mb-3">
    <label class="form-label fw-bold">{{ translate('code') }}</label>
    <p class="text-muted small mb-3">{{ translate('add_code_text_and_or_code_image_url') }}</p>
</div>

<div class="mb-3">
    <label class="form-label">{{ translate('code_text') }}</label>
    <textarea class="form-control" name="code_text" rows="5" placeholder="{{ translate('code_text') }}">{{ optional($service ?? null)->code_text ?? old('code_text') }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label">{{ translate('code_image_url') }}</label>
    <input type="url" class="form-control" name="code_img" placeholder="https://..."
           value="{{ optional($service ?? null)->code_img ?? old('code_img') }}">
    @if(isset($service) && !empty($service->code_img))
        <div class="mt-2">
            <img src="{{ $service->code_img }}" alt="Code" class="img-fluid rounded" style="max-height: 150px;">
        </div>
    @endif
</div>
