@extends('providermanagement::layouts.master')

@section('title',translate('Dashboard'))

@push('css_or_js')

@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <img width="20"
                                 src="{{asset('assets/provider-module')}}/img/media/business_analytics.png"
                                 alt="">
                            <h4>{{translate('Business Analytics')}}</h4>
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col-lg-3 col-sm-6">
                            <div class="business-summary business-summary-earning">
                                <h2>{{with_currency_symbol($data[0]['top_cards']['total_earning'])}}</h2>
                                <h3>{{translate('total_earning')}}</h3>
                                <img src="{{asset('assets/provider-module')}}/img/icons/total-earning.png"
                                     class="absolute-img" alt="">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="business-summary business-summary-customers">
                                <h2>{{$data[0]['top_cards']['total_subscribed_services']}}</h2>
                                <h3>{{translate('total_subscription')}}</h3>
                                <img src="{{asset('assets/provider-module')}}/img/icons/customers.png"
                                     class="absolute-img"
                                     alt="">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="business-summary business-summary-providers">
                                <h2>{{$data[0]['top_cards']['total_service_man']}}</h2>
                                <h3>{{translate('total_service_man')}}</h3>
                                <img src="{{asset('assets/provider-module')}}/img/icons/providers.png"
                                     class="absolute-img"
                                     alt="">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="business-summary business-summary-services">
                                <h2>{{$data[0]['top_cards']['total_booking_served']}}</h2>
                                <h3>{{translate('total_booking_served')}}</h3>
                                <img src="{{asset('assets/provider-module')}}/img/icons/services.png"
                                     class="absolute-img"
                                     alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3" data-bg-img="{{asset('assets/provider-module')}}/img/media/create-ads-bg.png">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="media align-items-center gap-3">
                            <img width="84" src="{{asset('assets/provider-module')}}/img/media/create-ads.png"
                                 alt="">
                            <div class="media-body">
                                <h4 class="mb-2">{{translate('Want To Get Highlighted?')}}</h4>
                                <p>{{translate('Create ads to get highlighted on the app and web browser')}}</p>
                            </div>
                        </div>

                        <a class="text-white btn btn--primary" href="{{route('provider.advertisements.ads-create')}}">{{translate('Create Ads')}}</a>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-9">
                    <div class="card earning-statistics h-100">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <h4 class="c1">{{translate('Earning_Statistics')}}</h4>
                                <div
                                    class="position-relative index-2 d-flex flex-wrap gap-3 align-items-center justify-content-between">
                                    <ul class="option-select-btn">
                                        <li>
                                            <label>
                                                <input type="radio" name="statistics" hidden checked>
                                                <span>{{translate('Yearly')}}</span>
                                            </label>
                                        </li>
                                    </ul>

                                    <div class="select-wrap d-flex flex-wrap gap-10">
                                        <select class="js-select" onchange="update_chart(this.value)">
                                            @php($from_year=date('Y'))
                                            @php($to_year=$from_year-10)
                                            @while($from_year!=$to_year)
                                                <option
                                                    value="{{$from_year}}" {{session()->has('dashboard_earning_graph_year') && session('dashboard_earning_graph_year') == $from_year?'selected':''}}>
                                                    {{$from_year}}
                                                </option>
                                                @php($from_year--)
                                            @endwhile
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="apex-line-chart-container">
                                <div id="apex_line-chart">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card recent-transactions h-100">
                        <div class="card-body">
                            <h4 class="mb-3 c1">{{translate('Recent_Transactions')}}</h4>
                            @if(isset($data[6]['recent_transactions']) && count($data[6]['recent_transactions']) > 0)
                                <div class="d-flex align-items-center gap-3 mb-4">
                                    <img src="{{asset('assets/provider-module')}}/img/icons/arrow-up.png" alt="">
                                    <p class="opacity-75">{{$data[6]['this_month_trx_count']}} {{translate('transactions_this_month')}}</p>
                                </div>
                            @endif
                            <div class="events">
                                @if($data[6]['this_month_trx_count'] > 0)
                                    @foreach($data[6]['recent_transactions'] as $transaction)
                                        <div class="event">
                                            <div class="knob"></div>
                                            <div class="title">
                                                @if($transaction->debit>0)
                                                    <h5>{{with_currency_symbol($transaction->debit)}} {{translate('debited')}}</h5>
                                                @else
                                                    <h5>{{with_currency_symbol($transaction->credit)}} {{translate('credited')}}</h5>
                                                @endif
                                            </div>
                                            <div class="description">
                                                <p>{{date('d M H:i a',strtotime($transaction->created_at))}}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                                <div class="line"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="card top-providers h-100">
                        <div class="card-header d-flex justify-content-between gap-10 pb-0">
                            <div class="">
                                <h5 class="c1 mb-3">{{translate('Recent_Bookings_Activity')}}</h5>

                                <ul class="nav nav--tabs custom-activate-tab gap-2 gap-sm-4">
                                    <li class="nav-item cursor-pointer">
                                        <a class="nav-link px-0 active" data-bs-toggle="tab" id="normal-tab"
                                           data-bs-target="#normal-bookings">{{translate('Normal_Bookings')}}</a>
                                    </li>
                                    <li class="nav-item cursor-pointer">
                                        <a class="nav-link px-0" data-bs-toggle="tab" id="customize-tab"
                                           data-bs-target="#customize-bookings">{{translate('Customize_Booking')}}</a>
                                    </li>
                                </ul>
                            </div>
                            <a href="{{route('provider.booking.list', ['booking_status'=>'pending'])}}" id="view-all-link"
                               class="btn-link c1">{{translate('View all')}}</a>
                        </div>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="normal-bookings">
                                <div class="card-body">
                                    <div class="row gy-5">
                                        <div class="@if(count($data[3]['recent_bookings']) < 1) col-lg-12 @else col-lg-6 @endif">
                                            <ul class="common-list pe-xl-3">
                                                @if(count($data[3]['recent_bookings']) < 1)
                                                <div class="py-5 text-center">
                                                    <div class="opacity-75">{{translate('No_recent_bookings_are_available')}}</div>
                                                </div>
                                                @endif
                                                @foreach($data[3]['recent_bookings'] as $key=>$booking)
                                                    <li class="@if($key==0) pt-0 @endif d-flex flex-wrap gap-2 align-items-center justify-content-between cursor-pointer booking-item"
                                                        data-booking="{{$booking->id}}">
                                                        <div class="media align-items-center gap-3">
                                                            <div class="avatar avatar-lg">
                                                                <img class="avatar-img rounded"
                                                                     src="{{$booking->detail[0]->service?->thumbnail_full_path}}"
                                                                     alt="{{ translate('booking thumbnail') }}">
                                                            </div>
                                                            <div class="media-body ">
                                                                <h5>{{translate('Booking')}}
                                                                    # {{$booking['readable_id']}}</h5>
                                                                <p>{{date('d-M-y H:iA', strtotime($booking->created_at))}}</p>
                                                            </div>
                                                        </div>
                                                        <span
                                                            class="badge py-2 px-3 badge-info">{{translate($booking['booking_status'])}}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        @if(count($data[3]['recent_bookings']) > 0)
                                        <div class="col-lg-6 border-lg-start">
                                            <div class="d-flex justify-content-center">
                                                <div id="apex-donut-chart"></div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-content">
                            <div class="tab-pane fade" id="customize-bookings">
                                <div class="card-body">
                                    <div class="row gy-5">
                                        <div class="@if(count($data[7]['customized_bookings']) < 1) col-lg-12 @else col-lg-6 @endif">
                                            <ul class="common-list pe-xl-3">
                                                @if(count($data[7]['customized_bookings']) < 1)
                                                <div class="py-5 text-center">
                                                    <div class="opacity-75">{{translate('No_recent_customized_bookings_are_available')}}</div>
                                                </div>
                                                @endif
                                                @foreach($data[7]['customized_bookings'] as $key=>$customBooking)
                                                    <li class="@if($key==0) pt-0 @endif d-flex flex-wrap gap-2 align-items-center justify-content-between cursor-pointer">
                                                        <div class="media align-items-center gap-3">
                                                            <div class="avatar avatar-lg">
                                                                <img class="avatar-img rounded"
                                                                     src="{{$customBooking?->service?->thumbnail_full_path}}"
                                                                     alt="{{ translate('booking thumbnail') }}">
                                                            </div>
                                                            <div class="media-body ">
                                                                <h5>{{$customBooking?->service?->name}}</h5>
                                                                <span>{{$customBooking?->sub_category?->name}}</span>
                                                                <p>{{date('d-M-y H:iA', strtotime($customBooking->created_at))}}</p>
                                                            </div>
                                                        </div>
                                                        <span
                                                            class="badge py-2 px-3 badge-info">{{translate($customBooking['booking_status'])}}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        @if(count($data[7]['customized_bookings']) > 0)
                                        <div class="col-lg-6 border-lg-start">
                                            <div class="d-flex justify-content-center">
                                                <div id="apex-donut-chart2"></div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="card recent-activities h-100">
                        <div class="card-header d-flex justify-content-between gap-10">
                            <h5 class="c1">{{translate('My_Subscriptions')}}</h5>
                            <a href="{{route('provider.sub_category.subscribed', ['status' => 'all'])}}"
                               class="btn-link c1">{{translate('View all')}}</a>
                        </div>
                        <div class="card-body">
                            <ul class="common-list">
                                @if(count($data[4]['subscriptions']) < 1)
                                    <span
                                        class="opacity-75">{{translate('No_subscribed_services_are_available')}}</span>
                                @endif
                                @foreach($data[4]['subscriptions'] as $key=>$subscription)
                                    <li class="@if($key==0) pt-0 @endif d-flex flex-wrap gap-2 align-items-center justify-content-between cursor-auto">
                                        <div class="media gap-10">
                                            <div class="avatar avatar-lg">
                                                <img class="avatar-img rounded"
                                                     src="{{$subscription->sub_category->image_full_path}}"
                                                     alt="">
                                            </div>
                                            <div class="media-body">
                                                <h5>{{ Str::limit($subscription->sub_category?$subscription->sub_category->name:'', 20) }}</h5>
                                                <p>{{$subscription['services_count'] . ' ' . translate('Services')}}</p>
                                            </div>
                                        </div>
                                        <span
                                            class="">{{$subscription['completed_booking_count'] . ' ' . translate('Bookings Completed')}}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="card top-providers h-100">
                        <div class="card-header d-flex justify-content-between gap-10">
                            <h5 class="c1">{{translate('Serviceman_List')}}</h5>
                            <a href="{{route('provider.serviceman.list')}}?status=all"
                               class="btn-link c1">{{translate('View all')}}</a>
                        </div>
                        <div class="card-body">
                            <ul class="common-list">
                                @if(count($data[5]['serviceman_list']) < 1)
                                    <span class="opacity-75">{{translate('No_active_servicemen_are_available')}}</span>
                                @endif
                                @foreach($data[5]['serviceman_list'] as $key=>$serviceman)
                                    <li class="@if($key==0) pt-0 @endif">
                                        <div class="media gap-3">
                                            <div class="avatar avatar-lg">
                                                <a href="{{route('provider.serviceman.show', [$serviceman['id']])}}">
                                                    <img class="object-fit rounded-circle"
                                                         src="{{$serviceman->user['profile_image_full_path']}}"
                                                         alt="">
                                                </a>
                                            </div>
                                            <div class="media-body ">
                                                <a href="{{route('provider.serviceman.show', [$serviceman['id']])}}">
                                                    <h5>{{Str::limit($serviceman->user['first_name'],30) }}</h5>
                                                </a>
                                                <p>{{Str::limit($serviceman->user['phone'],30) }}</p>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{asset('assets/provider-module')}}/plugins/apex/apexcharts.min.js"></script>
    <script>
        "use strict";

        var options = {
            series: [
                {
                    name: "{{translate('total_earnings')}}",
                    data: @json($chart_data['total_earning'])
                }
            ],
            chart: {
                height: 386,
                type: 'line',
                dropShadow: {
                    enabled: true,
                    color: '#000',
                    top: 18,
                    left: 7,
                    blur: 10,
                    opacity: 0.2
                },
                toolbar: {
                    show: false
                }
            },
            yaxis: {
                labels: {
                    offsetX: 0,
                    formatter: function (value) {
                        return value;
                    }
                },
            },
            colors: ['#82C662', '#4FA7FF'],
            dataLabels: {
                enabled: false,
            },
            stroke: {
                curve: 'smooth',
            },
            grid: {
                xaxis: {
                    lines: {
                        show: true
                    }
                },
                yaxis: {
                    lines: {
                        show: true
                    }
                },
                borderColor: '#CAD2FF',
                strokeDashArray: 5,
            },
            markers: {
                size: 1
            },
            theme: {
                mode: 'light',
            },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center',
                floating: true,
                offsetY: -10,
                offsetX: 0
            },
            padding: {
                top: 0,
                right: 0,
                bottom: 200,
                left: 10
            },
        };

        if (localStorage.getItem('dir') === 'rtl') {
            options.yaxis.labels.offsetX = -20;
        }

        var chart = new ApexCharts(document.querySelector("#apex_line-chart"), options);
        chart.render();

        function update_chart(year) {
            var chartElement = document.querySelector('#apex_line-chart');
            if (chartElement) {
                chartElement.remove();
            }

            var container = document.querySelector('.apex-line-chart-container');
            var newChartElement = document.createElement('div');
            newChartElement.id = 'apex_line-chart';
            container.appendChild(newChartElement);

            var url = '{{route('provider.update-dashboard-earning-graph')}}?year=' + year;

            $.getJSON(url, function (response) {
                var options = {
                    series: [{
                        name: "{{translate('total_earnings')}}",
                        data: response.total_earning
                    }],
                    chart: {
                        height: 386,
                        type: 'line',
                        dropShadow: {
                            enabled: true,
                            color: '#000',
                            top: 18,
                            left: 7,
                            blur: 10,
                            opacity: 0.2
                        },
                        toolbar: {
                            show: false
                        }
                    },
                    yaxis: {
                        labels: {
                            offsetX: 0,
                            formatter: function (value) {
                                return value;
                            }
                        }
                    },
                    colors: ['#82C662', '#4FA7FF'],
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth'
                    },
                    grid: {
                        xaxis: {
                            lines: {
                                show: true
                            }
                        },
                        yaxis: {
                            lines: {
                                show: true
                            }
                        },
                        borderColor: '#CAD2FF',
                        strokeDashArray: 5
                    },
                    markers: {
                        size: 1
                    },
                    theme: {
                        mode: 'light'
                    },
                    xaxis: {
                        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
                    },
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center',
                        floating: true,
                        offsetY: -10,
                        offsetX: 0
                    },
                    padding: {
                        top: 0,
                        right: 0,
                        bottom: 200,
                        left: 10
                    }
                };

                // Recreate the chart with the new data
                var chart = new ApexCharts(document.querySelector('#apex_line-chart'), options);
                chart.render();  // Render the chart
            });
        }

        $(document).ready(function () {
            let routeName = '{{ route('provider.booking.details', ['id' => ':id']) }}';

            $('.booking-item').on('click', function () {
                var bookingId = $(this).data('booking');
                var url = routeName.replace(':id', bookingId);
                window.location.href = url + '?web_page=details';
            });

            $('.custom-activate-tab li a').on('click', function (e) {
                e.preventDefault();
                $('.custom-activate-tab li a').removeClass('active');
                $(this).addClass('active');
            })
        });
    </script>
    <script>
        var options = {
            series: [{{$booking_counts['normal_booking_count']}}, {{$booking_counts['post_count']}}],
            chart: {
                type: 'donut',
                // width: '200',
            },
            labels: ["Total Normal Bookings", "Total Customized Bookings"],
            colors: ['#2A95FF', '#00C8A4'],
            legend: {
                position: 'bottom'
            },
        };

        var chart = new ApexCharts(document.querySelector("#apex-donut-chart"), options);
        var chart2 = new ApexCharts(document.querySelector("#apex-donut-chart2"), options);
        chart.render();
        chart2.render();
    </script>
    <script>
        $(document).ready(function() {
            $('#normal-tab').click(function() {
                var target = $(this).attr('data-bs-target');
                $('#view-all-link').attr('href', "{{route('provider.booking.list', ['booking_status'=>'pending'])}}");
            });
        });
        $(document).ready(function() {
            $('#customize-tab').click(function() {
                var target = $(this).attr('data-bs-target');
                $('#view-all-link').attr('href', "{{route('provider.booking.post.list', ['type'=>'all'])}}");
            });
        });
    </script>
@endpush
