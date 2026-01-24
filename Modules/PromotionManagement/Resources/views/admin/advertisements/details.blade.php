@extends('adminmodule::layouts.master')

@section('title',translate('Ads Details'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/css/lightbox.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('/public/assets/admin-module/css/daterangepicker.css')}}"/>
@endpush

@section('content')

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex align-items-center flex-wrap-reverse justify-content-between gap-3 mb-3">
                <div class="page-title-wrap mb-3">
                    <h2 class="page-title mb-2">{{translate('Ad Details')}} #{{ $advertisement->readable_id }}</h2>
                    <p>{{translate('Ad Placed')}} : {{ $advertisement->created_at->format('d/m/Y g:i A') }}</p>
                </div>
                <div class="d-flex align-items-center flex-wrap gap-3">
                    @can('advertisement_manage_status')
                        @if($advertisement->status == 'pending')
                            <a type="button" class="btn btn-danger text-capitalize provider_approval" data-status="deny"
                               data-bs-toggle="modal" data-bs-target="#deniedModal-{{$advertisement->id}}">
                                <span class="material-icons m-0">close</span>
                                <span class="text-uppercase">{{translate('Deny')}}</span>
                            </a>

                        @endif
                        @if($advertisement->status == 'pending' || $advertisement->status == 'denied')
                            <a type="button" class="btn btn--success text-capitalize approval_provider"
                               data-approve="approve"
                               href="{{route('admin.advertisements.status-update',[$advertisement->id, 'approved'])}}">
                                <span class="material-icons m-0">done_outline</span>
                                <span class="text-uppercase">{{translate('Approve')}}</span>
                            </a>
                        @endif
                    @endcan

                    @can('advertisement_update')
                        <a href="{{route('admin.advertisements.edit',[$advertisement->id])}}" class="btn btn--primary">
                            <span class="material-icons">border_color</span>
                            <span class="text-uppercase">{{translate('Edit Ads')}}</span>
                        </a>
                    @endcan
                </div>
            </div>

            {{-- Language Tabs --}}
            @php($language = Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name', 'system_language')->first())
            @php($default_lang = str_replace('_', '-', app()->getLocale()))
            @if($language)
                <ul class="nav nav--tabs border-color-primary mb-4">
                    <li class="nav-item">
                        <a class="nav-link lang_link active" href="#default-link">{{ translate('default') }}</a>
                    </li>
                    @foreach ($language->live_values as $lang)
                        <li class="nav-item">
                            <a class="nav-link lang_link"
                               href="#{{ $lang['code'] }}-link">{{ get_language_name($lang['code']) }}</a>
                        </li>
                    @endforeach
                </ul>
            @endif


            <div class="row">
                <div class="col-lg-8">
                    @if(in_array($advertisement->status, ['canceled', 'paused', 'denied']))
                        @if($advertisement?->note?->whereIn('type',['denied','canceled','paused'])->latest()?->first() != null)
                            @php($adsNote = optional($advertisement->note->whereIn('type', ['denied', 'canceled', 'paused'])->latest()->first()))
                            <div
                                class="cancellantion-note d-flex align-items-center gap-2 rounded mb-3 flex-wrap flex-lg-nowrap">
                                @if($adsNote->type == 'paused' && $advertisement->status == 'paused')
                                    <strong class="text-danger text-nowrap"># {{translate('Paused Note:')}}</strong>
                                    <span>{{$adsNote->note}}</span>
                                @elseif($adsNote->type == 'canceled' && $advertisement->status == 'canceled')
                                    <strong
                                        class="text-danger text-nowrap"># {{translate('Cancellation Note:')}}</strong>
                                    <span>{{$adsNote->note}}</span>
                                @elseif($adsNote->type == 'denied'  && $advertisement->status == 'denied')
                                    <strong class="text-danger text-nowrap"># {{translate('Denied Note:')}}</strong>
                                    <span>{{$adsNote->note}}</span>
                                @endif
                            </div>
                        @endif
                    @endif
                    <div class="card mb-3 h-100">
                        <div class="card-body pb-5">
                            <div class="row g-4">
                                <div class="col-lg-7">

                                    @foreach ($language->live_values as $lang)
                                            <?php
                                            $translatedTitle = null;
                                            $translatedDescription = null;

                                            foreach ($advertisement->translations as $translation) {
                                                if ($translation->locale == $lang['code']) {
                                                    if ($translation->key == 'title') {
                                                        $translatedTitle = $translation->value;
                                                    }
                                                    if ($translation->key == 'description') {
                                                        $translatedDescription = $translation->value;
                                                    }
                                                }
                                            }
                                            ?>

                                        <div id="{{ $lang['code'] }}-link-content" class="language-content" style="display: none">
                                            <h4 class="mb-2">{{ translate('Title:') }}</h4>
                                            <p class=""><span class="text-capitalize">{{ $translatedTitle ?? $advertisement->title }}</span></p>

                                            <h4 class="mb-2">{{ translate('Description:') }}</h4>
                                            <p class=""><span class="text-capitalize">{{ $translatedDescription ?? $advertisement->description }}</span></p>
                                        </div>
                                    @endforeach


                                        <div id="default-link-content" class="default-content">
                                            <h4 class="mb-2">{{ translate('Title:') }}</h4>
                                            <p class=""><span class="text-capitalize">{{  $advertisement->title }}</span></p>

                                            <h4 class="mb-2">{{ translate('Description:') }}</h4>
                                            <p class=""><span class="text-capitalize">{{  $advertisement->description }}</span></p>
                                        </div>

                                    <div class="d-flex justify-content-between mt-5">
                                        @if($advertisement->type == 'video_promotion')
                                            <div class="d-flex flex-column gap-2">
                                                <h4 class="mb-2">{{translate('Video')}}</h4>

                                                <div class="video position-relative">
                                                    <div class="play-icon absolute-centered">
                                                        <span class="material-icons">play_circle</span>
                                                    </div>
                                                    <div id="light">
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <h5>{{ translate('Video Preview') }}</h5>
                                                            <a class="boxclose" id="boxclose"
                                                               onclick="lightbox_close();"></a>
                                                        </div>
                                                        <video id="VisaChipCardVideo" width="600" controls>
                                                            <source src="{{$advertisement?->promotional_video_full_path}}" type="video/mp4">
                                                        </video>
                                                    </div>

                                                    <div id="fade" onClick="lightbox_close();"></div>

                                                    <div>
                                                        <a href="#" class="d-block h-200" onclick="lightbox_open();">
                                                            <video class="max-w360" width="450">
                                                                <source src="{{$advertisement?->promotional_video_full_path}}" type="video/mp4">
                                                            </video>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="d-flex gap-3 flex-wrap">
                                                <div class="d-flex flex-column gap-2">
                                                    <h4 class="mb-2">{{translate('Logo / Profile Image:')}}</h4>

                                                    <div class="img-wrap">
                                                        <a data-lightbox="mygallery" href="{{ $advertisement?->provider_cover_image_full_path}}">
                                                            <img class="h-120 profile_img rounded" src="{{ $advertisement?->provider_cover_image_full_path}}">
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column gap-2">
                                                    <h4 class="mb-2">{{translate('Cover Image')}}</h4>

                                                    <div class="img-wrap">
                                                        <a data-lightbox="mygallery2" href="{{ $advertisement?->provider_profile_image_full_path}}">
                                                            <img class="h-120 rounded" src="{{ $advertisement?->provider_profile_image_full_path}}">
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="d-flex flex-column gap-2 gap-lg-3 align-items-lg-end">
                                        <p class="d-flex gap-2 align-items-center mb-0">
                                            <span>{{translate('Request Verify Status:')}} </span>
                                            <span class="text-primary text-capitalize fw-semibold"
                                                id="payment_status__span">{{ $advertisement->status }}</span>
                                        @if(\Carbon\Carbon::parse($advertisement->end_date)->startOfDay() < \Carbon\Carbon::today())
                                                <small class="text-muted text-center">({{translate('Expired')}})</small>
                                            @endif
                                            @if(($advertisement->status == 'approved' || $advertisement->status == 'resumed') && ($advertisement->start_date <= \Carbon\Carbon::today() && $advertisement->end_date >= \Carbon\Carbon::today() ) )
                                                <small class="text-success text-center">({{translate('Running')}})</small>
                                            @endif
                                        </p>
                                        <p class="d-flex gap-2 align-items-center mb-0">
                                            <span>{{translate('Payment Status:')}} </span>
                                            <span
                                                class="fw-semibold {{ $advertisement->is_paid == 1 ? 'text-primary' : 'text-danger' }}"
                                                id="payment_status__span">{{ $advertisement->is_paid == 1 ? 'Paid' : 'Unpaid' }}</span>
                                        </p>

                                        <p class="d-flex gap-2 align-items-center mb-0">
                                            <span>{{translate('Ads Type:')}} </span>
                                            <span class="fw-bold"
                                                id="payment_status__span">{{ ucwords(str_replace('_', ' ', $advertisement->type)) }}</span>
                                        </p>

                                        <p class="d-flex gap-2 align-items-center mb-0">
                                            <span>{{translate('Duration:')}} </span>
                                            <span class="fw-bold" id="payment_status__span">{{ $advertisement->start_date->format('Y-m-d') }} - {{ $advertisement->end_date->format('Y-m-d') }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="c1">{{translate('Ad Setup')}}</h3>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center gap-10 form-control"
                                 id="payment-status-div">
                                <span class="title-color">
                                    {{translate('Payment Status')}}
                                </span>
                                @can('advertisement_manage_status')
                                    <div class="on-off-toggle">
                                        <input class="on-off-toggle__input switcher_input change-payment-status"
                                               value="{{$advertisement['is_paid'] ? 1 : 0}}"
                                               {{$advertisement['is_paid'] ? 'checked' : ''}} type="checkbox"
                                               id="payment_status"/>
                                        <label for="payment_status" class="on-off-toggle__slider">
                                        <span class="on-off-toggle__on">
                                            <span class="on-off-toggle__text">{{translate('Paid')}}</span>
                                            <span class="on-off-toggle__circle"></span>
                                        </span>
                                            <span class="on-off-toggle__off">
                                            <span class="on-off-toggle__circle"></span>
                                            <span class="on-off-toggle__text">{{translate('Unpaid')}}</span>
                                        </span>
                                        </label>
                                    </div>
                                @endcan
                            </div>


                            @can('advertisement_manage_status')
                                <div class="mt-3" id="change_schedule">
                                    <div class="position-relative">
                                            <?php
                                            $startDate = new DateTime($advertisement['start_date']);
                                            $endDate = new DateTime($advertisement['end_date']);

                                            $formattedStartDate = $startDate->format('m/d/Y');
                                            $formattedEndDate = $endDate->format('m/d/Y');
                                            ?>


                                        <span class="material-symbols-outlined icon-absolute-on-right">calendar_month</span>
                                        <input type="text" class="form-control h-45 position-relative bg-transparent" id="dates"
                                            name="dates"
                                            placeholder="Select Validation Date"
                                               value="<?php echo $formattedStartDate . ' - ' . $formattedEndDate ?>">
                                    </div>
                                </div>
                            @endcan

                            <div class="py-3 d-flex flex-column gap-3 mb-2">
                                <div class="c1-light-bg radius-10 provider-information">
                                    <div
                                            class="border-bottom d-flex align-items-center justify-content-between gap-2 py-3 px-4 mb-2">
                                        <h4 class="d-flex align-items-center gap-2">
                                            <span class="material-icons title-color">person</span>
                                            {{translate('Provider_Information')}}
                                        </h4>
                                        <div class="btn-group">
                                            <div class="cursor-pointer" data-bs-toggle="dropdown"
                                                 aria-expanded="false">
                                                <span class="material-symbols-outlined">more_vert</span>
                                            </div>
                                            <ul class="dropdown-menu dropdown-menu__custom border-none dropdown-menu-end">
                                                <li>
                                                    <div
                                                            class="d-flex align-items-center gap-2 cursor-pointer provider-chat">
                                                        <span class="material-symbols-outlined">chat</span>
                                                        {{translate('chat_with_Provider')}}
                                                        <form action="{{route('admin.chat.create-channel')}}"
                                                              method="post" id="chatForm-{{$advertisement->id}}">
                                                            @csrf
                                                            <input type="hidden" name="provider_id"
                                                                   value="{{$advertisement?->provider?->owner->id}}">
                                                            <input type="hidden" name="type" value="booking">
                                                            <input type="hidden" name="user_type"
                                                                   value="provider-admin">
                                                        </form>
                                                    </div>
                                                </li>
                                                <li>
                                                    <a class="d-flex align-items-center gap-2 cursor-pointer p-0"
                                                       href="{{route('admin.provider.details',[$advertisement?->provider_id, 'web_page'=>'overview'])}}">
                                                        <span class="material-icons">person</span>
                                                        {{translate('View_Details')}}
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="py-3 px-4">
                                        <div class="media gap-2 flex-wrap">
                                            <img width="58" height="58" class="rounded-circle border border-white"
                                                 src="{{$advertisement?->provider?->logo_full_path}}"
                                                 alt="{{translate('provider')}}">
                                            <div class="media-body">
                                                <a href="#">
                                                    <h5 class="c1 mb-3">{{$advertisement?->provider?->company_name }}</h5>
                                                </a>
                                                <ul class="list-info">
                                                    <li>
                                                        <span class="material-icons">phone_iphone</span>
                                                        <a href="tel:{{234567890??''}}">{{$advertisement?->provider?->company_phone}}</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="deniedModal-{{$advertisement->id}}" tabindex="-1"
             aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body pt-5 p-md-5">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        <div class="d-flex justify-content-center mb-4">
                            <img width="75" height="75" src="{{asset('public/assets/admin-module/img/delete2.png')}}"
                                 class="rounded-circle" alt="">
                        </div>

                        <h3 class="text-start mb-1 fw-medium text-center">{{translate('Are you sure you want to deny the request?')}}</h3>
                        <p class="text-start fs-12 fw-medium text-muted text-center">{{translate('You will lost the provider ads request')}}</p>
                        <form method="post"
                              action="{{route('admin.advertisements.status-update',[$advertisement->id, 'denied'])}}">
                            @csrf
                            <div class="form-floating">
                                <textarea class="form-control h-69px" placeholder="{{translate('Denied Note')}}"
                                          name="note" id="add-your-note" required></textarea>
                                <label for="add-your-note"
                                       class="d-flex align-items-center gap-1">{{translate('Deny Note')}}</label>
                                <div class="d-flex justify-content-center mt-3 gap-3">
                                    <button type="button" class="btn btn--secondary min-w-92px px-2"
                                            data-bs-dismiss="modal"
                                            aria-label="Close">{{translate('Not Now')}}</button>
                                    <button type="submit"
                                            class="btn btn--danger min-w-92px">{{translate('Yes')}}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script src="{{asset('public/assets/js/lightbox.min.js')}}"></script>
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
    <script>

        $('.change-payment-status').on('click', function () {
            let paymentStatus = $(this).is(':checked') === true ? 1 : 0;
            let route = '{{route('admin.advertisements.payment-update',[$advertisement->id])}}' + '?payment_status=' + paymentStatus;

            Swal.fire({
                title: "{{translate('are_you_sure')}}?",
                text: "{{translate('want_to_update_payment_status')}}",
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'var(--c2)',
                confirmButtonColor: 'var(--c1)',
                cancelButtonText: '{{translate('Cancel')}}',
                confirmButtonText: '{{translate('Yes')}}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href = route;
                }
            })
        })

        window.document.onkeydown = function (e) {
            if (!e) {
                e = event;
            }
            if (e.keyCode == 27) {
                lightbox_close();
            }
        }

        function lightbox_open() {
            var lightBoxVideo = document.getElementById("VisaChipCardVideo");
            window.scrollTo(0, 0);
            document.getElementById('light').style.display = 'block';
            document.getElementById('fade').style.display = 'block';
            lightBoxVideo.play();
        }

        function lightbox_close() {
            var lightBoxVideo = document.getElementById("VisaChipCardVideo");
            document.getElementById('light').style.display = 'none';
            document.getElementById('fade').style.display = 'none';
            lightBoxVideo.pause();
        }

        $('.provider-chat').on('click', function () {
            $(this).find('form').submit();
        });

    </script>

    <script>
        $('#dates').on('apply.daterangepicker', function () {
            let dates_value = $(this).val();
            let route = '{{route('admin.advertisements.dates-update',[$advertisement->id])}}' + '?dates=' + dates_value;

            Swal.fire({
                title: "{{translate('are_you_sure')}}?",
                text: "{{translate('want_to_update_ads_date')}}",
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'var(--c2)',
                confirmButtonColor: 'var(--c1)',
                cancelButtonText: '{{translate('Cancel')}}',
                confirmButtonText: '{{translate('Yes')}}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href = route;
                }
            })
        })

    </script>

    <script>
        $(document).ready(function () {
            $('.lang_link').click(function (e) {
                e.preventDefault();
                var id = $(this).attr('href').substr(1);
                console.log(id);
                $('.lang_link').removeClass('active');
                $(this).addClass('active');

                // Hide all language contents except the one clicked

                if(id === 'default-link'){
                    $('.default-content').show();
                    $('.language-content').hide();
                    $('#' + id + '-content').hide();
                }else {
                    $('.default-content').hide();
                    $('.language-content').hide();
                    $('#' + id + '-content').show();
                }
            });
        });
    </script>
@endpush
