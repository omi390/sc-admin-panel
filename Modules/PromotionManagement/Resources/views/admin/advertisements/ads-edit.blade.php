@extends('adminmodule::layouts.master')

@section('title',translate('Ads Edit'))

@push('css_or_js')
    <link rel="stylesheet" type="text/css" href="{{asset('/public/assets/admin-module/css/daterangepicker.css')}}"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-4">
                        <h2 class="page-title mb-2">{{translate('Edit Advertisement')}}</h2>
                        <p>{{ translate('Boost Your Visibility with Targeted Ads') }}</p>
                    </div>

                    <div class="card mb-30">
                        <div class="card-body p-30">
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
                            <form action="{{ route('admin.advertisements.update', [$advertisement->id]) }}"
                                  method="POST" enctype="multipart/form-data" id="create-add-form">
                                @csrf
                                @method('put')
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-floating form-floating__icon mb-30">
                                            <select class="js-select theme-input-style w-100 provider-info"
                                                    name="provider_id"
                                                    disabled>
                                                <option value="">{{translate('Select Provider')}}</option>
                                                @foreach($providers as $provider)
                                                    <option
                                                        value="{{$provider['id']}}" {{ $advertisement['provider_id'] == $provider['id'] ? 'selected' : '' }}>{{$provider['company_name']}}
                                                        ({{$provider['company_phone']}})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-floating form-floating__icon mb-30">
                                            <select class="theme-input-style w-100" name="priority">
                                                <option selected disabled>Priority</option>
                                                @for($i=1; $i<=$maxPriority; $i++)
                                                    <option
                                                        value="{{$i}}" {{ $advertisement['priority'] == $i ? 'selected' : '' }}>{{$i}}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="form-floating form-floating__icon mb-30">
                                            <select class="js-select theme-input-style w-100 promotion_type"
                                                    name="type">
                                                <option
                                                    value="video_promotion" {{ $advertisement['type'] == 'video_promotion' ? 'selected' : ''}}>{{translate('Video Promotion')}}</option>
                                                <option
                                                    value="profile_promotion" {{ $advertisement['type'] == 'profile_promotion' ? 'selected' : ''}}>{{translate('Profile Promotion')}}</option>
                                            </select>
                                        </div>
                                        <div class="mb-30 position-relative">
                                            <?php
                                            $startDate = new DateTime($advertisement['start_date']);
                                            $endDate = new DateTime($advertisement['end_date']);

                                            $formattedStartDate = $startDate->format('m/d/Y');
                                            $formattedEndDate = $endDate->format('m/d/Y');
                                            ?>
                                            <span class="material-symbols-outlined icon-absolute-on-right">calendar_month</span>
                                            <input type="text"
                                                   class="form-control h-45 position-relative bg-transparent"
                                                   name="dates"
                                                   placeholder="Select Validation Date"
                                                   value="<?php echo $formattedStartDate . ' - ' . $formattedEndDate ?>">

                                        </div>
                                        @if($language)
                                            <div class="lang-form" id="default-form">
                                                <div class="form-floating form-floating__icon mb-30">
                                                    <input type="text" class="form-control" id="title-default" name="title[]" maxlength="255"
                                                           placeholder="{{ translate('Title') }}" required="" value="{{$advertisement?->getRawOriginal('title')}}"
                                                           data-preview-text="preview-title-default"/>
                                                    <label for="title">{{translate('title')}} ({{ translate('default') }})</label>
                                                    <span class="material-icons">title</span>
                                                </div>
                                                <div class="form-floating mb-30">
                                            <textarea class="form-control resize-none" id="description-default" required
                                                      placeholder="{{translate('description')}}"
                                                      name="description[]" maxlength="100"
                                                      data-preview-text="preview-description-default">{{$advertisement?->getRawOriginal('description')}}</textarea>
                                                    <label for="description">{{translate('description')}} ({{ translate('default') }})</label>
                                                </div>
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                            @foreach ($language?->live_values as $lang)
                                                    <?php
                                                    if (count($advertisement['translations'])) {
                                                        $translate = [];
                                                        foreach ($advertisement['translations'] as $t) {
                                                            if ($t->locale == $lang['code'] && $t->key == "title") {
                                                                $translate[$lang['code']]['title'] = $t->value;
                                                            }

                                                            if ($t->locale == $lang['code'] && $t->key == "description") {
                                                                $translate[$lang['code']]['description'] = $t->value;
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                <div class="lang-form d-none" id="{{$lang['code']}}-form">
                                                    <div class="form-floating form-floating__icon mb-30">
                                                        <input type="text" class="form-control"  id="title-{{$lang['code']}}"
                                                               name="title[]" value="{{$translate[$lang['code']]['title']??''}}" maxlength="255"
                                                               placeholder="{{ translate('Title') }}"
                                                               data-preview-text="preview-title-{{$lang['code']}}"/>
                                                        <label for="title">{{translate('title')}}
                                                            ({{strtoupper($lang['code'])}})</label>
                                                        <span class="material-icons">title</span>
                                                    </div>
                                                    <div class="form-floating mb-30">
                                                    <textarea class="form-control resize-none" id="description-{{$lang['code']}}"
                                                              placeholder="{{translate('description')}}"
                                                              name="description[]" maxlength="100"
                                                              data-preview-text="preview-description-{{$lang['code']}}">{{$translate[$lang['code']]['description']??''}}</textarea>
                                                        <label for="description">{{translate('description')}}
                                                            ({{strtoupper($lang['code'])}})</label>
                                                    </div>
                                                </div>

                                                <input type="hidden" name="lang[]" value="{{$lang['code']}}">
                                            @endforeach
                                        @else
                                            <div class="lang-form" id="default-form">
                                                <div class="form-floating form-floating__icon mb-30">
                                                    <input type="text" class="form-control" id="title" name="title[]" maxlength="255"
                                                           placeholder="{{ translate('Title') }}" required="" value="{{$advertisement->title}}"
                                                           data-preview-text="preview-title-default"/>
                                                    <label for="title">{{translate('title')}}</label>
                                                    <span class="material-icons">title</span>
                                                </div>
                                                <div class="form-floating mb-30">
                                            <textarea class="form-control resize-none" id="description" required
                                                      placeholder="{{translate('description')}}"
                                                      name="description[]" maxlength="100"
                                                      data-preview-text="preview-description-default">{{$advertisement->description}}</textarea>
                                                    <label for="description">{{translate('description')}}</label>
                                                </div>
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                        @endif
                                        <div class="promotion-typewise-upload-box" id="video-upload-box">
                                            <div class="border rounded p-3">
                                                <div class="d-flex flex-column align-items-center gap-3">
                                                    <p class="title-color mb-0 text-uppercase">{{translate('Upload Your Video')}}
                                                        (16:9)</p>

                                                    <div class="upload-file">
                                                        <input type="file" class="video_attachment"
                                                               name="video_attachment"
                                                               accept="video/mp4, video/webm, video/mkv">
                                                        <div
                                                            class="upload-file__img upload-file__img_banner upload-file__video-not-playable h-140">
                                                            <img src="{{asset('public/assets/admin-module/img/media/banner-upload-file.png')}}" alt="">
                                                        </div>
                                                        <button class="remove-file-button" type="button">
                                                            <span class="material-symbols-outlined">close</span>
                                                        </button>
                                                    </div>

                                                    <p class="opacity-75 max-w220 mx-auto text-center fs-12">
                                                        {{translate('Maximum 50 MB')}}
                                                        <br>
                                                        {{translate('Supports: MP4, WEBM, MKV')}}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="promotion-typewise-upload-box" id="profile-upload-box">
                                            <br>
                                            <h5 class="mb-3">{{ translate('Show Review & Ratings') }}</h5>
                                            <div class="card bg--secondary shadow-none">
                                                <div class="card-body p-3">
                                                    <div class="w-100 d-flex flex-wrap gap-3">
                                                        <label class="form-check form--check me-3">
                                                            <input type="checkbox" class="form-check-input"
                                                                   name="review" {{ $advertisement?->review?->value == 1 ? 'checked' : '' }}>
                                                            <span
                                                                class="form-check-label">{{ translate('Review') }}</span>
                                                        </label>
                                                        <label class="form-check form--check">
                                                            <input type="checkbox" class="form-check-input"
                                                                   name="rating" {{ $advertisement?->rating?->value == 1 ? 'checked' : '' }}>
                                                            <span
                                                                class="form-check-label">{{ translate('Rating') }}</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <br>
                                            <div class="border rounded p-3">
                                                <div class="d-flex flex-column align-items-center gap-3">
                                                    <p class="title-color mb-0 text-uppercase">{{translate('Upload your profile Image')}}
                                                        (1:1)</p>

                                                    <div class="upload-file max-w-130px">
                                                        <input type="file" class="cover_attachment js-upload-input"
                                                               data-target="profile-prev-image" name="profile_image"
                                                               accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*">
                                                        <div class="upload-file__img">
                                                            <img src="{{$advertisement?->provider_profile_image_full_path}}"
                                                                alt="" onerror='this.src="{{asset('public/assets/admin-module/img/media/upload-file.png')}}"'>
                                                        </div>
                                                        <button class="remove-file-button" type="button">
                                                            <span class="material-symbols-outlined">close</span>
                                                        </button>
                                                    </div>

                                                    <p class="opacity-75 max-w220 mx-auto text-center fs-12">
                                                        {{translate('Supports: PNG, JPG, JPEG, GIF')}}
                                                        <br>
                                                        {{translate('Maximum 10 MB')}}
                                                    </p>
                                                </div>
                                            </div>
                                            <br>
                                            <div class="border rounded p-3">
                                                <div class="d-flex flex-column align-items-center gap-3">
                                                    <p class="title-color mb-0 text-uppercase">{{translate('Upload your Cover Image')}}
                                                        (2:1)</p>

                                                    <div class="upload-file">
                                                        <input type="file" class="cover_attachment js-upload-input"
                                                               data-target="main-image" name="cover_image"
                                                               accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*">
                                                        <div class="upload-file__img upload-file__img_banner">
                                                            <img src="{{$advertisement?->provider_cover_image_full_path}}"
                                                                alt="" onerror='this.src="{{asset('public/assets/admin-module/img/media/banner-upload-file.png')}}"'>
                                                        </div>
                                                        <button class="remove-file-button" type="button">
                                                            <span class="material-symbols-outlined">close</span>
                                                        </button>
                                                    </div>

                                                    <p class="opacity-75 max-w220 mx-auto text-center fs-12">
                                                        {{translate('Supports: PNG, JPG, JPEG, GIF')}}
                                                        <br>
                                                        {{translate('Maximum 10 MB')}}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="position-sticky top-80px text-8797AB">
                                            <div class="bg-light p-3 p-sm-4 rounded">
                                                <p class="title-color">{{translate('Advertisement Preview')}}</p>
                                                <div id="video-preview-box" class="video-preview-box">
                                                    <div class="bg--secondary rounded">
                                                        <div class="video h-200">
                                                            <video style="display:none" controls>
                                                                {{ translate('Your browser does not support the video tag.') }}
                                                            </video>
                                                        </div>
                                                        <div class="prev-video-box rounded white-color-bg px-3 py-4 position-relative gap-4 mt-n2">
                                                            <div class="profile-img">
                                                            </div>
                                                            <div class="d-flex align-items-center justify-content-between gap-2">
                                                                <div class="d-flex flex-column gap-2 flex-grow-1">
                                                                    <div class="preview-title w-100">
                                                                        <h5 class="main-text pe-4"></h5>
                                                                        <div class="placeholder-text bg--secondary p-2 w-50"></div>
                                                                    </div>
                                                                    <div class="preview-description w-100">
                                                                        <div class="main-text line-limit-2"></div>
                                                                        <div class="placeholder-text bg--secondary p-2 w-75"></div>
                                                                    </div>
                                                                    <div class="preview-description w-100">
                                                                        <div class="placeholder-text bg--secondary p-2 w-65"></div>
                                                                    </div>
                                                                </div>
                                                                <a class="btn btn--primary py-2 px-3 cursor-auto">
                                                                    <span class="material-symbols-outlined m-0">arrow_forward</span>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="profile-preview-box" class="profile-preview-box">
                                                    <div class="bg--secondary rounded">
                                                        <div class="main-image rounded min-h-200"
                                                             style="background: url('{{ $advertisement?->provider_cover_image_full_path}}') center center / cover no-repeat;">
                                                        </div>
                                                        <div class="rounded white-color-bg px-3 py-4 position-relative mt-n2">
                                                            <div class="preview-title preview-description">
                                                                <div class="wishlist-btn bg--secondary placeholder-text"></div>
                                                                <div class="static-text wishlist-btn-2">
                                                                    <div class="h-100 w-100 d-flex align-items-center justify-content-center">
                                                                        <span class="material-symbols-outlined">favorite</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex align-items-center justify-content-between gap-2">
                                                                <div class="profile-prev-image bg--secondary me-xl-3"
                                                                     style="background: url('{{$advertisement?->provider_cover_image_full_path}}') center center / cover no-repeat;">

                                                                </div>
                                                                <div class="w-0 d-flex flex-column gap-2 flex-grow-1">
                                                                    <div class="d-flex justify-content-between">
                                                                        <div class="preview-title w-100">
                                                                            <h5 class="main-text pe-4"></h5>
                                                                            <div
                                                                                class="placeholder-text bg--secondary p-2 w-50"></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="preview-description w-100">
                                                                        <div class="main-text line-limit-2"></div>
                                                                        <div
                                                                            class="placeholder-text bg--secondary p-2 w-75"></div>
                                                                    </div>
                                                                    <div
                                                                        class="d-flex flex-wrap gap-3 align-items-center">
                                                                        <div
                                                                            class="rating-placeholder bg--secondary p-2 w-25"></div>
                                                                        <div class="rating-text static-text">
                                                                            <div class="d-flex c1">
                                                                                <div class="rating-number">

                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <span class="opacity-25">|</span>
                                                                        <span
                                                                            class="review--text static-text"> {{ translate('Reviews') }}</span>
                                                                        <div
                                                                            class="review-placeholder bg--secondary p-2 w-25"></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="d-flex justify-content-end gap-20 mt-30">
                                                <button class="btn btn--secondary"
                                                        type="reset">{{translate('reset')}}</button>
                                                <button class="btn btn--primary demo_check"
                                                        type="submit">{{translate('submit')}}</button>
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

    <script type="text/javascript" src="{{asset('/public/assets/admin-module/js/moment.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('/public/assets/admin-module/js/daterangepicker.min.js')}}"></script>

    <script>
        $(function() {
            var formattedStartDate = '<?php echo $formattedStartDate; ?>';
            var formattedEndDate = '<?php echo $formattedEndDate; ?>';

            $('input[name="dates"]').daterangepicker({
                startDate: formattedStartDate,
                endDate: formattedEndDate,
            });
        });
    </script>


    <!-- Video Upload Handlr -->
    <script>
        $(".video_attachment").on("change", function (event) {
            const videoEl = $(".video > video")
            const prevVideoBox = $('.prev-video-box')
            let file = event.target.files[0];
            let blobURL = URL.createObjectURL(file);
            const prevImage = $(this).closest('.upload-file').find('.upload-file__img').find('img').attr('src');
            videoEl.css('display', 'block');
            videoEl.attr('src', blobURL);
            videoEl.siblings('.play-icon').hide();
            $(this).closest('.upload-file').find('.upload-file__img').html('<video src="' + blobURL + '" controls></video>');
            $(this).closest('.upload-file').find('.remove-file-button').show()
            $(this).closest('.upload-file').find('.remove-file-button').on('click', function () {
                $(this).hide()
                videoEl.siblings('.play-icon').show();
                $(this).closest('.upload-file').find('.upload-file__img').find('img').attr('src', prevImage);
                $(this).closest('.upload-file').find('.video_attachment').val('');
                $(this).closest('.upload-file').find('.video > video').css('display', 'none');
                videoEl.css('display', 'none');
                videoEl.attr('src', '');
            })
        })

        $(window).on('load', function () {
            handleUploadBox();

            const videoEl = $(".video > video")
            let blobURL = "{{$advertisement?->promotional_video_full_path}}";
            videoEl.css('display', 'block');
            videoEl.attr('src', blobURL);
            $(".video_attachment").closest('.upload-file').find('.upload-file__img').html('<video src="' + blobURL + '" controls></video>');
            $(".video_attachment").closest('.upload-file').find('.remove-file-button').show()
            $(".video_attachment").closest('.upload-file').find('.remove-file-button').on('click', function () {
                $(this).hide()
                $(this).closest('.upload-file').find('.upload-file__img').html('<img src="{{asset('public/assets/admin-module/img/media/banner-upload-file.png')}}" alt="">');
                $(this).closest('.upload-file').find('.video_attachment').val('');
                $(this).closest('.upload-file').find('.video > video').css('display', 'none');
                videoEl.css('display', 'none');
                videoEl.attr('src', '');
            })
        })
    </script>

    <!-- Select Toggler Scripts -->
    <script>
        const handleUploadBox = () => {
            const value = $('.promotion_type').val();
            if (value == 'video_promotion') {
                $('#video-upload-box, #video-preview-box').show();
                $('#profile-upload-box, #profile-preview-box').hide();
            } else {
                $('#video-upload-box, #video-preview-box').hide();
                $('#profile-upload-box, #profile-preview-box').show();
            }
        }
        $(window).on('load', function () {
            handleUploadBox()
        })

        $('.promotion_type').on('change', function () {
            handleUploadBox();
            $('.remove-file-button').click()
        })
    </script>

    <!-- Profile Promotion Image Upload Handlr -->
    <script>
        $(".js-upload-input").on("change", function (event) {
            let file = event.target.files[0];
            const target = $(this).data('target');
            let blobURL = URL.createObjectURL(file);
            const prevImage = $(this).closest('.upload-file').find('.upload-file__img').find('img').attr('src');
            $(this).closest('.upload-file').find('.upload-file__img').html('<img src="' + blobURL + '" alt="">');
            $(this).closest('.upload-file').find('.remove-file-button').show()
            $('#profile-preview-box').find('.' + target).css('background', 'url(' + blobURL + ') no-repeat center center / cover');
            $(this).closest('.upload-file').find('.remove-file-button').on('click', function () {
                $(this).hide()
                $(this).closest('.upload-file').find('.upload-file__img').find('img').attr('src', prevImage);
                $(this).closest('.upload-file').find('.js-upload-input').val(file);
                $('#profile-preview-box').find('.' + target).css('background', 'rgba(117, 133, 144, 0.1)');
            })
        })
    </script>

    <!-- Title and Description Change Handlr -->
    <script>
        // $('[data-preview-text]').on('input', function (event) {
        //     const target = $(this).data('preview-text');
        //     if (event.target.value) {
        //         $('.' + target).each(function () {
        //             $(this).find('.main-text').text(event.target.value)
        //             $(this).find('.placeholder-text').hide()
        //             $(this).find('.static-text').show()
        //         })
        //     } else {
        //         $('.' + target).each(function () {
        //             $(this).find('.main-text').text('')
        //             $(this).find('.placeholder-text').show()
        //             $(this).find('.static-text').hide()
        //         })
        //     }
        // })
        // const resetTextHandlr = () => {
        //     $('[data-preview-text]').each(function () {
        //         const target = $(this).data('preview-text');
        //         const value = $(this).val()
        //         if (value) {
        //             $('.' + target).each(function () {
        //                 $(this).find('.main-text').text(value)
        //                 $(this).find('.placeholder-text').hide()
        //                 $(this).find('.static-text').show()
        //             })
        //         }
        //     })
        // }
        // $(window).on('load', function () {
        //     resetTextHandlr()
        // })

        $(document).ready(function() {
            // Function to update preview text based on the current form inputs
            function updatePreviewText(langCode) {
                const title = $(`#title-${langCode}`).val();
                const description = $(`#description-${langCode}`).val();

                $('.preview-title').each(function() {
                    const mainText = $(this).find('.main-text');
                    const placeholderText = $(this).find('.placeholder-text');
                    const staticText = $(this).find('.static-text');

                    if (title) {
                        mainText.text(title);
                        placeholderText.hide();
                        staticText.show();
                    } else {
                        mainText.text('');
                        placeholderText.show();
                        staticText.hide();
                    }
                });

                $('.preview-description').each(function() {
                    const mainText = $(this).find('.main-text');
                    const placeholderText = $(this).find('.placeholder-text');
                    const staticText = $(this).find('.static-text');

                    if (description) {
                        mainText.text(description);
                        placeholderText.hide();
                        staticText.show();
                    } else {
                        mainText.text('');
                        placeholderText.show();
                        staticText.hide();
                    }
                });
            }

            // Event listener for input change to update preview
            $('[data-preview-text]').on('input', function() {
                const currentLang = $('.lang-form:not(.d-none)').attr('id').split('-')[0];
                updatePreviewText(currentLang);
            });

            // Event listener for language tab click
            $('.lang_link').on('click', function(event) {
                event.preventDefault();
                const selectedLang = $(this).attr('id').split('-')[0];

                $('.lang_link').removeClass('active');
                $(this).addClass('active');

                $('.lang-form').addClass('d-none');
                $(`#${selectedLang}-form`).removeClass('d-none');

                updatePreviewText(selectedLang);
            });

            // Initial load handler
            $(window).on('load', function() {
                const initialLang = $('.lang_link.active').attr('id').split('-')[0];
                updatePreviewText(initialLang);
            });
        });

        $('#create-add-form').on('reset', function () {
            window.location.reload()
        })
    </script>

    <!-- Review and Rating Handlr -->
    <script>
        $('[name="review"]').on('change', function () {
            if ($(this).is(':checked')) {
                $('.review-placeholder').hide()
                $('.review--text').show()
            } else {
                $('.review-placeholder').show()
                $('.review--text').hide()
            }
        })
        $('[name="rating"]').on('change', function () {
            if ($(this).is(':checked')) {
                $('.rating-placeholder').hide()
                $('.rating-text').show()
            } else {
                $('.rating-placeholder').show()
                $('.rating-text').hide()
            }
        })


        $(window).on('load', function () {
            $('[name="review"]').each(function () {
                if ($(this).is(':checked')) {
                    $('.review-placeholder').hide()
                    $('.review--text').show()
                } else {
                    $('.review-placeholder').show()
                    $('.review--text').hide()
                }
            })
            $('[name="rating"]').each(function () {
                if ($(this).is(':checked')) {
                    $('.rating-placeholder').hide()
                    $('.rating-text').show()
                } else {
                    $('.rating-placeholder').show()
                    $('.rating-text').hide()
                }
            })
        })
    </script>

    <script>
        $(document).ready(function () {
            // Safely retrieve the provider ID
            var providerId = "{{$advertisement?->provider_id}}";

            // Check if providerId is not null or empty before making the AJAX request
            if (providerId) {
                $.ajax({
                    url: '{{ route("admin.provider.get-provider-info", ":provider_id") }}'.replace(':provider_id', providerId),
                    type: 'GET',
                    success: function (response) {
                        var rating = parseFloat(response.rating);
                        var stars = '';

                        // Generate stars based on the rating
                        for (var i = 0; i < 5; i++) {
                            if (rating >= i + 1) {
                                stars += '<span class="material-symbols-outlined">star</span>';
                            } else if (rating > i) {
                                stars += '<span class="material-symbols-outlined">star_half</span>';
                            } else {
                                stars += '<span class="material-symbols-outlined">star_border</span>';
                            }
                        }

                        // Display the generated stars and reviews text
                        $('.rating-number').html(stars);
                        $('.review--text').html(response.reviews + ' ' + '{{translate("Reviews")}}');
                    },
                    error: function (xhr) {
                        console.error("Error: ", xhr.responseText);
                    }
                });
            } else {
                console.error("Error: providerId is not set.");
            }
        });

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
