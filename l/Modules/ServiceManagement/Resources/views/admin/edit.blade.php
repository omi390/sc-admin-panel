@extends('adminmodule::layouts.master')

@section('title',translate('service_update'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/select.dataTables.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/wysiwyg-editor/froala_editor.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/css/tags-input.min.css"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('update_service')}}</h2>
                    </div>

                    <div class="card">
                        <div class="card-body p-30">
                            <form action="{{route('admin.service.update',[$service->id])}}" method="post"
                                  enctype="multipart/form-data"
                                  id="service-add-form">
                                @csrf
                                @method('PUT')
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
                                <div id="form-wizard">
                                    <h3>{{translate('service_information')}}</h3>
                                    <section>
                                        <div class="row">
                                            <div class="col-lg-5 mb-5 mb-lg-0">
                                                @if($language)
                                                    <div class="form-floating form-floating__icon mb-30 lang-form" id="default-form">
                                                        <input type="text" name="name[]" class="form-control"
                                                               placeholder="{{translate('service_name')}}"
                                                               value="{{$service?->getRawOriginal('name')}}" required>
                                                        <label>{{translate('service_name')}} ({{ translate('default') }}
                                                            )</label>
                                                        <span class="material-icons">subtitles</span>
                                                    </div>
                                                    <input type="hidden" name="lang[]" value="default">
                                                    @foreach ($language?->live_values as $lang)
                                                            <?php
                                                            if (count($service['translations'])) {
                                                                $translate = [];
                                                                foreach ($service['translations'] as $t) {
                                                                    if ($t->locale == $lang['code'] && $t->key == "name") {
                                                                        $translate[$lang['code']]['name'] = $t->value;
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                        <div class="form-floating form-floating__icon mb-30 d-none lang-form"
                                                             id="{{$lang['code']}}-form">
                                                            <input type="text" name="name[]" class="form-control"
                                                                   placeholder="{{translate('service_name')}}"
                                                                   value="{{$translate[$lang['code']]['name']??''}}">
                                                            <label>{{translate('service_name')}}
                                                                ({{strtoupper($lang['code'])}})</label>
                                                            <span class="material-icons">subtitles</span>
                                                        </div>
                                                        <input type="hidden" name="lang[]" value="{{$lang['code']}}">
                                                    @endforeach
                                                @else
                                                    <div class="lang-form">
                                                        <div class="mb-30">
                                                            <div class="form-floating form-floating__icon">
                                                                <input type="text" class="form-control" name="name[]"
                                                                       placeholder="{{translate('service_name')}} *"
                                                                       required value="{{$service->name}}">
                                                                <label>{{translate('service_name')}} *</label>
                                                                <span class="material-icons">subtitles</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="lang[]" value="default">
                                                @endif
                                                <div class="mb-30">
                                                    <select class="js-select theme-input-style w-100" name="category_id"
                                                            id="category-id">
                                                        <option value="0" selected
                                                                disabled>{{translate('choose_category')}}</option>
                                                        @foreach($categories as $category)
                                                            <option
                                                                value="{{$category->id}}" {{$category->id==$service->category_id?'selected':''}}>
                                                                {{$category->name}}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-30" id="sub-category-selector">
                                                    <select class="js-select theme-input-style w-100"
                                                            name="sub_category_id"></select>
                                                </div>

                                                <div class="mb-30">
                                                    <div class="form-floating form-floating__icon">
                                                        <input type="text" class="form-control" name="tax" min="0"
                                                               max="100" step="0.01"
                                                               placeholder="{{translate('add_tax_percentage')}} *"
                                                               required="" value="{{$service->tax}}">
                                                        <label>{{translate('add_tax_percentage')}} *</label>
                                                        <span class="material-icons">percent</span>
                                                    </div>
                                                </div>

                                                <div class="mb-30">
                                                    <div class="form-floating form-floating__icon">
                                                        <input type="number" class="form-control"
                                                               name="min_bidding_price" min="0"
                                                               max="100" step="any"
                                                               placeholder="{{translate('min_bidding_price')}} *"
                                                               required="" value="{{$service->min_bidding_price}}">
                                                        <label>{{translate('min_bidding_price')}} *</label>
                                                        <span class="material-icons">price_change</span>
                                                    </div>
                                                </div>

                                                <div class="mb-30">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" name="tags"
                                                               placeholder="{{translate('Enter tags')}}"
                                                               value="{{implode(",",$tagNames)}}"
                                                               data-role="tagsinput">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 col-sm-5 mb-5 mb-sm-0">
                                                <div class="d-flex flex-column align-items-center gap-3">
                                                    <p class="mb-0">{{translate('thumbnail_image')}}</p>
                                                    <div>
                                                        <div class="upload-file">
                                                            <input type="file" class="upload-file__input"
                                                                   name="thumbnail" accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*">
                                                            <div class="upload-file__img">
                                                                <img src="{{$service->thumbnail_full_path}}"
                                                                     alt="{{translate('image')}}">
                                                            </div>
                                                            <span class="upload-file__edit">
                                                                <span class="material-icons">edit</span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <p class="opacity-75 max-w220 mx-auto">{{translate('Image format - jpg, png,
                                                        jpeg,
                                                        gif Image
                                                        Size -
                                                        maximum size 2 MB Image Ratio - 1:1')}}</p>
                                                </div>
                                            </div>
                                            <div class="col-lg-4 col-sm-7">
                                                <div class="d-flex flex-column align-items-center gap-3">
                                                    <p class="mb-0">{{translate('cover_image')}}</p>
                                                    <div>
                                                        <div class="upload-file">
                                                            <input type="file" class="upload-file__input"
                                                                   name="cover_image" accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*">
                                                            <div class="upload-file__img upload-file__img_banner">
                                                                <img alt="{{ translate('cover-image') }}"
                                                                     src="{{$service->cover_image_full_path}}">
                                                            </div>
                                                            <span class="upload-file__edit">
                                                                <span class="material-icons">edit</span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <p class="opacity-75 max-w220 mx-auto">{{translate('Image format - jpg, png,
                                                        jpeg, gif Image Size - maximum size 2 MB Image Ratio - 3:1')}}</p>
                                                </div>
                                            </div>
                                            @if($language)
                                                <div class="lang-form2" id="default-form2">
                                                    <div class="col-lg-12 mt-5">
                                                        <div class="mb-30">
                                                            <div class="form-floating">
                                                                <textarea type="text" class="form-control" required
                                                                          name="short_description[]">{{$service?->getRawOriginal('short_description')}}</textarea>
                                                                <label>{{translate('short_description')}}
                                                                    ({{translate('default')}}) *</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 mt-4 mt-md-5">
                                                        <label for="editor"
                                                               class="mb-2">{{translate('long_Description')}}
                                                            ({{translate('default')}})
                                                            <span class="text-danger">*</span></label>
                                                        <section id="editor" class="dark-support">
                                                            <textarea class="ckeditor" required
                                                                      name="description[]">{!! $service?->getRawOriginal('description') !!}</textarea>
                                                        </section>
                                                    </div>
                                                </div>
                                                @foreach ($language?->live_values as $lang)
                                                        <?php
                                                        if (count($service['translations'])) {
                                                            $translate = [];
                                                            foreach ($service['translations'] as $t) {
                                                                if ($t->locale == $lang['code'] && $t->key == "short_description") {
                                                                    $translate[$lang['code']]['short_description'] = $t->value;
                                                                }

                                                                if ($t->locale == $lang['code'] && $t->key == "description") {
                                                                    $translate[$lang['code']]['description'] = $t->value;
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                    <div class="d-none lang-form2" id="{{$lang['code']}}-form2">
                                                        <div class="col-lg-12 mt-5">
                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                        <textarea type="text" class="form-control"
                                                                  name="short_description[]">{{$translate[$lang['code']]['short_description']??''}}</textarea>
                                                                    <label>{{translate('short_description')}}
                                                                        ({{strtoupper($lang['code'])}}) *</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 mt-4 mt-md-5">
                                                            <label for="editor"
                                                                   class="mb-2">{{translate('long_Description')}}
                                                                ({{strtoupper($lang['code'])}})
                                                                <span class="text-danger">*</span></label>
                                                            <section id="editor" class="dark-support">
                                                                <textarea class="ckeditor"
                                                                          name="description[]">{!! $translate[$lang['code']]['description']??'' !!}</textarea>
                                                            </section>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="normal-form">
                                                    <div class="col-lg-12 mt-5">
                                                        <div class="mb-30">
                                                            <div class="form-floating">
                                                                <textarea type="text" class="form-control" required
                                                                          name="short_description[]">{{old('short_description')}}</textarea>
                                                                <label>{{translate('short_description')}} *</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 mt-4 mt-md-5">
                                                        <label for="editor"
                                                               class="mb-2">{{translate('long_Description')}}
                                                            <span class="text-danger">*</span></label>
                                                        <section id="editor" class="dark-support">
                                                            <textarea class="ckeditor" required
                                                                      name="description[]">{{old('description')}}</textarea>
                                                        </section>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </section>

                                    <h3>{{translate('price_variation')}}</h3>
                                    <section>
                                        <div class="d-flex flex-wrap gap-20 mb-3">
                                            <div class="form-floating flex-grow-1">
                                                <input type="text" class="form-control" name="variant_name"
                                                       id="variant-name"
                                                       placeholder="{{translate('add_variant')}} *" required="">
                                                <label>{{translate('add_variant')}} *</label>
                                            </div>
                                            <div class="form-floating flex-grow-1">
                                                <input type="number" class="form-control" name="variant_price"
                                                       id="variant-price"
                                                       placeholder="{{translate('price')}} *" required="" value="0">
                                                <label>{{translate('price')}} *</label>
                                            </div>
                                            <button type="button" class="btn btn--primary" id="service-ajax-variation">
                                                <span class="material-icons">add</span>
                                                {{translate('add')}}
                                            </button>
                                        </div>

                                        <div class="table-responsive p-01">
                                            <table class="table align-middle table-variation">
                                                <thead id="category-wise-zone" class="text-nowrap">
                                                <tr>
                                                    <th scope="col">{{translate('variations')}}</th>
                                                    <th scope="col">{{translate('default_price')}}</th>
                                                    @foreach($zones as $zone)
                                                        <th scope="col">{{$zone->name}}</th>
                                                    @endforeach
                                                    <th scope="col">{{translate('action')}}</th>
                                                </tr>
                                                </thead>
                                                <tbody id="variation-update-table">
                                                @include('servicemanagement::admin.partials._update-variant-data',['variants'=>$service->variations,'zones'=>$zones])
                                                </tbody>
                                            </table>

                                            <div id="new-variations-table"
                                                 class="{{session()->has('variations') && count(session('variations'))>0?'':'hide-div'}}">
                                                <label
                                                    class="badge badge-primary mb-10">{{translate('new_variations')}}</label>
                                                <table class="table align-middle table-variation">
                                                    <tbody id="variation-table">
                                                    @include('servicemanagement::admin.partials._variant-data',['zones'=>$zones])
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </section>
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
    <script src="{{asset('public/assets/admin-module')}}/js//tags-input.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/jquery-steps/jquery.steps.min.js"></script>
    <script src="{{asset('public/assets/admin-module/plugins/tinymce/tinymce.min.js')}}"></script>
    <script src="{{asset('public/assets/ckeditor/jquery.js')}}"></script>
    <script>
        "use strict";

        $(document).ready(function () {
            $('.js-select').select2();
        });

        $("#form-wizard").steps({
            headerTag: "h3",
            bodyTag: "section",
            transitionEffect: "slideLeft",
            autoFocus: true,
            onFinished: function (event, currentIndex) {
                $("#service-add-form")[0].submit();
            }
        });

        ajax_get('{{url('/')}}/admin/category/ajax-childes-only/{{$service->category_id}}?sub_category_id={{$service->sub_category_id}}', 'sub-category-selector')

        $("#service-ajax-variation").on('click', function () {
            let route = "{{route('admin.service.ajax-add-variant')}}";
            let id = "variation-table";
            ajax_variation(route, id);
        })

        function ajax_variation(route, id) {

            let name = $('#variant-name').val();
            let price = $('#variant-price').val();

            if (name.length > 0 && price >= 0) {
                $.get({
                    url: route,
                    dataType: 'json',
                    data: {
                        name: $('#variant-name').val(),
                        price: $('#variant-price').val(),
                    },
                    beforeSend: function () {
                    },
                    success: function (response) {
                        console.log(response.template)
                        if (response.flag == 0) {
                            toastr.info('Already added');
                        } else {
                            $('#new-variations-table').show();
                            $('#' + id).html(response.template);
                            $('#variant-name').val("");
                            $('#variant-price').val(0);
                        }
                    },
                    complete: function () {
                    },
                });
            } else {
                toastr.warning('{{translate('fields_are_required')}}');
            }
        }

        document.querySelectorAll('.service-ajax-remove-variant').forEach(function(element) {
            element.addEventListener('click', function() {
                var route = this.getAttribute('data-route');
                var id = this.getAttribute('data-id');
                ajax_remove_variant(route, id);
            });
        });


        function ajax_remove_variant(route, id) {
            Swal.fire({
                title: "{{translate('are_you_sure')}}?",
                text: "{{translate('want_to_remove_this_variation')}}",
                type: 'warning',
                showCloseButton: true,
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
                        success: function (response) {
                            console.log(response.template)
                            $('#' + id).html(response.template);
                        },
                        complete: function () {
                        },
                    });
                }
            })
        }


        $("#category-id").change(function () {
            let id = this.value;
            let route = "{{ url('/admin/category/ajax-childes/') }}/" + id;
            ajax_switch_category(route)
        });

        function ajax_switch_category(route) {
            $.get({
                url: route + '?service_id={{$service->id}}',
                dataType: 'json',
                data: {},
                beforeSend: function () {
                },
                success: function (response) {
                    console.log(response);
                    $('#sub-category-selector').html(response.template);
                    $('#category-wise-zone').html(response.template_for_zone);
                    $('#variation-table').html(response.template_for_variant);
                    $('#variation-update-table').html(response.template_for_update_variant);
                },
                complete: function () {
                },
            });
        }

        $(document).ready(function () {
            tinymce.init({
                selector: 'textarea.ckeditor'
            });
        });

        $(".lang_link").on('click', function (e) {
            e.preventDefault();
            $(".lang_link").removeClass('active');
            $(".lang-form").addClass('d-none');
            $(".lang-form2").addClass('d-none');
            $(this).addClass('active');

            let form_id = this.id;
            let lang = form_id.substring(0, form_id.length - 5);
            console.log(lang);
            $("#" + lang + "-form").removeClass('d-none');
            $("#" + lang + "-form2").removeClass('d-none');

            if (lang == '{{$default_lang}}') {
                $(".from_part_2").removeClass('d-none');
            } else {
                $(".from_part_2").addClass('d-none');
            }
        });
    </script>
@endpush
