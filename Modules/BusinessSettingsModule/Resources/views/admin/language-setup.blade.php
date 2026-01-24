@extends('adminmodule::layouts.master')

@section('title',translate('Language Setup'))

@push('css_or_js')

@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Language_Setup')}}</h2>
                    </div>

                    <?php
                    $options = [
                        'af' => translate('Afrikaans'),
                        'sq' => translate('Albanian - shqip'),
                        'am' => translate('Amharic - አማርኛ'),
                        'ar' => translate('Arabic - العربية'),
                        'an' => translate('Aragonese - aragonés'),
                        'hy' => translate('Armenian - հայերեն'),
                        'ast' => translate('Asturian - asturianu'),
                        'az' => translate('Azerbaijani - azərbaycan dili'),
                        'eu' => translate('Basque - euskara'),
                        'be' => translate('Belarusian - беларуская'),
                        'bn' => translate('Bengali - বাংলা'),
                        'bs' => translate('Bosnian - bosanski'),
                        'br' => translate('Breton - brezhoneg'),
                        'bg' => translate('Bulgarian - български'),
                        'ca' => translate('Catalan - català'),
                        'ckb' => translate('Central Kurdish - کوردی (دەستنوسی عەرەبی)'),
                        'zh' => translate('Chinese - 中文'),
                        'zh-HK' => translate('Chinese (Hong Kong) - 中文（香港）'),
                        'zh-CN' => translate('Chinese (Simplified) - 中文（简体）'),
                        'zh-TW' => translate('Chinese (Traditional) - 中文（繁體）'),
                        'co' => translate('Corsican'),
                        'hr' => translate('Croatian - hrvatski'),
                        'cs' => translate('Czech - čeština'),
                        'da' => translate('Danish - dansk'),
                        'nl' => translate('Dutch - Nederlands'),
                        'en-AU' => translate('English (Australia)'),
                        'en-CA' => translate('English (Canada)'),
                        'en-IN' => translate('English (India)'),
                        'en-NZ' => translate('English (New Zealand)'),
                        'en-ZA' => translate('English (South Africa)'),
                        'en-GB' => translate('English (United Kingdom)'),
                        'en-US' => translate('English (United States)'),
                        'eo' => translate('Esperanto - esperanto'),
                        'et' => translate('Estonian - eesti'),
                        'fo' => translate('Faroese - føroyskt'),
                        'fil' => translate('Filipino'),
                        'fi' => translate('Finnish - suomi'),
                        'fr' => translate('French - français'),
                        'fr-CA' => translate('French (Canada) - français (Canada)'),
                        'fr-FR' => translate('French (France) - français (France)'),
                        'fr-CH' => translate('French (Switzerland) - français (Suisse)'),
                        'gl' => translate('Galician - galego'),
                        'ka' => translate('Georgian - ქართული'),
                        'de' => translate('German - Deutsch'),
                        'de-AT' => translate('German (Austria) - Deutsch (Österreich)'),
                        'de-DE' => translate('German (Germany) - Deutsch (Deutschland)'),
                        'de-LI' => translate('German (Liechtenstein) - Deutsch (Liechtenstein)'),
                        'de-CH' => translate('German (Switzerland) - Deutsch (Schweiz)'),
                        'el' => translate('Greek - Ελληνικά'),
                        'gn' => translate('Guarani'),
                        'gu' => translate('Gujarati - ગુજરાતી'),
                        'ha' => translate('Hausa'),
                        'haw' => translate('Hawaiian - ʻŌlelo Hawaiʻi'),
                        'he' => translate('Hebrew - עברית'),
                        'hi' => translate('Hindi - हिन्दी'),
                        'hu' => translate('Hungarian - magyar'),
                        'is' => translate('Icelandic - íslenska'),
                        'id' => translate('Indonesian - Indonesia'),
                        'ia' => translate('Interlingua'),
                        'ga' => translate('Irish - Gaeilge'),
                        'it' => translate('Italian - italiano'),
                        'it-IT' => translate('Italian (Italy) - italiano (Italia)'),
                        'it-CH' => translate('Italian (Switzerland) - italiano (Svizzera)'),
                        'ja' => translate('Japanese - 日本語'),
                        'kn' => translate('Kannada - ಕನ್ನಡ'),
                        'kk' => translate('Kazakh - қазақ тілі'),
                        'km' => translate('Khmer - ខ្មែរ'),
                        'ko' => translate('Korean - 한국어'),
                        'ku' => translate('Kurdish - Kurdî'),
                        'ky' => translate('Kyrgyz - кыргызча'),
                        'lo' => translate('Lao - ລາວ'),
                        'la' => translate('Latin'),
                        'lv' => translate('Latvian - latviešu'),
                        'ln' => translate('Lingala - lingála'),
                        'lt' => translate('Lithuanian - lietuvių'),
                        'mk' => translate('Macedonian - македонски'),
                        'ms' => translate('Malay - Bahasa Melayu'),
                        'ml' => translate('Malayalam - മലയാളം'),
                        'mt' => translate('Maltese - Malti'),
                        'mr' => translate('Marathi - मराठी'),
                        'mn' => translate('Mongolian - монгол'),
                        'ne' => translate('Nepali - नेपाली'),
                        'no' => translate('Norwegian - norsk'),
                        'nb' => translate('Norwegian Bokmål - norsk bokmål'),
                        'nn' => translate('Norwegian Nynorsk - nynorsk'),
                        'oc' => translate('Occitan'),
                        'or' => translate('Oriya - ଓଡ଼ିଆ'),
                        'om' => translate('Oromo - Oromoo'),
                        'ps' => translate('Pashto - پښتو'),
                        'fa' => translate('Persian - فارسی'),
                        'pl' => translate('Polish - polski'),
                        'pt' => translate('Portuguese - português'),
                        'pt-BR' => translate('Portuguese (Brazil) - português (Brasil)'),
                        'pt-PT' => translate('Portuguese (Portugal) - português (Portugal)'),
                        'pa' => translate('Punjabi - ਪੰਜਾਬੀ'),
                        'qu' => translate('Quechua'),
                        'ro' => translate('Romanian - română'),
                        'mo' => translate('Romanian (Moldova) - română (Moldova)'),
                        'rm' => translate('Romansh - rumantsch'),
                        'ru' => translate('Russian - русский'),
                        'gd' => translate('Scottish Gaelic'),
                        'sr' => translate('Serbian - српски'),
                        'sh' => translate('Serbo-Croatian - Srpskohrvatski'),
                        'sn' => translate('Shona - chiShona'),
                        'sd' => translate('Sindhi'),
                        'si' => translate('Sinhala - සිංහල'),
                        'sk' => translate('Slovak - slovenčina'),
                        'sl' => translate('Slovenian - slovenščina'),
                        'so' => translate('Somali - Soomaali'),
                        'st' => translate('Southern Sotho'),
                        'es' => translate('Spanish - español'),
                        'es-AR' => translate('Spanish (Argentina) - español (Argentina)'),
                        'es-419' => translate('Spanish (Latin America) - español (Latinoamérica)'),
                        'es-MX' => translate('Spanish (Mexico) - español (México)'),
                        'es-ES' => translate('Spanish (Spain) - español (España)'),
                        'es-US' => translate('Spanish (United States) - español (Estados Unidos)'),
                        'su' => translate('Sundanese'),
                        'sw' => translate('Swahili - Kiswahili'),
                        'sv' => translate('Swedish - svenska'),
                        'tg' => translate('Tajik - тоҷикӣ'),
                        'ta' => translate('Tamil - தமிழ்'),
                        'tt' => translate('Tatar'),
                        'te' => translate('Telugu - తెలుగు'),
                        'th' => translate('Thai - ไทย'),
                        'ti' => translate('Tigrinya - ትግርኛ'),
                        'to' => translate('Tongan - lea fakatonga'),
                        'tr' => translate('Turkish - Türkçe'),
                        'tk' => translate('Turkmen'),
                        'tw' => translate('Twi'),
                        'uk' => translate('Ukrainian - українська'),
                        'ur' => translate('Urdu - اردو'),
                        'ug' => translate('Uyghur'),
                        'uz' => translate('Uzbek - o‘zbek'),
                        'vi' => translate('Vietnamese - Tiếng Việt'),
                        'wa' => translate('Walloon - wa'),
                        'cy' => translate('Welsh - Cymraeg'),
                        'fy' => translate('Western Frisian'),
                        'xh' => translate('Xhosa'),
                        'yi' => translate('Yiddish'),
                        'yo' => translate('Yoruba - Èdè Yorùbá'),
                        'zu' => translate('Zulu - isiZulu'),
                    ];
                    ?>
                    <div class="card">
                        <div class="card-body p-30">
                            <form action="{{route('admin.language.store')}}" method="post">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-30">
                                            <select class="js-select" name="code" id="user_id" required>
                                                <option value="">{{translate('Select One')}}</option>
                                                @foreach($options as $value => $label)
                                                    <option value="{{$value}}">{{$label}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-30">
                                            <div class="rounded form-control d-flex min-h45">
                                                <div class="d-flex align-items-center gap-4 gap-xl-5">
                                                    <div class="custom-radio">
                                                        <input type="radio" id="ltr" name="direction" value="ltr"
                                                               checked="">
                                                        <label for="ltr">{{translate('LTR')}}</label>
                                                    </div>
                                                    <div class="custom-radio">
                                                        <input type="radio" id="rtl" name="direction" value="rtl">
                                                        <label for="rtl">{{translate('RTL')}}</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @can('configuration_add')
                                        <div class="col-12">
                                            <div class="d-flex justify-content-end gap-3">
                                                <button class="btn btn-secondary"
                                                        type="reset">{{translate('reset')}}</button>
                                                <button class="btn btn--primary"
                                                        type="submit">{{translate('submit')}}</button>
                                            </div>
                                        </div>
                                    @endcan
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                <form action="#"
                                      class="search-form search-form_style-two"
                                      method="GET">
                                    @csrf
                                    <div class="input-group search-form__input_group">
                                    <span class="search-form__icon">
                                        <span class="material-icons">search</span>
                                    </span>
                                        <input type="search" class="theme-input-style search-form__input" name="search"
                                               placeholder="{{translate('search_here')}}"
                                               value="{{ request()?->search ?? null }}">
                                    </div>
                                    <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table id="example" class="table align-middle">
                                    <thead class="text-nowrap">
                                    <tr>
                                        <th>{{translate('ID')}}</th>
                                        <th>{{translate('Language')}}</th>
                                        <th>{{translate('Translated Content')}}</th>
                                        @can('configuration_manage_status')
                                            <th>{{translate('Default Status')}}</th>
                                            <th>{{translate('Status')}}</th>
                                        @endcan
                                        @canany(['configuration_delete', 'configuration_update'])
                                            <th>{{translate('action')}}</th>
                                        @endcan
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @php
                                        $searchValue = request()->search;

                                        $collection = collect($system_language['live_values'] ?? []);

                                        $filteredValues = $collection;

                                        if (!empty($searchValue)) {
                                            $filteredValues = $filteredValues->filter(function ($item) use ($searchValue) {
                                                return isset($item['code']) && $item['code'] == $searchValue;
                                            });
                                        }
                                        $filteredValues = $filteredValues->all();
                                    @endphp
                                    @foreach($filteredValues ?? [] as $key =>$data)
                                        <tr>
                                            <td>{{$key+1}}</td>
                                            <td>{{$data['code']}}</td>
                                            <td>
                                                <a href="{{( env('APP_MODE') == 'demo') ? 'javascript:' :route('admin.language.translate',[$data['code']]) }}"
                                                   class="btn border">{{translate('View Translations')}}</a>
                                            </td>
                                            @can('configuration_manage_status')
                                                <td>
                                                    @if($data['default'] == 'true' && array_key_exists('default', $data))
                                                        <span
                                                            class="btn default-lang">{{translate(('Default Language'))}}</span>
                                                    @else
                                                        <button class="btn btn--light default-language"
                                                                data-message="{{translate('want_to_update_default_status')}}"
                                                                data-route="{{route('admin.language.update-default-status',['code' =>$data['code']])}}"
                                                        >{{translate('Mark As Default')}}</button>
                                                    @endif
                                                </td>
                                            @endcan
                                            @can('configuration_manage_status')
                                                <td>
                                                    @if (array_key_exists('default', $data) && $data['default']==true)
                                                        <label class="switcher default-language-status"
                                                               data-bs-toggle="modal"
                                                               data-bs-target="#deactivateAlertModal">
                                                            <input class="switcher_input"
                                                                   checked disabled
                                                                   type="checkbox" {{$data['status']?'checked':''}}>
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    @elseif(array_key_exists('default', $data) && $data['default']==false)
                                                        <label class="switcher" data-bs-toggle="modal"
                                                               data-bs-target="#deactivateAlertModal">
                                                            <input class="switcher_input language-status-update"
                                                                   data-route="{{route('admin.language.update-status',['code' =>$data['code']])}}"
                                                                   data-message="{{translate('want_to_update_status')}}"
                                                                   type="checkbox" {{$data['status']?'checked':''}}>
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    @endif
                                                </td>
                                            @endcan
                                            @canany(['configuration_delete', 'configuration_update'])
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        @if($data['code']!='en')
                                                            @can('configuration_update')
                                                                <a data-bs-toggle="modal"
                                                                   data-bs-target="{{ (env('APP_ENV') != 'demo') ? '#lang-modal-update-' . $data['code'] : '' }}"
                                                                   class="action-btn btn--light-primary demo_check"
                                                                   style="--size: 30px">
                                                                    <span class="material-icons">edit</span>
                                                                </a>
                                                            @endcan
                                                            @if($data['default'] != true)
                                                                @can('configuration_delete')
                                                                    <button type="button"
                                                                            data-id="delete-{{$data['code']}}"
                                                                            class="action-btn btn--danger demo_check"
                                                                            style="--size: 30px">
                                                                    <span
                                                                        class="material-symbols-outlined">delete</span>
                                                                    </button>
                                                                @endcan
                                                            @endif
                                                        @endif
                                                    </div>
                                                    <form
                                                        action="{{route('admin.language.delete',[$data['code']])}}"
                                                        method="post" id="delete-{{$data['code']}}" class="hidden">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </td>
                                            @endcan
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @foreach($system_language['live_values'] ?? [] as $key =>$data)
        <div class="modal fade" id="lang-modal-update-{{$data['code']}}" tabindex="-1" role="dialog"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{translate('new_language')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{route('admin.language.update')}}" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <input type="hidden" name="code" value="{{ $data['code'] }}">
                                        <label for="message-text"
                                               class="col-form-label">{{translate('language')}}</label>
                                        <select disabled id="lang_code" class="form-control js-select2-custom">
                                            @foreach($options as $value => $label)
                                                <option
                                                    value="{{$value}}" {{ $data['code'] == $value?'selected':'' }}>{{$label}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="col-form-label">{{translate('direction')}} :</label>
                                        <select class="form-control" name="direction">
                                            <option
                                                value="ltr" {{isset($data['direction'])?$data['direction']=='ltr'?'selected':'':''}}>
                                                {{translate('LTR')}}
                                            </option>
                                            <option
                                                value="rtl" {{isset($data['direction'])?$data['direction']=='rtl'?'selected':'':''}}>
                                                {{translate('RTL')}}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">{{translate('close')}}</button>
                            <button type="submit" class="btn btn--primary">{{translate('update')}} <i
                                    class="fa fa-plus"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('script')
    <script>
        function default_language_status_alert() {
            toastr.warning('{{translate("default_language_can_not_be_deactive") }}!');
        }
    </script>

    <script>
        "use strict";

        $('.action-btn.btn--danger').on('click', function () {
            let id = $(this).data('id');
            let message = "{{translate('delete_this_language')}}?"
            @if(env('APP_ENV')!='demo')
            form_alert(id, message)
            @endif
        });

        $('.default-language').on('click', function () {
            let route = $(this).data('route');
            let message = $(this).data('message');
            default_status_change(route, message)
        });

        $('.language-status-update').on('click', function () {
            let route = $(this).data('route');
            let message = $(this).data('message');
            route_alert(route, message)
        });


        $('.default-language-status').on('click', function () {
            default_language_status_alert()
        });

        function default_status_change(route, message) {
            Swal.fire({
                title: "<?php echo e(translate('are_you_sure')); ?>?",
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'var(--c2)',
                confirmButtonColor: 'var(--c1)',
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Yes',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.get({
                        url: route,
                        dataType: 'json',
                        data: {},
                        beforeSend: function () {

                        },
                        success: function (data) {
                            console.log(data)
                            setTimeout(function () {
                                location.reload();
                            }, 1000);

                            toastr.success(data.message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        },
                        complete: function () {

                        },
                    });
                }
            })
        }
    </script>

@endpush
