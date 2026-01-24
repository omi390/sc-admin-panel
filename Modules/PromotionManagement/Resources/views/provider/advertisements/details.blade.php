@extends('providermanagement::layouts.master')

@section('title',translate('Ads Details'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/css/lightbox.css')}}">
@endpush

@section('content')

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <div class="page-title-wrap">
                    <h2 class="page-title mb-2">{{translate('Ad Details')}} #{{ $advertisement->readable_id }}</h2>
                    <p>{{translate('Ad Placed')}} : {{ $advertisement->created_at->format('d/m/Y g:i A') }}</p>
                </div>
                <a href="{{ route('provider.advertisements.edit',[$advertisement->id]) }}">
                    <button class="btn btn--primary">
                        <span class="material-symbols-outlined">edit</span>
                            <span>{{translate('Edit_Ads')}}</span>
                    </button>
                </a>
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

            <div class="row gy-3">
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
                    <div class="card h-100">
                        <div class="card-body pb-5">
{{--                            <h4 class="mb-2">{{translate('Title:')}}</h4>--}}
{{--                            <p class=""><span class="text-capitalize">{{$advertisement->title}}</span></p>--}}
{{--                            <h4 class="mb-2">{{translate('Description:')}}</h4>--}}
{{--                            <p class=""><span class="text-capitalize">{{$advertisement->description}}</span></p>--}}

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
                                    <h4 class="mb-2">{{ translate('Title:') }} ({{ $lang['code'] }})</h4>
                                    <p class=""><span class="text-capitalize">{{ $translatedTitle ?? $advertisement->title }}</span></p>
                                    <h4 class="mb-2">{{ translate('Description:') }}</h4>
                                    <p class=""><span class="text-capitalize">{{ $translatedDescription ?? $advertisement->description }}</span></p>
                                </div>
                            @endforeach

                            <div id="default-link-content" class="default-content">
                                <h4 class="mb-2">{{ translate('Title:') }}</h4>
                                <p class=""><span class="text-capitalize">{{ $advertisement->title }}</span></p>
                                <h4 class="mb-2">{{ translate('Description:') }}</h4>
                                <p class=""><span class="text-capitalize">{{ $advertisement->description }}</span></p>
                            </div>

                            <div class="d-flex justify-content-between">
                                @if($advertisement->type == 'video_promotion')
                                    <div class="d-flex flex-column gap-2">
                                        <h4 class="mb-2">{{translate('Video')}}</h4>

                                        <div class="video position-relative">
                                            <div class="play-icon absolute-centered">
                                                <span class="material-icons">play_circle</span>
                                            </div>
                                            <div id="light">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <h5>{{translate('Video Preview')}}</h5>
                                                    <a class="boxclose" id="boxclose" onclick="lightbox_close();"></a>
                                                </div>
                                                <video id="VisaChipCardVideo" width="600" controls>
                                                    <source src="{{ $advertisement?->promotional_video_full_path}}" type="video/mp4">
                                                </video>
                                            </div>

                                            <div id="fade" onClick="lightbox_close();"></div>

                                            <div>
                                                <a href="#" onclick="lightbox_open();" class="d-block h-200" >
                                                    <video class="max-w360" width="450">
                                                        <source src="{{ $advertisement?->promotional_video_full_path}}" type="video/mp4">
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
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="c1">{{translate('Ad Setup')}}</h3>
                            <hr>
                            <div class="d-flex flex-column gap-2 gap-lg-3">
                                <p class="d-flex gap-2 justify-content-between align-items-center mb-0">
                                    <span>{{translate('Request Verify Status:')}} </span>
                                    <span class="d-flex gap-1">
                                        <span class="text-primary text-capitalize fw-semibold" id="payment_status__span">{{ $advertisement->status }}</span>
                                    @if(\Carbon\Carbon::parse($advertisement->end_date)->startOfDay() < \Carbon\Carbon::today())
                                            <small class="text-muted text-center">({{translate('Expired')}})</small>
                                        @endif
                                        @if(($advertisement->status == 'approved' || $advertisement->status == 'resumed') && ($advertisement->start_date <= \Carbon\Carbon::today() && $advertisement->end_date >= \Carbon\Carbon::today() ) )
                                            <small class="text-success text-center">({{translate('Running')}})</small>
                                        @endif
                                    </span>
                                </p>
                                <p class="d-flex gap-2 justify-content-between align-items-center mb-0">
                                    <span>{{translate('Payment Status:')}} </span>
                                    @if($advertisement->is_paid)
                                        <span class="text-success fw-semibold" id="payment_status__span">{{translate('Paid')}}</span>
                                    @else
                                        <span class="text-danger fw-semibold" id="payment_status__span">{{translate('Unpaid')}}</span>
                                    @endif
                                </p>

                                <p class="d-flex gap-2 justify-content-between align-items-center mb-0">
                                    <span>{{translate('Ads Type:')}} </span>
                                    <span class="fw-bold" id="payment_status__span">{{ ucfirst(str_replace('_', ' ', $advertisement->type)) }}</span>
                                </p>

                                <p class="d-flex gap-2 justify-content-between align-items-center mb-0">
                                    <span>{{translate('Duration:')}} </span>
                                    <span class="fw-bold" id="payment_status__span">{{ $advertisement->start_date->format('Y-m-d') }} - {{ $advertisement->end_date->format('Y-m-d') }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
<script src="{{asset('public/assets/js/lightbox.min.js')}}"></script>
<script>
    window.document.onkeydown = function(e) {
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
</script>

<script>
    $(document).ready(function () {
        $('.lang_link').click(function (e) {
            e.preventDefault();
            var id = $(this).attr('href').substr(1);
            console.log(id);
            console.log(id + '-content');
            $('.lang_link').removeClass('active');
            $(this).addClass('active');

            if (id === 'default-link') {
                console.log('de');
                $('.default-content').show();
                $('.language-content').hide();
            } else {
                console.log('el');
                $('.default-content').hide();
                $('.language-content').hide();
                $('#' + id + '-content').show();
            }
        });
    });
</script>
@endpush
