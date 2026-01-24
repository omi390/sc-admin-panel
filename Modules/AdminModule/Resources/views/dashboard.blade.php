@extends('adminmodule::layouts.master')

@section('title',translate('dashboard'))

@section('content')
    @can('dashboard')
    <div class="main-content">
        <div class="container-fluid">
            @if(access_checker('dashboard'))
                <div class="row mb-4 g-4">
                    <div class="col-lg-3 col-sm-6">
                        <div class="business-summary business-summary-customers">
                            <h2>{{with_currency_symbol(data_get($data[0], 'top_cards.total_commission_earning', 0) + data_get($data[0], 'top_cards.total_fee_earning', 0) + data_get($data[0], 'top_cards.total_subscription_earning', 0))}}</h2>
                            <h3>{{translate('total_earning')}}</h3>
                            <img src="{{asset('assets/admin-module')}}/img/icons/customers.png"
                                 class="absolute-img"
                                 alt="">
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="business-summary business-summary-earning">
                            <h2>{{data_get($data[0], 'top_cards.total_commission_earning', 0)}}</h2>
                            <h3>{{translate('commission_earning')}}</h3>
                            <img src="{{asset('assets/admin-module')}}/img/icons/total-earning.png"
                                 class="absolute-img" alt="">
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="business-summary business-summary-providers">
                            <h2>{{data_get($data[0], 'top_cards.total_fee_earning', 0)}}</h2>
                            <h3>{{translate('Total Fee Earning')}}</h3>
                            <img src="{{asset('assets/admin-module')}}/img/icons/providers.png"
                                 class="absolute-img"
                                 alt="">
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6">
                        <div class="business-summary business-summary-services">
                            <h2>{{$data[0]['top_cards']['total_provider']}}</h2>
                            <h3>{{translate('providers')}}</h3>
                            <img src="{{asset('assets/admin-module')}}/img/icons/services.png"
                                 class="absolute-img"
                                 alt="">
                        </div>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-lg-9">
                        <div class="card earning-statistics">
                            <div class="card-body ps-0">
                                <div class="ps-20 d-flex flex-wrap align-items-center justify-content-between gap-3">
                                    <h4>{{translate('earning_statistics')}}</h4>
                                    <div
                                        class="position-relative index-2 d-flex flex-wrap gap-3 align-items-center justify-content-between">
                                        <ul class="option-select-btn">
                                            <li>
                                                <label>
                                                    <input type="radio" name="statistics" hidden checked>
                                                    <span>{{translate('yearly')}}</span>
                                                </label>
                                            </li>
                                        </ul>

                                        <div class="select-wrap d-flex flex-wrap gap-10">
                                            <select class="js-select update-chart">
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
                                <div id="apex_line-chart"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="card recent-transactions h-100">
                            <div class="card-body">
                                <h4 class="mb-3">{{translate('recent_transactions')}}</h4>
                                @if(isset($data[2]['recent_transactions']) && count($data[2]['recent_transactions']) > 0)
                                    <div class="d-flex align-items-center gap-3 mb-4">
                                        <img src="{{asset('assets/admin-module')}}/img/icons/arrow-up.png"
                                             alt="">
                                        <p class="opacity-75">{{$data[2]['this_month_trx_count']}} {{translate('transactions_this_month')}}</p>
                                    </div>
                                @endif
                                <div class="events">
                                    @foreach($data[2]['recent_transactions'] as $transaction)
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
                                    <div class="line"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-6">
                        <div class="card top-providers">
                            <div class="card-header d-flex justify-content-between gap-10">
                                <h5>{{translate('top_providers')}}</h5>
                                <a href="{{route('admin.provider.list')}}"
                                   class="btn-link">{{translate('view_all')}}</a>
                            </div>
                            <div class="card-body">
                                <ul class="common-list">
                                    @foreach($data[4]['top_providers'] as $provider)
                                        <li class="provider-redirect"
                                            data-route="{{route('admin.provider.details',[$provider->id])}}?web_page=overview">
                                            <div class="media gap-3">
                                                <div class="avatar avatar-lg">
                                                    <img class="avatar-img rounded-circle" src="{{ $provider->logo_full_path }}" alt="{{ translate('logo') }}">
                                                </div>
                                                <div class="media-body ">
                                                    <h5>{{\Illuminate\Support\Str::limit($provider->company_name,20)}}</h5>
                                                    <span class="common-list_rating d-flex gap-1">
                                                        <span class="material-icons">star</span>
                                                        {{$provider->avg_rating}}
                                                    </span>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5 col-sm-6">
                        <div class="card recent-activities">
                            <div class="card-header d-flex justify-content-between gap-10">
                                <h5>{{translate('recent_bookings')}}</h5>
                                <a href="{{route('admin.booking.list', ['booking_status'=>'pending'])}}"
                                   class="btn-link">{{translate('view_all')}}</a>
                            </div>
                            <div class="card-body">
                                <ul class="common-list">
                                    @foreach($data[3]['bookings'] as $booking)
                                        <li class="d-flex flex-wrap gap-2 align-items-center justify-content-between cursor-pointer recent-booking-redirect"
                                            data-route="{{route('admin.booking.details',[$booking->id])}}?web_page=details">
                                            <div class="media align-items-center gap-3">
                                                <div class="avatar avatar-lg">
                                                    <img class="avatar-img rounded"
                                                         src="{{ $booking->detail[0]->service?->thumbnail_full_path }}"
                                                         alt="{{ translate('provider-logo') }}">
                                                </div>
                                                <div class="media-body ">
                                                    <h5>{{translate('Booking')}}# {{$booking->readable_id}}</h5>
                                                    <p>{{date('d-m-Y, H:i a',strtotime($booking->created_at))}}</p>
                                                </div>
                                            </div>
                                            <span
                                                class="badge rounded-pill py-2 px-3 badge-primary text-capitalize">{{$booking->booking_status}}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="card top-providers">
                            <div class="card-header d-flex flex-column gap-10">
                                <h5>{{translate('booking_statistics')}} - {{date('M, Y')}}</h5>
                            </div>
                            <div class="card-body booking-statistics-info">
                                @if(isset($data[5]['zone_wise_bookings']))
                                    <ul class="common-list after-none gap-10 d-flex flex-column">
                                        @foreach($data[5]['zone_wise_bookings'] as $booking)
                                            <li>
                                                <div
                                                    class="mb-2 d-flex align-items-center justify-content-between gap-10 flex-wrap">
                                                    <span
                                                        class="zone-name">{{$booking->zone?$booking->zone->name:translate('zone_not_available')}}</span>
                                                    <span
                                                        class="booking-count">{{$booking->total}} {{translate('bookings')}}</span>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar" role="progressbar"
                                                         style="width: {{$booking->total}}%"
                                                         aria-valuenow="{{$booking->total}}" aria-valuemin="0"
                                                         aria-valuemax="100"></div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="d-flex align-items-center justify-content-center h-100">
                                        <span class="opacity-50">{{translate('No Bookings Found')}}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="text-center">
                                    {{translate('welcome_to_admin_panel')}}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @else
        <div class="main-content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center">
                            <h4>{{translate('This page was not authorized by the administrator for you.')}}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection


