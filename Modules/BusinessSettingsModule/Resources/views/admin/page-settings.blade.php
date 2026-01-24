@extends('adminmodule::layouts.master')

@section('title',translate('page_setup'))

@push('css_or_js')

@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('page_setup')}}</h2>
                    </div>

                    <div class="mb-3">
                        <ul class="nav nav--tabs nav--tabs__style2">
                            @foreach($dataValues as $pageData)
                                <li class="nav-item">
                                    <a href="{{url()->current()}}?web_page={{$pageData->key}}"
                                       class="nav-link {{$webPage==$pageData->key?'active':''}}">
                                        {{translate($pageData->key)}}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    @php($language= Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name','system_language')->first())
                    @php($defaultLanguage = str_replace('_', '-', app()->getLocale()))
                    @if($language)
                        <ul class="nav nav--tabs border-color-primary mb-4">
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
                    @foreach($dataValues as $pageData)
                        <div class="tab-content">
                            <div class="tab-pane fade {{$webPage==$pageData->key?'active show':''}}">
                                <div class="card">
                                    <form action="{{route('admin.business-settings.set-pages-setup')}}" method="POST">
                                        @csrf
                                        <div class="card-header page-settings">
                                            <h4 class="page-title">{{translate($pageData->key)}}</h4>
                                            @if(!in_array($pageData->key,['about_us','privacy_policy', 'terms_and_conditions']))
                                                <label class="switcher">
                                                    <input class="switcher_input"
                                                           type="checkbox"
                                                           name="is_active"
                                                           {{$pageData->is_active?'checked':''}} value="1">
                                                    <span class="switcher_control"></span>
                                                </label>
                                            @else
                                                <input name="is_active" value="1" class="hide-div">
                                            @endif
                                        </div>
                                        <div class="card-body p-30">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    @if ($language)
                                                        <div class="mb-30 dark-support lang-form default-form">
                                                            <input name="page_name" value="{{$pageData->key}}"
                                                                   class="hide-div">
                                                            <textarea class="ckeditor" required
                                                                      name="page_content[]">{!! $pageData?->getRawOriginal('value') !!}</textarea>
                                                        </div>
                                                        <input type="hidden" name="lang[]" value="default">
                                                        @foreach ($language?->live_values ?? [] as $lang)
                                                                <?php
                                                                if (count($pageData['translations'])) {
                                                                    $translate = [];
                                                                    foreach ($pageData['translations'] as $t) {
                                                                        if ($t->locale == $lang['code'] && $t->key == $pageData->key) {
                                                                            $translate[$lang['code']][$pageData->key] = $t->value;
                                                                        }
                                                                    }
                                                                }
                                                                ?>
                                                            <div
                                                                class="form-floating mb-30 d-none lang-form {{$lang['code']}}-form"
                                                            >
                                                               <textarea class="ckeditor"
                                                                         name="page_content[]">
                                                                   {!! $translate[$lang['code']][$pageData->key] ?? '' !!}
                                                               </textarea>
                                                            </div>
                                                            <input type="hidden" name="lang[]"
                                                                   value="{{$lang['code']}}">
                                                        @endforeach
                                                    @else
                                                        <div class="mb-30 dark-support lang-form default-form">
                                                            <input name="page_name" value="{{$pageData->key}}"
                                                                   class="hide-div">
                                                            <textarea class="ckeditor"
                                                                      name="page_content[]">{!! $pageData?->getRawOriginal('live_values') !!}</textarea>
                                                        </div>
                                                        <input type="hidden" name="lang[]" value="default">
                                                    @endif
                                                </div>
                                                @can('page_update')
                                                    <div class="d-flex justify-content-end">
                                                        <button class="btn btn--primary demo_check">
                                                            {{translate('update')}}
                                                        </button>
                                                    </div>
                                                @endcan
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{asset('public/assets/admin-module/plugins/tinymce/tinymce.min.js')}}"></script>

    <script>
        "use strict";

        $(document).ready(function () {
            tinymce.init({
                selector: 'textarea.ckeditor'
            });
        });

        $('.switcher_input').on('click', function () {
            $(this).submit()
        });

        $(".lang_link").on('click', function (e) {
            e.preventDefault();
            $(".lang_link").removeClass('active');
            $(".lang-form").addClass('d-none');
            $(this).addClass('active');

            let form_id = this.id;
            let lang = form_id.substring(0, form_id.length - 5);
            console.log(lang);
            $("." + lang + "-form").removeClass('d-none');
        });
    </script>
@endpush
