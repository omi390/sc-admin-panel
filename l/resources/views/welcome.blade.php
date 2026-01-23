@extends('layouts.landing.app')

@section('title', bs_data($settings, 'business_name', 1))

@section('content')
    <section class="banner-section">
        <div class="container">
            <div class="banner-wrapper justify-content-between">
                <div class="banner-content wow animate__fadeInUp">
                    <h6 class="subtitle text--btn">{{ bs_data_text($settingss, 'top_title', 1) }}</h6>
                    <h1 class="title">{{ bs_data_text($settingss, 'top_description', 1) }}</h1>
                    <p class="txt text--title">
                        {{ bs_data_text($settingss, 'top_sub_title', 1) }}
                    </p>
                    <div class="app-btns d-flex flex-wrap">
                        @if ($settings->where('key_name', 'app_url_appstore')->first()->is_active ?? 0)
                            <a href="{{ bs_data($settings, 'app_url_appstore', 1) }}">
                                <img src="{{ asset('public/assets/landing/img/app-btn/app-store.png') }}"
                                    alt="{{ translate('app store') }}">
                            </a>
                        @endif

                        @if ($settings->where('key_name', 'app_url_playstore')->first()->is_active ?? 0)
                            <a href="{{ bs_data($settings, 'app_url_playstore', 1) }}">
                                <img src="{{ asset('public/assets/landing') }}/img/app-btn/google-play.png"
                                    alt="{{ translate('play store') }}">
                            </a>
                        @endif

                        @if ($settings->where('key_name', 'web_url')->first()->is_active ?? 0)
                            <a href="{{ bs_data($settings, 'web_url', 1) }}">
                                <img src="{{ asset('public/assets/landing') }}/img/app-btn/brows_button.png"
                                    alt="{{ translate('app') }}">
                            </a>
                        @endif
                    </div>
                </div>
                <div class="banner-thumb">
                    <div class="banner-thumb-wrapper">
                        <img class="wow animate__dropIn" src="{{ $topImageData['top_image_1'] }}"
                            alt="{{ translate('banner') }}">
                    </div>
                    <div class="banner-thumb-wrapper">
                        <img class="wow animate__dropIn" src="{{ $topImageData['top_image_2'] }}"
                            alt="{{ translate('banner') }}">
                    </div>
                    <div class="banner-thumb-wrapper">
                        <img class="wow animate__dropIn" src="{{ $topImageData['top_image_3'] }}"
                            alt="{{ translate('banner') }}">
                    </div>
                    <div class="banner-thumb-wrapper">
                        <img class="wow animate__dropIn" src="{{ $topImageData['top_image_4'] }}"
                            alt="{{ translate('banner') }}">
                    </div>

                </div>
            </div>
        </div>
    </section>
    <section class="service-section py-25">
        <div class="scroll-elem" id="service"></div>
        <div class="container position-relative">
            <h3 class="section-title">{{ bs_data_text($settingss, 'mid_title', 1) }}</h3>
            <div class="service-slide-nav">
                <span class="service-slide-prev slide-icon">
                    <i class="las la-arrow-left"></i>
                </span>
                <span class="service-slide-next slide-icon">
                    <i class="las la-arrow-right"></i>
                </span>
            </div>
            <div class="slider-wrapper">
                <div class="service-slider owl-theme owl-carousel">
                    @foreach ($categories as $category)
                        <a href="javascript:void(0)" class="service__item" data-target="slide-{{ $category->id }}">
                            <div class="service__item-icon">
                                <img src="{{ $category->image_full_path }}" alt="{{ translate('category') }}">
                            </div>
                            <div class="service__item-content">
                                <h6 class="title">{{ $category['name'] }}</h6>
                                <p class="txt">
                                    {{ translate('this_category_is_available_in') }} ( {{ $category->zones_count }}
                                    ) {{ translate('zones') }}. {{ translate('and_available_subcategories') }}
                                    ({{ $category->children->count() }})
                                </p>
                                <span class="service__item-btn">{{ translate('Read More') }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
                @foreach ($categories as $category)
                    <div class="service__item-popup" data-slide="slide-{{ $category->id }}">
                        <div class="service__item-popup-inner">
                            <button type="button" class="close__popup">
                                <i class="las la-times"></i>
                            </button>
                            <div class="left-content">
                                <div class="service__item-icon">
                                    <img src="{{ $category->image_full_path }}" alt="{{ translate('category') }}">
                                </div>
                                <div class="service__item-content">
                                    <h6 class="title">{{ $category['name'] }}</h6>
                                    <p class="txt">
                                        {{ translate('this_category_is_available_in') }} ( {{ $category->zones_count }}
                                        ) {{ translate('zones') }}. {{ translate('and_available_subcategories') }}
                                        ( {{ $category->children->count() }} )
                                    </p>
                                </div>
                            </div>
                            <div class="right-content ms-sm-5">
                                <div class="mb-4">
                                    <span class="top-text">{{translate('Thousands of companies use on demand service platform  as their go-to place for all kinds of cleaning and maintenance services. Our verified service providers are always ready for your next call whenever you have a business need. A dedicated Key Account Manager and a 24 X 7 customer support team make us your first choice for many companies.')}}</span>
                                </div>
                                <div class="service-inner-slider owl-theme owl-carousel">
                                    @foreach ($category->children as $child)
                                        <div class="service-inner-slider-item">
                                            <img src="{{ $child->image_full_path }}" alt="{{ translate('child image') }}">
                                            <span>{{ $child['name'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>


        </div>
    </section>
    <section class="about-section py-25">
        <div class="scroll-elem" id="about"></div>
        <div class="container">
            <div class="about__wrapper">
                <div class="about__wrapper-content  wow animate__fadeInUp">
                    <h3 class="section-title text-start ms-0">{{ bs_data_text($settingss, 'about_us_title', 1) }}</h3>
                    <p>
                        {{ bs_data_text($settingss, 'about_us_description', 1) }}
                    </p>
                    <a href="{{ route('page.about-us') }}" class="cmn--btn2">
                        {{ translate('Read More') }} <i class="las la-long-arrow-alt-right"></i>
                    </a>
                </div>
                <div class="about__wrapper-thumb">
                    @php($aboutUsImage = getBusinessSettingsImageFullPath(key: 'about_us_image', settingType: 'landing_images', path: 'landing-page/', defaultPath: 'public/assets/placeholder.png'))
                    <img class="main-img" src="{{ $aboutUsImage }}" alt="{{ translate('image') }}">
                    <div class="bg-img">
                        <img src="{{ asset('public/assets/landing') }}/img/about-us.png" alt="{{ translate('image') }}">
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="contact-info-section py-25 wow animate__fadeInUp">
        <div class="container">
            <div class="row g-2 g-sm-3 g-md-4 justify-content-center">
                @foreach ($specialities ?? [] as $item)
                    <div class="col-sm-6 col-lg-4">
                        <div class="counter__item h-100">
                            <div class="counter__item-left">
                                <img src="{{ $item['image_full_path'] }}" alt="{{ translate('counter') }}">
                            </div>
                            <div class="counter__item-right">
                                <h3 class="subtitle mb-2">
                                    <span class="ms-1">{{ $item['title'] }}</span>
                                </h3>
                                <div>{{ $item['description'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    <section class="app-slider-section pt-25 pb-50">
        <div class="container">
            <div class="app-slider-wrapper">
                <div class="app-content">
                    <div class="app-slider owl-theme owl-carousel">
                        @foreach ($features ?? [] as $item)
                            <div>
                                <h3 class="subtitle">{{ $item['title'] }}</h3>
                                <p>{{ $item['sub_title'] }}</p>
                            </div>
                        @endforeach
                    </div>
                    <div class="slider-bottom mt-4 mt-lg-5 d-flex justify-content-center">
                        <div class="owl-btn app-owl-prev">
                            <i class="las la-long-arrow-alt-left"></i>
                        </div>
                        <div class="app-counter mx-3"></div>
                        <div class="owl-btn app-owl-next">
                            <i class="las la-long-arrow-alt-right"></i>
                        </div>
                    </div>
                </div>
                <div class="app-thumb">
                    <div class="main-thumb">
                        <img class="main-img" src="{{ asset('public/assets/landing/img/app/iphone-frame.png') }}"
                            alt="{{ translate('app') }}">
                        <div class="app-slider owl-theme owl-carousel">
                            @foreach ($features ?? [] as $item)
                                <img src="{{ $item['image_1_full_path'] }}" alt="{{ translate('app') }}">
                            @endforeach
                        </div>
                    </div>
                    <div class="smaller-thumb">
                        <img class="main-img" src="{{ asset('public/assets/landing') }}/img/app/iphone-frame.png"
                            alt="{{ translate('app') }}">
                        <div class="app-slider owl-theme owl-carousel">
                            @foreach ($features ?? [] as $item)
                                <img src="{{ $item['image_2_full_path'] }}" alt="{{ translate('app') }}">
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="cta-section py-25">
        <div class="container">
            <div class="cta-main">
                @php($providerSectionImage = getBusinessSettingsImageFullPath(key: 'provider_section_image', settingType: 'landing_images', path: 'landing-page/', defaultPath: 'public/assets/placeholder.png'))

                <div class="cta-wrapper bg__img" data-img="{{ asset('public/assets/landing') }}/img/cta-bg.png">
                    <img width="238" src="{{ $providerSectionImage }}" alt="{{ translate('image') }}"
                        class="left-icon">
                    <div class="content text-center">
                        <h2 class="title text-uppercase">{{ bs_data_text($settingss, 'registration_title', 1) }}</h2>
                        <p class="text-btn-title">
                            {{ bs_data_text($settingss, 'registration_description', 1) }}
                        </p>
                    </div>
                    @if (bs_data_text($settingss, 'registration_description', 1) ?? 0)
                        <div class="text-center">
                            <a href="{{ route('provider.auth.sign-up') }}"
                                class="cmn--btn">{{ translate('register_here') }}</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
    <section class="testimonial-section pt-25 pb-50">
        <div class="container-fluid">
            <h3 class="section-title mb-5"> {{ bs_data_text($settingss, 'bottom_title', 1) }}</h3>
            {{-- <div class="testimonial-slider owl-theme owl-carousel">
                @foreach ($testimonials ?? [] as $item)
                    <div class="testimonial__item">
                        <div class="testimonial__item-img">
                            <img src="{{ $item['image_full_path'] }}" alt="{{ translate('image') }}">
                        </div>
                        <div class="testimonial__item-cont">
                            <span class="fw-bold fs-5">{{ $item['name'] }}</span><br>
                            <span class="text--secondary">{{ $item['designation'] }} </span>
                            <blockquote>
                                {{ $item['review'] }}
                            </blockquote>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="slider-bottom d-flex justify-content-center">
                <div class="owl-btn testimonial-owl-prev">
                    <i class="las la-long-arrow-alt-left"></i>
                </div>
                <div class="slider-counter mx-3"></div>
                <div class="owl-btn testimonial-owl-next">
                    <i class="las la-long-arrow-alt-right"></i>
                </div>
            </div> --}}
            <div class="testimonial-slider swiper">
                <div class="swiper-wrapper">
                    @foreach ($testimonials ?? [] as $item)
                        <div class="testimonial__item swiper-slide">
                            <div class="testimonial__item-wrapper">
                                <div class="testimonial__item-img">
                                    <img src="{{ $item['image_full_path'] }}" alt="{{ translate('image') }}">
                                </div>
                                <div class="testimonial__item-cont">
                                    <span class="fw-bold fs-5">{{ $item['name'] }}</span><br>
                                    <span class="text--secondary">{{ $item['designation'] }} </span>
                                    <blockquote>
                                        {{ $item['review'] }}
                                    </blockquote>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="slider-bottom d-flex justify-content-center">
                    <div class="owl-btn testimonial-owl-prev">
                        <i class="las la-long-arrow-alt-left"></i>
                    </div>
                    <div class="slider-counter mx-3"></div>
                    <div class="owl-btn testimonial-owl-next">
                        <i class="las la-long-arrow-alt-right"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
