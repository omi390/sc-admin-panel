@extends('adminmodule::layouts.master')

@section('title',translate('sub_category_update'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('assets/admin-module')}}/plugins/select2/select2.min.css"/>
    <link rel="stylesheet" href="{{asset('assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="{{asset('assets/admin-module')}}/plugins/dataTables/select.dataTables.min.css"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('sub_category_update')}}</h2>
                    </div>

                    <div class="card category-setup mb-30">
                        <div class="card-body p-30">
                            <form action="{{route('admin.sub-category.update',[$subCategory->id])}}" method="post"
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
                                            <select class="js-select theme-input-style w-100" name="parent_id">
                                                <option value="0" selected disabled>
                                                    {{translate('Select_Category_Name')}}
                                                </option>
                                                @foreach($mainCategories as $item)
                                                    <option
                                                        value="{{$item['id']}}" {{$subCategory->parent_id==$item->id?'selected':''}}>
                                                        {{$item->name}}
                                                    </option>
                                                @endforeach
                                            </select>

                                            @if($language)
                                                <div class="lang-form" id="default-form">
                                                    <div class="form-floating form-floating__icon mb-30 mt-30">
                                                        <input type="text" name="name[]" class="form-control"
                                                               placeholder="{{translate('sub_category_name')}}"
                                                               value="{{$subCategory?->getRawOriginal('name')}}" required>
                                                        <label>{{translate('sub_category_name')}}
                                                            ({{ translate('default') }})</label>
                                                        <span class="material-icons">subtitles</span>
                                                    </div>

                                                    <div class="form-floating mb-30">
                                                <textarea type="text" name="short_description[]" class="form-control resize-none" required
                                                >{{$subCategory?->getRawOriginal('description')}}</textarea>
                                                        <label>{{translate('short_description')}}
                                                            ({{ translate('default') }})</label>
                                                    </div>
                                                </div>

                                                <input type="hidden" name="lang[]" value="default">
                                                @foreach ($language?->live_values as $lang)
                                                        <?php
                                                        if (count($subCategory['translations'])) {
                                                            $translate = [];
                                                            foreach ($subCategory['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == "name") {
                                                                    $translate[$lang['code']]['name'] = $t->value;
                                                                }

                                                                if ($t->locale == $lang['code'] && $t->key == "description") {
                                                                    $translate[$lang['code']]['description'] = $t->value;
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                    <div class="lang-form d-none" id="{{ $lang['code'] }}-form">
                                                        <div class="form-floating form-floating__icon mb-30 mt-30">
                                                            <input type="text" name="name[]" class="form-control"
                                                                   placeholder="{{translate('sub_category_name')}} "
                                                                   value="{{$translate[$lang['code']]['name']??''}}">
                                                            <label>{{translate('sub_category_name')}}
                                                                ({{ strtoupper($lang['code']) }})</label>
                                                            <span class="material-icons">subtitles</span>
                                                        </div>

                                                        <div class="form-floating mb-30">
                                                            <textarea type="text" name="short_description[]"
                                                                      class="form-control resize-none">{{$translate[$lang['code']]['description']??''}}</textarea>
                                                            <label>{{translate('short_description')}}
                                                                ({{ strtoupper($lang['code']) }})</label>
                                                        </div>
                                                        <input type="hidden" name="lang[]" value="{{$lang['code']}}">
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="form-floating form-floating__icon mb-30 mt-30 lang-form">
                                                    <input type="text" name="name[]" class="form-control"
                                                           value="{{$subCategory->name}}"
                                                           placeholder="{{translate('sub_category_name')}}" required>
                                                    <label>{{translate('sub_category_name')}}
                                                        ({{ translate('default') }})</label>
                                                    <span class="material-icons">subtitles</span>
                                                </div>

                                                <div class="form-floating mb-30">
                                                <textarea type="text" name="short_description[]" class="form-control resize-none"
                                                          required>{{$subCategory->description}}</textarea>
                                                    <label>{{translate('short_description')}}
                                                    </label>
                                                </div>

                                                <input type="hidden" name="lang[]" value="default">
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="d-flex flex-column gap-3">
                                            <label class="form-label fw-bold">{{translate('image')}}</label>
                                            <label class="form-label small text-muted">{{translate('image_url')}}</label>
                                            <input type="url" class="form-control" name="image_url"
                                                   id="subcategory-image-url" placeholder="https://..."
                                                   value="{{ (\Illuminate\Support\Str::startsWith($subCategory->getRawOriginal('image') ?? '', 'http') ? $subCategory->getRawOriginal('image') : '') }}">
                                            <div class="mt-2 text-center">
                                                <img src="{{ $subCategory->image_full_path ?? asset('public/assets/admin-module/img/media/upload-file.png') }}" alt="" id="subcategory-image-preview" class="img-fluid rounded" style="max-height: 120px;">
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
    <script src="{{asset('assets/admin-module/plugins/select2/select2.min.js')}}"></script>
    <script src="{{asset('assets/category-module/js/sub-category/edit.js')}}"></script>
    <script src="{{asset('assets/admin-module/plugins/dataTables/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/admin-module/plugins/dataTables/dataTables.select.min.js')}}"></script>
    <script>
        // Sub-category image URL: preview when typing
        $('#subcategory-image-url').on('input', function() {
            var url = $(this).val();
            if (url && (url.startsWith('http://') || url.startsWith('https://'))) {
                $('#subcategory-image-preview').attr('src', url);
            }
        });
    </script>
@endpush
