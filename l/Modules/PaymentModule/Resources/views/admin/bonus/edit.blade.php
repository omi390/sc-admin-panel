@extends('adminmodule::layouts.master')

@section('title',translate('Update Wallet Bonus'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Update Wallet Bonus')}}</h2>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <form action="{{route('admin.bonus.update',[$bonus->id])}}" method="post">
                                @method('PUT')
                                @csrf
                                @php($language= Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name','system_language')->first())
                                @php($default_lang = str_replace('_', '-', app()->getLocale()))
                                @if($language)
                                    <ul class="nav nav--tabs border-color-primary">
                                        <li class="nav-item">
                                            <a class="nav-link lang_link active"
                                               href="#"
                                               id="default-link">{{translate('default')}}</a>
                                        </li>
                                        @foreach ($language?->live_values as $lang)
                                            <li class="nav-item">
                                                <a class="nav-link lang_link"
                                                   href="#"
                                                   id="{{ $lang['code'] }}-link">{{ get_language_name($lang['code']) }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                                @if($language)
                                    <div class="lang-form" id="default-form">
                                        <div class="row">

                                            <div class="col-lg-4">
                                                <div class="form-floating form-floating__icon mb-30 mt-30">
                                                    <input type="text" name="bonus_title[]" class="form-control" required
                                                           value="{{$bonus?->getRawOriginal('bonus_title')}}"
                                                           placeholder="{{translate('bonus_title')}}"
                                                    >
                                                    <label>{{translate('bonus_title')}}
                                                        ({{ translate('default') }})</label>
                                                    <span class="material-icons">title</span>
                                                </div>
                                            </div>
                                            <div class="col-lg-8">
                                                <div class="form-floating form-floating__icon mb-30 mt-30">
                                                    <input type="text" name="short_description[]" class="form-control"
                                                           value="{{$bonus?->getRawOriginal('short_description')}}" required
                                                           placeholder="{{translate('short_description')}}"
                                                    >
                                                    <label>{{translate('short_description')}}
                                                        ({{ translate('default') }})</label>
                                                    <span class="material-icons">subtitles</span>
                                                </div>
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                        </div>
                                    </div>
                                    @foreach ($language?->live_values as $lang)
                                            <?php
                                            if (count($bonus['translations'])) {
                                                $translate = [];
                                                foreach ($bonus['translations'] as $t) {
                                                    if ($t->locale == $lang['code'] && $t->key == "bonus_title") {
                                                        $translate[$lang['code']]['bonus_title'] = $t->value;
                                                    }

                                                    if ($t->locale == $lang['code'] && $t->key == "short_description") {
                                                        $translate[$lang['code']]['short_description'] = $t->value;
                                                    }
                                                }
                                            }
                                            ?>
                                        <div class="lang-form d-none" id="{{ $lang['code'] }}-form">
                                            <div class="row">
                                                <div class="col-lg-4">
                                                    <div class="form-floating form-floating__icon mb-30 mt-30">
                                                        <input type="text" name="bonus_title[]" class="form-control"
                                                               placeholder="{{translate('bonus_title')}} "
                                                               value="{{$translate[$lang['code']]['bonus_title'] ?? ''}}">
                                                        <label>{{translate('bonus_title')}}
                                                            ({{ strtoupper($lang['code']) }})</label>
                                                        <span class="material-icons">title</span>
                                                    </div>
                                                </div>

                                                <div class="col-lg-8">
                                                    <div class="form-floating form-floating__icon mb-30 mt-30">
                                                        <input type="text" name="short_description[]"
                                                               class="form-control"
                                                               value="{{$translate[$lang['code']]['short_description'] ?? ''}}"
                                                               placeholder="{{translate('short_description')}} ">
                                                        <label>{{translate('short_description')}}
                                                            ({{ strtoupper($lang['code']) }})</label>
                                                        <span class="material-icons">subtitles</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="lang[]" value="{{$lang['code']}}">
                                        </div>
                                    @endforeach
                                @else
                                    <div class="lang-form">
                                        <div class="col-lg-4">
                                            <div class="form-floating form-floating__icon mb-30 mt-30">
                                                <input type="text" name="bonus_title[]" class="form-control"
                                                       value="{{old('name')}}"
                                                       placeholder="{{translate('bonus_title')}}" required>
                                                <label>{{translate('bonus_title')}}
                                                    ({{ translate('default') }})</label>
                                                <span class="material-icons">title</span>
                                            </div>
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="form-floating form-floating__icon mb-30 mt-30 lang-form">
                                                <input type="text" name="short_description[]" class="form-control"
                                                       value="{{old('name')}}"
                                                       placeholder="{{translate('short_description')}}" required>
                                                <label>{{translate('short_description')}}
                                                    ({{ translate('default') }})</label>
                                                <span class="material-icons">subtitles</span>
                                            </div>
                                        </div>
                                        <input type="hidden" name="lang[]" value="default">
                                    </div>
                                @endif
                                <div class="row">
                                    <div class="col-lg-4 mb-30">
                                        <select class="select-amount theme-input-style" id="amount_type"
                                                name="bonus_amount_type" required>
                                            <option
                                                    value="percent" {{$bonus->bonus_amount_type == 'percent' ? 'selected' : ''}}>{{translate('percentage')}}</option>
                                            <option
                                                    value="amount" {{$bonus->bonus_amount_type == 'amount' ? 'selected' : ''}}>{{translate('fixed_amount')}}</option>
                                        </select>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-floating form-floating__icon mb-30">
                                            <input class="form-control" name="bonus_amount" id="amount"
                                                   placeholder="Ex: 50%" step="any" min="0"
                                                   value="{{$bonus->bonus_amount}}" type="number" required>
                                            <label id="amount_label">{{$bonus->bonus_amount_type == 'percent' ? 'Bonus ('. '%)' : "Bonus". ' ('. (currency_symbol(). ')')}}</label>
                                            <span class="material-icons">price_change</span>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-floating form-floating__icon mb-30">
                                            <input class="form-control" name="minimum_add_amount"
                                                   placeholder="{{translate('minimum_add_amount')}}"
                                                   value="{{$bonus->minimum_add_amount}}" type="number" step="any"
                                                   required>
                                            <label>{{translate('minimum_add_amount')}}</label>
                                            <span class="material-icons">price_change</span>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-floating form-floating__icon mb-30">
                                            <input class="form-control" name="maximum_bonus_amount"
                                                   placeholder="{{translate('maximum_bonus_amount')}}"
                                                   value="{{$bonus->maximum_bonus_amount}}" min="0" type="number"
                                                   step="any"
                                                   id="max_amount"
                                                   required {{$bonus->bonus_amount_type == 'amount' ? 'disabled' : ''}}>
                                            <label>{{translate('maximum_bonus_amount')}}</label>
                                            <span class="material-icons">price_change</span>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="mb-30">
                                            <div class="form-floating">
                                                <input type="date" class="form-control" name="start_date"
                                                       value="{{$bonus->start_date}}" id="start_date">
                                                <label>{{translate('Start_Date')}}</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="mb-30">
                                            <div class="form-floating">
                                                <input type="date" class="form-control" name="end_date"
                                                       value="{{$bonus->end_date}}"
                                                       id="end_date">
                                                <label>{{translate('End_Date')}}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex gap-4 flex-wrap justify-content-end">
                                    <button type="reset" class="btn btn--secondary">{{translate('Reset')}}</button>
                                    <button type="submit" class="btn btn--primary">{{translate('Update')}}</button>
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
    <script>
        "use strict"
        $(document).ready(function () {
            $('.select-amount').select2({});

            const amountType = $('#amount_type');
            amountType.on('change', function () {
                if (amountType.val() == 'amount') {
                    $("#amount_label").text("Bonus ({{currency_symbol()}})");
                    $('#max_amount').prop("disabled", true);
                    $('#max_amount').val(0);
                } else {
                    $("#amount_label").text("Bonus (%)")
                    $('#max_amount').removeAttr("disabled");
                }
            });

            const startInput = document.getElementById('start_date');
            const endInput = document.getElementById('end_date');
            const today = new Date();
            const formattedToday = today.toISOString().split('T')[0];

            startInput.setAttribute('min', formattedToday);
            endInput.setAttribute('min', formattedToday);


        });
    </script>

    <script>
        $(".lang_link").on('click', function (e) {
            e.preventDefault();
            $(".lang_link").removeClass('active');
            $(".lang-form").addClass('d-none');
            $(this).addClass('active');

            let form_id = this.id;
            let lang = form_id.substring(0, form_id.length - 5);
            $("#" + lang + "-form").removeClass('d-none');
        });
    </script>
@endpush
