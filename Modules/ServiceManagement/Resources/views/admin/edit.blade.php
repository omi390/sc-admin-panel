@extends('adminmodule::layouts.master')

@section('title',translate('service_update'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('assets/admin-module')}}/plugins/select2/select2.min.css"/>
    <link rel="stylesheet" href="{{asset('assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="{{asset('assets/admin-module')}}/plugins/dataTables/select.dataTables.min.css"/>
    <link rel="stylesheet" href="{{asset('assets/admin-module')}}/plugins/wysiwyg-editor/froala_editor.min.css"/>
    <link rel="stylesheet" href="{{asset('assets/admin-module')}}/css/tags-input.min.css"/>
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
                                            <button type="button" class="btn btn--primary" id="add-provider-variant-btn" data-bs-toggle="modal" data-bs-target="#providerVariantModal">
                                                <span class="material-icons">add</span>
                                                {{translate('add_provider_variant')}}
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
                                                    <th scope="col">{{translate('image')}}</th>
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

                                    <!-- Provider Variant Modal -->
                                    <div class="modal fade" id="providerVariantModal" tabindex="-1" aria-labelledby="providerVariantModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="providerVariantModalLabel">{{translate('add_provider_variant')}}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="alert alert-warning d-none mb-3" id="subcategory-warning">
                                                        <strong>{{translate('notice')}}:</strong> {{translate('please_select_subcategory_first_before_adding_provider_variant')}}
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{translate('zone')}} *</label>
                                                        <select class="form-control" id="provider-variant-zone" required>
                                                            <option value="">{{translate('select_zone')}}</option>
                                                            @foreach($zones as $zone)
                                                                <option value="{{$zone->id}}">{{$zone->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{translate('provider')}} *</label>
                                                        <select class="form-control" id="provider-variant-provider" required disabled>
                                                            <option value="">{{translate('select_provider')}}</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{translate('variant_name')}} *</label>
                                                        <input type="text" class="form-control" id="provider-variant-name" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{translate('price')}} *</label>
                                                        <input type="number" class="form-control" id="provider-variant-price" value="0" step="any" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{translate('image_url')}}</label>
                                                        <input type="url" class="form-control" id="provider-variant-image" placeholder="{{translate('enter_image_url')}}">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{translate('close')}}</button>
                                                    <button type="button" class="btn btn--primary" id="save-provider-variant">{{translate('add')}}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Edit Provider Variant Modal -->
                                    <div class="modal fade" id="editProviderVariantModal" tabindex="-1" aria-labelledby="editProviderVariantModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editProviderVariantModalLabel">{{translate('edit_provider_variant')}}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" id="edit-variant-id">
                                                    <input type="hidden" id="edit-variant-key">
                                                    <input type="hidden" id="edit-service-id">
                                                    <input type="hidden" id="edit-provider-id">
                                                    <input type="hidden" id="edit-zone-id">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">{{translate('variant_name')}} *</label>
                                                        <input type="text" class="form-control" id="edit-variant-name" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{translate('price')}} *</label>
                                                        <input type="number" class="form-control" id="edit-variant-price" value="0" step="any" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{translate('image_url')}}</label>
                                                        <input type="url" class="form-control" id="edit-variant-image" placeholder="{{translate('enter_image_url')}}">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{translate('close')}}</button>
                                                    <button type="button" class="btn btn--primary" id="update-provider-variant">{{translate('update')}}</button>
                                                </div>
                                            </div>
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
    <script src="{{asset('assets/admin-module')}}/js//tags-input.min.js"></script>
    <script src="{{asset('assets/admin-module')}}/plugins/select2/select2.min.js"></script>
    <script src="{{asset('assets/admin-module')}}/plugins/jquery-steps/jquery.steps.min.js"></script>
    <script src="{{asset('assets/admin-module/plugins/tinymce/tinymce.min.js')}}"></script>
    <!-- CKEditor jQuery adapter removed - using TinyMCE instead -->
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

        // Provider Variant Modal JavaScript for Edit Page
        $(document).ready(function() {
            // Store service subcategory ID from server
            var serviceSubCategoryId = '{{$service->sub_category_id}}';
            
            // Function to get subcategory ID
            function getSubCategoryId() {
                // First try to get from form field (in case it was changed)
                var formSubCategoryId = $('#sub-category-id').val();
                if (formSubCategoryId) {
                    return formSubCategoryId;
                }
                // Fallback to service subcategory ID
                return serviceSubCategoryId;
            }
            
            // Function to check and update modal state based on subcategory
            function updateModalState() {
                var subCategoryId = getSubCategoryId();
                var zoneSelect = $('#provider-variant-zone');
                var providerSelect = $('#provider-variant-provider');
                
                if (!subCategoryId) {
                    zoneSelect.prop('disabled', true);
                    providerSelect.prop('disabled', true).html('<option value="">{{translate('please_select_subcategory_first')}}</option>');
                    $('#subcategory-warning').removeClass('d-none');
                } else {
                    zoneSelect.prop('disabled', false);
                    $('#subcategory-warning').addClass('d-none');
                    // Zone is optional now, so we can load providers without zone selection
                }
                
                console.log('Modal state updated. Subcategory ID:', subCategoryId);
            }
            
            // Function to load providers based on subcategory (zone is optional)
            function loadProviders() {
                var zoneId = $('#provider-variant-zone').val();
                var providerSelect = $('#provider-variant-provider');
                var subCategoryId = getSubCategoryId();
                
                console.log('Zone ID:', zoneId);
                console.log('Subcategory ID:', subCategoryId);
                
                if (!subCategoryId) {
                    console.log('No subcategory selected');
                    providerSelect.prop('disabled', true).html('<option value="">{{translate('please_select_subcategory_first')}}</option>');
                    toastr.warning('{{translate('please_select_subcategory_first')}}');
                    return;
                }
                
                providerSelect.prop('disabled', true).html('<option value="">{{translate('loading')}}...</option>');
                console.log('Making AJAX request...');
                
                // Build data object - zone_id is optional now
                var requestData = {
                    sub_category_id: subCategoryId
                };
                if (zoneId) {
                    requestData.zone_id = zoneId;
                }
                
                $.ajax({
                    url: "{{route('admin.service.ajax-get-providers')}}",
                    method: 'GET',
                    data: requestData,
                    success: function(response) {
                        console.log('Response received:', response);
                        console.log('Response flag:', response.flag);
                        console.log('Response data:', response.data);
                        
                        // Handle different response formats
                        var providers = [];
                        if (response.flag === 1 && response.data) {
                            providers = response.data;
                        } else if (response.response_code === 'default' && response.data) {
                            providers = response.data;
                        } else if (Array.isArray(response)) {
                            providers = response;
                        } else if (response.data && Array.isArray(response.data)) {
                            providers = response.data;
                        }
                        
                        console.log('Providers array:', providers);
                        console.log('Providers count:', providers ? providers.length : 0);
                        
                        if (providers && providers.length > 0) {
                            providerSelect.html('<option value="">{{translate('select_provider')}}</option>');
                            $.each(providers, function(index, provider) {
                                console.log('Adding provider:', provider);
                                providerSelect.append('<option value="' + provider.id + '">' + provider.company_name + '</option>');
                            });
                            providerSelect.prop('disabled', false);
                            console.log('Providers loaded successfully:', providers.length);
                        } else {
                            console.warn('No providers found. Response:', response);
                            providerSelect.html('<option value="">{{translate('no_providers_found')}}</option>');
                            providerSelect.prop('disabled', true);
                            toastr.info('{{translate('no_providers_found_for_this_zone')}}');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading providers:', error);
                        console.error('XHR:', xhr);
                        console.error('Status:', status);
                        providerSelect.html('<option value="">{{translate('error_loading_providers')}}</option>');
                        providerSelect.prop('disabled', true);
                        toastr.error('{{translate('error_loading_providers')}}');
                    }
                });
            }
            
            // When modal is shown, check if subcategory is selected and attach event handlers
            $('#providerVariantModal').on('shown.bs.modal', function() {
                console.log('Modal shown');
                updateModalState();
                // Load providers when modal opens if subcategory is available
                var subCategoryId = getSubCategoryId();
                if (subCategoryId) {
                    loadProviders();
                }
                // Attach event handler after modal is shown
                $('#provider-variant-zone').off('change').on('change', function() {
                    console.log('Zone change event fired');
                    loadProviders();
                });
                console.log('Event handler attached to zone select');
            });
            
            // Monitor subcategory changes
            $(document).on('change', '#sub-category-id', function() {
                updateModalState();
                // Load providers when subcategory changes
                loadProviders();
            });
            
            // When zone is selected, fetch providers (also attach on document ready as fallback)
            $(document).on('change', '#provider-variant-zone', function() {
                console.log('Zone change via delegation');
                loadProviders();
            });

            // Save provider variant
            $('#save-provider-variant').on('click', function() {
                var zoneId = $('#provider-variant-zone').val();
                var providerId = $('#provider-variant-provider').val();
                var variantName = $('#provider-variant-name').val();
                var variantPrice = $('#provider-variant-price').val();
                var variantImage = $('#provider-variant-image').val();
                var subCategoryId = getSubCategoryId();

                if (!subCategoryId) {
                    toastr.warning('{{translate('please_select_subcategory_first')}}');
                    return;
                }

                if (!zoneId) {
                    toastr.warning('{{translate('please_select_zone')}}');
                    return;
                }

                if (!providerId) {
                    toastr.warning('{{translate('please_select_provider')}}');
                    return;
                }

                if (!variantName || variantPrice === '') {
                    toastr.warning('{{translate('variant_name_and_price_are_required')}}');
                    return;
                }

                let route = "{{route('admin.service.ajax-add-variant')}}";
                let id = "variation-table";
                
                $.get({
                    url: route,
                    dataType: 'json',
                    data: {
                        name: variantName,
                        price: variantPrice,
                        provider_id: providerId,
                        zone_id: zoneId,
                        variation_image: variantImage
                    },
                    success: function (response) {
                        if (response.flag == 0) {
                            toastr.info(response.message || '{{translate('already_exist')}}');
                        } else {
                            $('#variation-table').html(response.template);
                            $('#new-variations-table').show();
                            $('#providerVariantModal').modal('hide');
                            $('#provider-variant-zone').val('').trigger('change');
                            $('#provider-variant-name').val('');
                            $('#provider-variant-price').val(0);
                            $('#provider-variant-image').val('');
                            toastr.success('{{translate('variant_added_successfully')}}');
                        }
                    },
                    error: function() {
                        toastr.error('{{translate('error_adding_variant')}}');
                    }
                });
            });

            // Edit Provider Variant functionality
            $(document).on('click', '.edit-provider-variant-btn', function() {
                var variantId = $(this).data('variant-id') || '';
                var variantName = $(this).data('variant-name') || '';
                var variantPrice = $(this).data('variant-price') || 0;
                var variantImage = $(this).data('variant-image') || '';
                var variantKey = $(this).data('variant-key') || '';
                var serviceId = $(this).data('service-id') || '{{$service->id}}';
                var providerId = $(this).data('provider-id') || '';
                var zoneId = $(this).data('zone-id') || '';

                // Populate edit modal
                $('#edit-variant-id').val(variantId);
                $('#edit-variant-key').val(variantKey);
                $('#edit-service-id').val(serviceId);
                $('#edit-provider-id').val(providerId);
                $('#edit-zone-id').val(zoneId);
                $('#edit-variant-name').val(variantName);
                $('#edit-variant-price').val(variantPrice);
                $('#edit-variant-image').val(variantImage);
            });

            // Update provider variant
            $('#update-provider-variant').on('click', function() {
                var variantId = $('#edit-variant-id').val();
                var variantKey = $('#edit-variant-key').val();
                var serviceId = $('#edit-service-id').val();
                var providerId = $('#edit-provider-id').val();
                var zoneId = $('#edit-zone-id').val();
                var variantName = $('#edit-variant-name').val();
                var variantPrice = $('#edit-variant-price').val();
                var variantImage = $('#edit-variant-image').val();

                if (!variantName || variantPrice === '') {
                    toastr.warning('{{translate('variant_name_and_price_are_required')}}');
                    return;
                }

                $.ajax({
                    url: "{{route('admin.service.ajax-update-provider-variant')}}",
                    method: 'POST',
                    data: {
                        variant_id: variantId,
                        variant_key: variantKey,
                        variant_name: variantName,
                        price: variantPrice,
                        variation_image: variantImage,
                        provider_id: providerId,
                        zone_id: zoneId,
                        service_id: serviceId
                    },
                    success: function(response) {
                        if (response.flag == 1) {
                            $('#variation-update-table').html(response.template);
                            $('#editProviderVariantModal').modal('hide');
                            toastr.success('{{translate('variant_updated_successfully')}}');
                        } else {
                            toastr.error(response.message || '{{translate('error_updating_variant')}}');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error updating variant:', error);
                        toastr.error('{{translate('error_updating_variant')}}');
                    }
                });
            });
        });
    </script>
@endpush
