@extends('adminmodule::layouts.master')

@section('title',translate('category_update'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/select.dataTables.min.css"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('category_update')}}</h2>
                    </div>

                    <div class="card category-setup mb-30">
                        <div class="card-body p-30">
                            <form action="{{route('admin.category.update',[$category->id])}}" method="post"
                                  enctype="multipart/form-data">
                                @csrf
                                @method('put')
                                @php($language= Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name','system_language')->first())
                                @php($default_lang = str_replace('_', '-', app()->getLocale()))
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
                                <div class="row">
                                    <div class="col-lg-8 mb-5 mb-lg-0">
                                        <div class="d-flex flex-column">
                                            @if ($language)
                                                <div class="form-floating form-floating__icon mb-30 lang-form" id="default-form">
                                                    <input type="text" name="name[]" class="form-control"
                                                           placeholder="{{translate('category_name')}}"
                                                           value="{{$category?->getRawOriginal('name')}}" required>
                                                    <label>{{translate('category_name')}} ({{ translate('default') }}
                                                        )</label>
                                                    <span class="material-icons">subtitles</span>
                                                </div>
                                                <input type="hidden" name="lang[]" value="default">
                                                @foreach ($language?->live_values as $lang)
                                                        <?php
                                                        if (count($category['translations'])) {
                                                            $translate = [];
                                                            foreach ($category['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == "name") {
                                                                    $translate[$lang['code']]['name'] = $t->value;
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                    <div class="form-floating form-floating__icon mb-30 d-none lang-form"
                                                         id="{{$lang['code']}}-form">
                                                        <input type="text" name="name[]" class="form-control"
                                                               placeholder="{{translate('category_name')}}"
                                                               value="{{$translate[$lang['code']]['name']??''}}">
                                                        <label>{{translate('category_name')}}
                                                            ({{strtoupper($lang['code'])}})</label>
                                                        <span class="material-icons">subtitles</span>
                                                    </div>
                                                    <input type="hidden" name="lang[]" value="{{$lang['code']}}">
                                                @endforeach
                                            @else
                                                <div class="form-floating form-floating__icon mb-30">
                                                    <input type="text" name="name[]" class="form-control"
                                                           placeholder="{{translate('category_name')}}"
                                                           value="{{$category['name']}}" required>
                                                    <label>{{translate('category_name')}}</label>
                                                    <span class="material-icons">subtitles</span>
                                                </div>
                                                <input type="hidden" name="lang[]" value="default">
                                            @endif


                                            <select class="zone-select theme-input-style w-100" name="zone_ids[]"
                                                    multiple="multiple" id="zone_selector__select">
                                                <option value="all">{{translate('Select All')}}</option>
                                                @foreach($zones as $zone)
                                                    <option
                                                        value="{{$zone['id']}}" {{in_array($zone->id,$category->zones->pluck('id')->toArray())?'selected':''}}>{{$zone->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="d-flex  gap-3 gap-xl-5">
                                            <p class="opacity-75 max-w220">{{translate('image_format_-_jpg,_png,_jpeg,_gif_image
                                                size_-_
                                                maximum_size_2_MB_Image_Ratio_-_1:1')}}</p>
                                            <div>
                                                <div class="upload-file">
                                                    <input type="file" class="upload-file__input" name="image" accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*">
                                                    <div class="upload-file__img">
                                                        <img src="{{$category->image_full_path}}" alt="{{translate('category image')}}">
                                                    </div>
                                                    <span class="upload-file__edit">
                                                        <span class="material-icons">edit</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex justify-content-end gap-20 mt-30">
                                            <button class="btn btn--secondary"
                                                    type="reset">{{translate('reset')}}</button>
                                            <button class="btn btn--primary" type="submit">{{translate('update')}}
                                            </button>
                                        </div>
                                    </div>
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
    <script src="{{asset('public/assets/admin-module/plugins/select2/select2.min.js')}}"></script>
    <script src="{{asset('public/assets/category-module/js/category/edit.js')}}"></script>
    <script src="{{asset('public/assets/admin-module/plugins/dataTables/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('public/assets/admin-module/plugins/dataTables/dataTables.select.min.js')}}"></script>

    <script>
        "use strict"

        $('#zone_selector__select').on('change', function() {
            var selectedValues = $(this).val();
            if (selectedValues !== null && selectedValues.includes('all')) {
                $(this).find('option').not(':disabled').prop('selected', 'selected');
                $(this).find('option[value="all"]').prop('selected', false);
            }
        });

    </script>
@endpush
