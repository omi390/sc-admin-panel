@extends('adminmodule::layouts.master')

@section('title',translate('Earning_Report'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('/public/assets/admin-module/plugins/swiper/swiper-bundle.min.css')}}">
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{translate('Earning Details')}}</h2>
            </div>

            <div class="mb-4">
                <ul class="nav nav--tabs nav--tabs__style2">
                    <li class="nav-item">
                        <a href="{{route('admin.report.business.subscription-earning')}}"
                           class="nav-link">{{translate('Subscription Earning')}}</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('admin.report.business.commission-earning')}}"
                           class="nav-link active">{{translate('Commission Earning')}}</a>
                    </li>
                </ul>
            </div>

            <div class="row g-4">
                <div class="col-lg-7 col-xl-8 col-xxl-9">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <h4>{{translate('Earning Statistics')}}</h4>
                                <div>
                                    <h4 class="">
                                        <span class="c1 fw-semibold">{{ with_currency_symbol(array_sum($chartData)) }}</span>
                                        <small class="opacity-75 fw-normal">(Total Earning)</small>
                                    </h4>
                                </div>
                                <form id="barFilterForm" method="GET" action="{{url()->current()}}">
                                    <div class="select-wrap d-flex flex-wrap gap-10">
                                        <select class="js-select" name="bar_filter" onchange="document.getElementById('barFilterForm').submit();">
                                            <option value="1" {{ $barFilter == 1 ? 'selected' : '' }}>{{ translate('All Times') }}</option>
                                            <option value="2" {{ $barFilter == 2 ? 'selected' : '' }}>{{ translate('Last Month') }}</option>
                                            <option value="3" {{ $barFilter == 3 ? 'selected' : '' }}>{{ translate('This Year') }}</option>
                                            <option value="4" {{ $barFilter == 4 ? 'selected' : '' }}>{{ translate('Last Year') }}</option>
                                            <option value="5" {{ $barFilter == 5 ? 'selected' : '' }}>{{ translate('This Month') }}</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                            <div id="apex-bar-chart"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 col-xl-4 col-xxl-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                                <div>
                                    <h4>{{translate('Providers')}}</h4>
                                    <small>Based on activity</small>
                                </div>
                                <form id="filterForm" method="GET" action="{{url()->current()}}">
                                    <div class="select-wrap d-flex flex-wrap gap-10">
                                        <select class="js-select" name="filter" onchange="document.getElementById('filterForm').submit();">
                                            <option value="1" {{ $filter == 1 ? 'selected' : '' }}>{{ translate('All Times') }}</option>
                                            <option value="2" {{ $filter == 2 ? 'selected' : '' }}>{{ translate('Last Month') }}</option>
                                            <option value="3" {{ $filter == 3 ? 'selected' : '' }}>{{ translate('This Year') }}</option>
                                            <option value="4" {{ $filter == 4 ? 'selected' : '' }}>{{ translate('Last Year') }}</option>
                                            <option value="5" {{ $filter == 5 ? 'selected' : '' }}>{{ translate('This Month') }}</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                            <div class="position-relative">
                            <div class="total--subscriptions">
                                <h3 class="fw-bold mb-2">{{ $providers['active_provider'] +  $providers['inactive_provider']}}</h3>
                                <div>{{ translate('Commission')}} <br> {{ translate('Based Providers')}}</div>
                            </div>
                                <div id="apex-pie-chart" class="d-flex justify-content-center"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                        <form action="{{url()->current()}}"
                              class="search-form search-form_style-two"
                              method="GET">
                            <div class="input-group search-form__input_group">
                                    <span class="search-form__icon">
                                        <span class="material-icons">search</span>
                                    </span>
                                <input type="search" class="theme-input-style search-form__input"
                                       value="{{$search??''}}" name="search"
                                       placeholder="{{translate('Search the booking id')}}">
                            </div>
                            <button type="submit"
                                    class="btn btn--primary">{{translate('search')}}</button>
                        </form>
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <div class="dropdown">
                                <button type="button"
                                        class="btn btn--secondary text-capitalize dropdown-toggle"
                                        data-bs-toggle="dropdown">
                                    <span class="material-icons">file_download</span> {{translate('download')}}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                    <li><a class="dropdown-item" href="{{ route('admin.report.business.commission.download',['search'=>$search]) }}">{{translate('Excel')}}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="align-middle">
                            <tr>
                                <th>{{translate('SL')}}</th>
                                <th class="text-center">{{translate('Bokking ID')}}</th>
                                <th class="text-center">{{translate('Booking Date')}}</th>
                                <th class="text-end">{{translate('Booking Amount')}}</th>
                                <th class="text-end">{{translate('Commission')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($commissionEarningList as $key => $earning)
                                <tr>
                                    <td>{{$key+$commissionEarningList?->firstItem()}}</td>
                                    <td class="text-center"><a href="">{{ $earning->booking_id }}</a></td>
                                    <td class="text-center">{{ $earning->created_at }}</td>
                                    <td class="text-end">{{ with_currency_symbol($earning->booking->total_booking_amount) }}</td>
                                    <td class="text-end">{{ with_currency_symbol($earning->admin_commission) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-center"
                                        colspan="3">{{translate('Data_not_available')}}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end">
                        {!! $commissionEarningList->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')


    <script src="{{asset('public/assets/admin-module')}}/plugins/apex/apexcharts.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/swiper/swiper-bundle.min.js"></script>
    <script>
        "use strict"

        function pieChart(activeProviders, inactiveProviders) {
            const options = {
                labels: [
                    `Inactive Providers`,
                    `Active Providers`
                ],
                series: [inactiveProviders, activeProviders],
                colors: [ "#4153B3", "#F3C278"],
                chart: {
                    width: 425,
                    height: 250,
                    type: 'donut',
                },
                dataLabels: {
                    enabled: false,
                },
                responsive: [{
                    breakpoint: 1680,
                    options: {
                        chart: {
                            width: 260,
                            height: 210,
                        },
                    }
                },{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 300,
                            height: 200,
                        },
                    }
                }],
                legend: {
                    position: 'bottom',
                    offsetY: -5,
                    // height: 30,
                },
            };

            const chart = new ApexCharts(document.querySelector("#apex-pie-chart"), options);
            chart.render();
        }
        pieChart(<?php echo json_encode($providers['active_provider']); ?>, <?php echo json_encode($providers['inactive_provider']); ?>);

        function barChart(categories, chartData) {
            const colors = ["#4153B3"];
            const options2 = {
                series: [{
                    name : '{{ translate('total') }}',
                    data: chartData
                }],
                chart: {
                    height: 350,
                    type: 'bar',
                    toolbar: {
                        show: false
                    }
                },
                colors: colors,
                plotOptions: {
                    bar: {
                        columnWidth: '10px',
                        endingShape: 'rounded'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                legend: {
                    show: false
                },
                xaxis: {
                    categories: categories,
                    labels: {
                        style: {
                            fontSize: '12px'
                        }
                    }
                }
            };

            const chart2 = new ApexCharts(document.querySelector("#apex-bar-chart"), options2);
            chart2.render();
        }

        barChart(<?php echo json_encode($categories); ?>, <?php echo json_encode($chartData); ?>);

        function walletSlider() {
            const swiper = new Swiper('.wallet-slider', {
                slidesPerView: 'auto',
                spaceBetween: 20,
                freeMode: true,
                navigation: {
                    nextEl: '.swiper-next',
                    prevEl: '.swiper-prev',
                },
            });
        }
        walletSlider()

    </script>
@endpush