@push('script')
    <script src="{{asset('assets/admin-module')}}/plugins/apex/apexcharts.min.js"></script>
    <script>
        'use strict';

        $('.js-select.update-chart').on('change', function() {
            var selectedYear = $(this).val();
            localStorage.setItem('selectedYear', selectedYear); // Store the selected year in local storage
            update_chart(selectedYear);
        });

        // On page load, check if a year is stored in local storage
        $(document).ready(function() {
            var storedYear = localStorage.getItem('selectedYear');
            if (storedYear) {
                $('.js-select.update-chart').val(storedYear); // Set the select to the stored year
                update_chart(storedYear); // Update the chart with the stored year
            }
        });

        var options = {
            series: [
                {
                    name: "{{translate('total_earnings')}}",
                    data: @json($chart_data['total_earning'])
                },
                {
                    name: "{{translate('admin_commission')}}",
                    data: @json($chart_data['commission_earning'])
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
                        return Math.abs(value)
                    }
                },
            },
            colors: ['#4FA7FF', '#82C662'],
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
                floating: false,
                offsetY: -10,
                offsetX: 0,
                itemMargin: {
                    horizontal: 10,
                    vertical: 10
                },
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
            var url = '{{route('admin.update-dashboard-earning-graph')}}?year=' + year;

            $.getJSON(url, function (response) {
                chart.updateSeries([{
                    name: "{{translate('total_earning')}}",
                    data: response.total_earning
                }, {
                    name: "{{translate('admin_commission')}}",
                    data: response.commission_earning
                }])
            });
        }


        $(".provider-redirect").on('click', function(){
            location.href = $(this).data('route');
        });

        $(".recent-booking-redirect").on('click', function(){
            location.href = $(this).data('route');
        });
    </script>
@endpush
