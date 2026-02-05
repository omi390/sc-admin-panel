{{-- Pros and Cons: two sections - one for pros, one for cons --}}
<div class="mb-3">
    <label class="form-label fw-bold">{{ translate('pros_and_cons') }}</label>
    <p class="text-muted small mb-3">{{ translate('add_pros_above_and_cons_below') }}</p>
</div>

{{-- Pros section --}}
<div class="card card-body mb-4 border border-success">
    <label class="form-label fw-bold text-success">{{ translate('pros') }}</label>
    <div id="service-pros-list">
        @php
            $pros = isset($prosAndCons) ? $prosAndCons->where('prod_or_con', 'pros') : collect();
        @endphp
        @foreach($pros as $index => $item)
            <div class="input-group mb-2 pros-row">
                <input type="text" class="form-control" name="service_pros[]" placeholder="{{ translate('pros_item') }}" value="{{ $item->title ?? '' }}">
                <input type="hidden" name="service_pros_sort[]" value="{{ $index }}">
                <button type="button" class="btn btn-outline-danger remove-pros-row" aria-label="{{ translate('remove') }}">
                    <span class="material-icons">delete</span>
                </button>
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-sm btn-outline-success mt-2" id="add-pros-btn">
        <span class="material-icons">add</span>
        {{ translate('add_pros') }}
    </button>
</div>

{{-- Cons section --}}
<div class="card card-body border border-danger">
    <label class="form-label fw-bold text-danger">{{ translate('cons') }}</label>
    <div id="service-cons-list">
        @php
            $cons = isset($prosAndCons) ? $prosAndCons->where('prod_or_con', 'con') : collect();
        @endphp
        @foreach($cons as $index => $item)
            <div class="input-group mb-2 cons-row">
                <input type="text" class="form-control" name="service_cons[]" placeholder="{{ translate('cons_item') }}" value="{{ $item->title ?? '' }}">
                <input type="hidden" name="service_cons_sort[]" value="{{ $index }}">
                <button type="button" class="btn btn-outline-danger remove-cons-row" aria-label="{{ translate('remove') }}">
                    <span class="material-icons">delete</span>
                </button>
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="add-cons-btn">
        <span class="material-icons">add</span>
        {{ translate('add_cons') }}
    </button>
</div>

<template id="pros-row-template">
    <div class="input-group mb-2 pros-row">
        <input type="text" class="form-control" name="service_pros[]" placeholder="{{ translate('pros_item') }}">
        <input type="hidden" name="service_pros_sort[]" value="">
        <button type="button" class="btn btn-outline-danger remove-pros-row" aria-label="{{ translate('remove') }}">
            <span class="material-icons">delete</span>
        </button>
    </div>
</template>
<template id="cons-row-template">
    <div class="input-group mb-2 cons-row">
        <input type="text" class="form-control" name="service_cons[]" placeholder="{{ translate('cons_item') }}">
        <input type="hidden" name="service_cons_sort[]" value="">
        <button type="button" class="btn btn-outline-danger remove-cons-row" aria-label="{{ translate('remove') }}">
            <span class="material-icons">delete</span>
        </button>
    </div>
</template>
