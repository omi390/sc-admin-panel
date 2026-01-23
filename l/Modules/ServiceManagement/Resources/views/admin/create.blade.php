@extends('adminmodule::layouts.master')

@section('title',translate('service_setup'))

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
                        <h2 class="page-title">{{translate('add_new_service')}}</h2>
                    </div>

                    <div class="card">
                        <div class="card-body p-30">
                            <div>
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
                                <form action="{{route('admin.service.store')}}" method="post"           enctype="multipart/form-data"
                                    id="form-wizard">
                                    @csrf

                                    <h3>{{translate('service_information')}}</h3>
                                    <section>
                                        <div class="row">
                                            <div class="col-lg-5 mb-5 mb-lg-0">
                                                @if($language)
                                                    <div class="mb-30">
                                                        <div class="form-floating form-floating__icon lang-form" id="default-form">
                                                            <input type="text" name="name[]"
                                                                class="form-control default-name" required
                                                                placeholder="{{translate('service_name')}}">
                                                            <label>{{translate('service_name')}} ({{ translate('default') }}
                                                                )</label>
                                                            <span class="material-icons">subtitles</span>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="lang[]" value="default">
                                                    @foreach ($language?->live_values as $lang)
                                                        <div class="mb-30">
                                                            <div class="form-floating form-floating__icon d-none lang-form"
                                                                id="{{$lang['code']}}-form">
                                                                <input type="text" name="name[]"
                                                                    class="form-control input-language"
                                                                    placeholder="{{translate('service_name')}}">
                                                                <label>{{translate('service_name')}}
                                                                    ({{strtoupper($lang['code'])}})</label>
                                                                <span class="material-icons">subtitles</span>
                                                            </div>
                                                        </div>
                                                        <input type="hidden" name="lang[]" value="{{$lang['code']}}">
                                                    @endforeach
                                                @else
                                                    <div class="lang-form">
                                                        <div class="mb-30">
                                                            <div class="form-floating form-floating__icon">
                                                                <input type="text" class="form-control" name="name[]"
                                                                       placeholder="{{translate('service_name')}} *"
                                                                       required>
                                                                <label>{{translate('service_name')}} *</label>
                                                                <span class="material-icons">subtitles</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="lang[]" value="default">
                                                @endif
                                                <div class="mb-30">
                                                    <div class="form-error-wrap">
                                                        <select class="js-select theme-input-style w-100 form-error-wrap" name="category_id" id="category-id">
                                                            <option value="0" selected
                                                                    disabled>{{translate('choose_Category')}} *
                                                            </option>
                                                            @foreach($categories as $category)
                                                                <option
                                                                        value="{{$category->id}}">{{$category->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="mb-30 form-error-wrap" id="sub-category-selector">
                                                    <div class="form-error-wrap">
                                                        <select class="subcategory-select theme-input-style w-100"
                                                                name="sub_category_id" id="sub-category-id">
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="mb-30">
                                                    <div class="form-floating form-floating__icon">
                                                        <input type="number" class="form-control" name="tax" min="0"
                                                               max="100" step="any"
                                                               placeholder="{{translate('add_tax_percentage')}} *"
                                                               required="" value="{{old('tax')}}">
                                                        <label>{{translate('add_tax_percentage')}} *</label>
                                                        <span class="material-icons">percent</span>
                                                    </div>
                                                </div>

                                                <div class="mb-30">
                                                    <div class="form-floating form-floating__icon">
                                                        <input type="number" class="form-control"
                                                               name="min_bidding_price" min="0" step="any"
                                                               placeholder="{{translate('Minimum bidding price')}} *"
                                                               required="" value="{{old('min_bidding_price')}}">
                                                        <label>{{translate('Minimum bidding price')}} *</label>
                                                        <span class="material-icons">price_change</span>
                                                    </div>
                                                </div>

                                                <div class="mb-30">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control w-100" name="tags"
                                                               placeholder="{{translate('Enter_tags')}}"
                                                               data-role="tagsinput">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 col-sm-5 mb-5 mb-sm-0">
                                                <div class="d-flex flex-column align-items-center gap-3">
                                                    <p class="mb-0">{{translate('thumbnail_image')}} *</p>
                                                    <div class="d-flex flex-column align-items-center">
                                                        <div class="upload-file form-error-wrap">
                                                            <input type="file" class="upload-file__input"
                                                                   name="thumbnail" accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*">
                                                            <div class="upload-file__img">
                                                                <img src="{{asset('public/assets/admin-module/img/media/upload-file.png')}}"
                                                                        alt="{{ translate('service') }}">
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
                                                    <p class="mb-0">{{translate('cover_image')}} *</p>
                                                    <div>
                                                        <div class="upload-file form-error-wrap">
                                                            <input type="file" class="upload-file__input"
                                                                   name="cover_image" accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*">
                                                            <div class="upload-file__img upload-file__img_banner">
                                                                <img src="{{asset('public/assets/admin-module/img/media/banner-upload-file.png')}}"
                                                                     alt="{{ translate('service-cover-image') }}">
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
                                                                <textarea type="text" class="form-control" name="short_description[]" required></textarea>
                                                                <label>{{translate('short_description')}}
                                                                    ({{translate('default')}}) *</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 mt-4 mt-md-5">
                                                        <div class="form-error-wrap">
                                                            <label for="editor"
                                                                class="mb-2">{{translate('long_Description')}}
                                                                ({{translate('default')}})
                                                                <span class="text-danger">*</span></label>
                                                            <section id="editor" class="dark-support">
                                                            <textarea class="ckeditor" name="description[]" required></textarea>
                                                            </section>
                                                        </div>
                                                    </div>
                                                </div>
                                                @foreach ($language?->live_values as $lang)
                                                    <div class="d-none lang-form2" id="{{$lang['code']}}-form2">
                                                        <div class="col-lg-12 mt-5">
                                                            <div class="mb-30">
                                                                <div class="form-floating">
                                                                    <textarea type="text" class="form-control" name="short_description[]"></textarea>
                                                                    <label>{{translate('short_description')}}
                                                                        ({{strtoupper($lang['code'])}}) *</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 mt-4 mt-md-5">
                                                            <div class="form-error-wrap">
                                                                <label for="editor" class="mb-2">{{translate('long_Description')}}({{strtoupper($lang['code'])}})
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <section id="editor" class="dark-support">
                                                                <textarea class="ckeditor" name="description[]"></textarea>
                                                                </section>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="normal-form">
                                                    <div class="col-lg-12 mt-5">
                                                        <div class="mb-30">
                                                            <div class="form-floating">
                                                                <textarea type="text" class="form-control" name="short_description[]" required></textarea>
                                                                <label>{{translate('short_description')}} *</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 mt-4 mt-md-5">
                                                        <div class="form-error-wrap">
                                                            <label for="editor" class="mb-2">{{translate('long_Description')}}
                                                                <span class="text-danger">*</span>
                                                            </label>
                                                            <section id="editor" class="dark-support">
                                                                <textarea class="ckeditor" name="description[]" required></textarea>
                                                            </section>
                                                        </div>
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
                                                       placeholder="{{translate('add_variant')}} *">
                                                <label>{{translate('add_variant')}} *</label>
                                            </div>
                                            <div class="form-floating flex-grow-1">
                                                <input type="number" class="form-control" name="variant_price"
                                                       id="variant-price"
                                                       placeholder="{{translate('price')}} *" value="0">
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
                                                    @include('servicemanagement::admin.partials._category-wise-zone',['zones'=>session()->has('category_wise_zones')?session('category_wise_zones'):[]])
                                                </thead>
                                                <tbody id="variation-table">
                                                    @include('servicemanagement::admin.partials._variant-data',['zones'=>session()->has('category_wise_zones')?session('category_wise_zones'):[]])
                                                </tbody>
                                            </table>
                                        </div>
                                    </section>
                                </form>
                            </div>
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
    <script src="{{asset('public/assets/provider-module')}}/plugins/jquery-validation/jquery.validate.min.js"></script>
    <script src="{{asset('public/assets/admin-module/plugins/tinymce/tinymce.min.js')}}"></script>
    <script src="{{asset('public/assets/ckeditor/jquery.js')}}"></script>

    <script>
        (function ($) {
            "use strict";

            let formWizard = $("#form-wizard");

            $('body').on('click', function (event) {
                if (!$(event.target).closest('#editor').length) {
                    if($("#editor iframe").contents().find("body").text() !== ""){
                        formWizard.find('.desc-err').remove();
                    };
                }

                if (!$(event.target).closest('[name=category_id], [name=category_id] + .select2').length) {
                    if($('[name=category_id]').val()) {
                        $('[name=category_id]').parents('.form-error-wrap').siblings('[for="category-id"]').remove();
                    }
                }
            });



            // Form validation with jQuery
            formWizard.validate({
                errorPlacement: function (error, element) {
                    element.parents('.form-floating, .form-error-wrap').after(error);
                },
                rules: {
                    "name[]": "required",
                    category_id: "required",
                    tax: "required",
                    min_bidding_price: "required",
                    "short_description[]": "required",
                    thumbnail: "required",
                    cover_image: "required",
                    "description[]": "required",
                },
                messages: {
                    "name[]": "Please enter name",
                    category_id: "Please enter category id",
                    tax: "Please enter Tax",
                    min_bidding_price: "Please enter min bidding price",
                    "short_description[]": "Please enter short description",
                    thumbnail: "Please enter thumbnail",
                    cover_image: "Please upload cover image",
                    "description[]": "Please enter description",
                },
            });

            formWizard.steps({
                headerTag: "h3",
                bodyTag: "section",
                transitionEffect: "fade",
                stepsOrientation: "vertical",
                autoFocus: true,
                labels: {
                    finish: "Submit",
                    next: "Next",
                    previous: "Previous"
                },
                onStepChanging: function (event, currentIndex, newIndex) {
                    if (newIndex < currentIndex) {
                        return true;
                    }

                    if (currentIndex == 0) {
                        var errorMessageElement = formWizard.find(".desc-err");

                        if($("#editor iframe").contents().find("body").text() == ""){
                            if (errorMessageElement.length > 0) {
                                errorMessageElement.text("Please Add Description");
                            } else {
                                formWizard.find("#editor").after(`<span class="text-danger desc-err mt-2">Please Add Description</span>`)
                                return false;
                            }
                        } else {
                            formWizard.find('.desc-err').remove();
                        };
                    }

                    formWizard.validate().settings.ignore = ":disabled,:hidden";
                    return formWizard.valid();
                },
                onFinished: function (event, currentIndex) {
                    event.preventDefault();
                    let hasRows = $("#variation-table > tr").length > 0;
                    if (hasRows) {
                        formWizard.submit();
                    } else {
                        var errorMessageElement = formWizard.find(".table-row-err");

                        if (errorMessageElement.length > 0) {
                            errorMessageElement.text("Please Add Variation");
                        } else {
                            formWizard.find("#variation-table").parents(".table-responsive").after(`<span class="text-danger table-row-err">Please Add Variation</span>`);
                        }
                    }
                }
            });

        })(jQuery);
    </script>


    {{-- <script>
        (function ($) {
            "use strict";

            let formWizard = $("#form-wizard");

            // Form validation with jQuery
            formWizard.validate({
                errorPlacement: function (error, element) {
                    element.parents('.form-floating, .form-error-wrap').after(error);
                },
            });

            function setValidationRulesAndMessages(rules, messages) {
                formWizard.validate().settings.rules = rules;
                formWizard.validate().settings.messages = messages;
            }

            function handleTableRowValidation() {
                let hasRows = $("#variation-table > tr").length > 0;
                if (!hasRows) {
                    var errorMessageElement = formWizard.find(".table-row-err");

                    if (errorMessageElement.length > 0) {
                        errorMessageElement.text("Please Add Variation");
                    } else {
                        formWizard.find("#variation-table").parents(".table-responsive").after(`<span class="text-danger table-row-err">Please Add Variation</span>`);
                    }
                    return false;
                } else {
                    formWizard.find(".table-row-err").remove();
                }
                return true;
            }

            formWizard.steps({
                headerTag: "h3",
                bodyTag: "section",
                transitionEffect: "fade",
                stepsOrientation: "vertical",
                autoFocus: true,
                labels: {
                    finish: "Submit",
                    next: "Next",
                    previous: "Previous"
                },
                onStepChanging: function (event, currentIndex, newIndex) {
                    if (newIndex < currentIndex) {
                        return true;
                    }

                    switch (currentIndex) {
                        case 0:
                            setValidationRulesAndMessages({
                                "name[]": "required",
                                category_id: "required",
                                tax: "required",
                                min_bidding_price: "required",
                                "short_description[]": "required",
                                thumbnail: "required",
                                cover_image: "required",
                            }, {
                                "name[]": "Please enter name",
                                category_id: "Please enter category id",
                                tax: "Please enter Tax",
                                min_bidding_price: "Please enter min bidding price",
                                "short_description[]": "Please enter short description",
                                thumbnail: "Please enter thumbnail",
                                cover_image: "Please upload cover image",
                            });
                            break;
                        case 1:
                            if (!handleTableRowValidation()) {
                                return false;
                            break;
                    }

                    formWizard.validate().settings.ignore = ":disabled,:hidden";
                    return formWizard.valid();
                },
                onFinished: function (event, currentIndex) {
                    event.preventDefault();
                    let hasRows = $("#variation-table > tr").length > 0;
                    if(hasRows) {
                        formWizard.submit();
                    } else {
                        console.log("Please Add Variation");
                    }
                }
            });

        })(jQuery);
    </script> --}}

    <script>
        "use strict";

        $(document).ready(function () {
            $('.js-select').select2();
            $('.subcategory-select').select2({
                placeholder: "Choose Subcategory"
            });
        });

        var variationCount = $("#variation-table > tbody > tr").length;

        // $("#form-wizard").steps({
        //     headerTag: "h3",
        //     bodyTag: "section",
        //     transitionEffect: "slideLeft",
        //     autoFocus: true,
        //     onFinished: function (event, currentIndex) {
        //         //validation
        //         let category = $("#category-id").val();
        //         if (category === '0') {
        //             toastr.error("{{translate('Select_valid_category')}} *");
        //         }

        //         if (variationCount > 0) {
        //             $("#service-add-form")[0].submit();
        //         } else {
        //             $('#service-add-form').trigger('focus')
        //             toastr.error("{{translate('Must_add_a_service_variation')}}");
        //         }
        //     }
        // });

        $("#service-ajax-variation").on('click', function (){
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
                        /*$('#loading').show();*/
                    },
                    success: function (response) {
                        if (response.flag == 0) {
                            toastr.info('Already added');
                        } else {
                            $('#new-variations-table').show();
                            $('#' + id).html(response.template);
                            $('#variant-name').val("");
                            $('#variant-price').val(0);
                        }
                        variationCount++;
                    },
                    complete: function () {
                        /*$('#loading').hide();*/
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
                            $('#' + id).html(response.template);
                        },
                        complete: function () {
                        },
                    });
                }
            })
        }

        $("#category-id").change(function (){
            let id = this.value;
            let route = "{{ url('/admin/category/ajax-childes/') }}/" + id;
            ajax_switch_category(route)
        });

        function ajax_switch_category(route) {
            $.get({
                url: route,
                dataType: 'json',
                data: {},
                beforeSend: function () {
                },
                success: function (response) {
                    $('#sub-category-selector').html(response.template);
                    $('#category-wise-zone').html(response.template_for_zone);
                    $('#variation-table').html(response.template_for_variant);
                },
                complete: function () {
                },
            });
        }

        $(".lang_link").on('click', function (e) {
            e.preventDefault();
            $(".lang_link").removeClass('active');
            $(".lang-form").addClass('d-none');
            $(".lang-form2").addClass('d-none');
            $(this).addClass('active');

            let form_id = this.id;
            let lang = form_id.substring(0, form_id.length - 5);
            $("#" + lang + "-form").removeClass('d-none');
            $("#" + lang + "-form2").removeClass('d-none');

        });

        $(document).ready(function () {
            tinymce.init({
                selector: 'textarea.ckeditor'
            });
        });

    </script>
@endpush
