@extends('adminmodule::layouts.master')

@section('title',translate('Earning_Report'))

@push('css_or_js')
    <style>
        .apexcharts-series:not(:last-child) {opacity: 0;}
    </style>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{translate('Business_Reports')}}</h2>
            </div>

            <div class="mb-4">
                <ul class="nav nav--tabs nav--tabs__style2">
                    <li class="nav-item">
                        <a href="{{route('admin.report.business.overview')}}"
                           class="nav-link">{{translate('Overview')}}</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('admin.report.business.earning')}}"
                           class="nav-link active">{{translate('Earning_Report')}}</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('admin.report.business.expense')}}"
                           class="nav-link">{{translate('Expense_Report')}}</a>
                    </li>
                </ul>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="mb-3 fz-16">{{translate('Search_Data')}}</div>

                    <form action="{{route('admin.report.business.earning')}}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-lg-4 col-sm-6 mb-30">
                                <label class="mb-2">{{translate('zone')}}</label>
                                <select class="js-select zone__select" name="zone_ids[]" id="zone_selector__select"
                                        multiple>
                                    <option value="all">{{translate('Select All')}}</option>
                                    @foreach($zones as $zone)
                                        <option
                                            value="{{$zone['id']}}" {{array_key_exists('zone_ids', $queryParams) && in_array($zone['id'], $queryParams['zone_ids']) ? 'selected' : '' }}>{{$zone['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-4 col-sm-6 mb-30">
                                <label class="mb-2">{{translate('category')}}</label>
                                <select class="js-select category__select" name="category_ids[]"
                                        id="category_selector__select" multiple>
                                    <option value="all">{{translate('Select All')}}</option>
                                    @foreach($categories as $category)
                                        <option
                                            value="{{$category['id']}}" {{array_key_exists('category_ids', $queryParams) && in_array($category['id'], $queryParams['category_ids']) ? 'selected' : '' }}>{{$category['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-4 col-sm-6 mb-30">
                                <label class="mb-2">{{translate('sub_category')}}</label>
                                <select class="js-select sub-category__select" name="sub_category_ids[]"
                                        id="sub_category_selector__select"
                                        multiple>
                                    <option value="all">{{translate('Select All')}}</option>
                                    @foreach($sub_categories as $sub_category)
                                        <option
                                            value="{{$sub_category['id']}}" {{array_key_exists('sub_category_ids', $queryParams) && in_array($sub_category['id'], $queryParams['sub_category_ids']) ? 'selected' : '' }}>{{$sub_category['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-4 col-sm-6 mb-30">
                                <label class="mb-2">{{translate('date_range')}}</label>
                                <select class="js-select" id="date-range" name="date_range">
                                    <option value="0" disabled selected>{{translate('Date_Range')}}</option>
                                    <option
                                        value="all_time" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='all_time'?'selected':''}}>{{translate('All_Time')}}</option>
                                    <option
                                        value="this_week" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='this_week'?'selected':''}}>{{translate('This_Week')}}</option>
                                    <option
                                        value="last_week" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='last_week'?'selected':''}}>{{translate('Last_Week')}}</option>
                                    <option
                                        value="this_month" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='this_month'?'selected':''}}>{{translate('This_Month')}}</option>
                                    <option
                                        value="last_month" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='last_month'?'selected':''}}>{{translate('Last_Month')}}</option>
                                    <option
                                        value="last_15_days" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='last_15_days'?'selected':''}}>{{translate('Last_15_Days')}}</option>
                                    <option
                                        value="this_year" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='this_year'?'selected':''}}>{{translate('This_Year')}}</option>
                                    <option
                                        value="last_year" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='last_year'?'selected':''}}>{{translate('Last_Year')}}</option>
                                    <option
                                        value="last_6_month" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='last_6_month'?'selected':''}}>{{translate('Last_6_Month')}}</option>
                                    <option
                                        value="this_year_1st_quarter" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='this_year_1st_quarter'?'selected':''}}>{{translate('This_Year_1st_Quarter')}}</option>
                                    <option
                                        value="this_year_2nd_quarter" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='this_year_2nd_quarter'?'selected':''}}>{{translate('This_Year_2nd_Quarter')}}</option>
                                    <option
                                        value="this_year_3rd_quarter" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='this_year_3rd_quarter'?'selected':''}}>{{translate('This_Year_3rd_Quarter')}}</option>
                                    <option
                                        value="this_year_4th_quarter" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='this_year_4th_quarter'?'selected':''}}>{{translate('this_year_4th_quarter')}}</option>
                                    <option
                                        value="custom_date" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='custom_date'?'selected':''}}>{{translate('Custom_Date')}}</option>
                                </select>
                            </div>
                            <div
                                class="col-lg-4 col-sm-6 {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='custom_date'?'':'d-none'}} align-self-end"
                                id="from-filter__div">
                                <div class="form-floating mb-30">
                                    <input type="date" class="form-control" id="from" name="from"
                                           value="{{array_key_exists('from', $queryParams)?$queryParams['from']:''}}">
                                    <label for="from">{{translate('From')}}</label>
                                </div>
                            </div>
                            <div
                                class="col-lg-4 col-sm-6 {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='custom_date'?'':'d-none'}} align-self-end"
                                id="to-filter__div">
                                <div class="form-floating mb-30">
                                    <input type="date" class="form-control" id="to" name="to"
                                           value="{{array_key_exists('to', $queryParams)?$queryParams['to']:''}}">
                                    <label for="to">{{translate('To')}}</label>
                                </div>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit"
                                        class="btn btn--primary btn-sm">{{translate('Filter')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row pt-3 g-3">
                <div class="col-lg-4">
                    <div class="d-flex flex-column gap-3">
                        <div class="card flex-row gap-4 p-30">
                            <div class="d-flex gap-4 flex-wrap">
                                <img width="35" class="avatar" src="{{asset('public/assets/admin-module')}}/img/icons/earning-total.png" alt="">
                                <div>
                                    <h2 class="fz-26">{{with_currency_symbol(array_sum($chart_data['total_earning']))}}</h2>
                                    <div>
                                        <span class="fz-12 text-capitalize">{{translate('total_earning')}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card flex-row align-items-center gap-4 p-30">
                            <div class="d-flex gap-4 flex-wrap">
                                <img width="35" class="avatar"
                                     src="{{asset('public/assets/admin-module')}}/img/icons/com-earning.png"
                                     alt="">
                                <div>
                                    <h2 class="fz-26">{{with_currency_symbol(array_sum($chart_data['commission_earning']))}}</h2>
                                    <span class="fz-12">{{translate('Commission_Earnings')}}</span>
                                </div>
                            </div>
                            <div class="ms-auto">
                                <a href="{{route('admin.report.business.commission-earning')}}" class="btn px-2 py-1 btn-outline-primary">
                                    <span class="py-1">{{translate('Details')}}</span>
                                </a>
                            </div>
                        </div>

                        <div class="card flex-row align-items-center gap-4 p-30">
                            <div class="d-flex gap-4 flex-wrap">
                                <img width="35" class="avatar"
                                     src="{{asset('public/assets/admin-module')}}/img/icons/sub-ear.png"
                                     alt="">
                                <div>
                                    <h2 class="fz-26">{{with_currency_symbol(array_sum($chart_data['subscription_earning']))}}</h2>
                                    <span class="fz-12">{{translate('Subscription Earnings')}}</span>
                                </div>
                            </div>
                            <div class="ms-auto">
                                <a href="{{route('admin.report.business.subscription-earning')}}" class="btn px-2 py-1 btn-outline-primary">
                                    <span class="py-1">{{translate('Details')}}</span>
                                </a>
                            </div>
                        </div>

                        <div class="card flex-row gap-4 p-30">
                            <div class="d-flex gap-4 flex-wrap">
                                <img width="35" class="avatar"
                                     src="{{asset('public/assets/admin-module')}}/img/icons/plat-ear.png" alt="">
                                <div>
                                    <h2 class="fz-26">{{with_currency_symbol(array_sum($chart_data['platform_fee']))}}</h2>
                                    <span class="fz-12">{{translate('platform_fee')}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card h-100">
                        <div class="card-body ps-0">
                            <h4 class="ps-20 mb-xl-4">{{translate('Earning_Statistics')}}</h4>
                            <div id="apex_line-chart"></div>
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
                                       placeholder="{{translate('search_by_Booking_ID')}}">
                            </div>
                            <button type="submit"
                                    class="btn btn--primary">{{translate('search')}}</button>
                        </form>
                        @can('report_export')
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <div class="dropdown">
                                    <button type="button"
                                            class="btn btn--secondary text-capitalize dropdown-toggle"
                                            data-bs-toggle="dropdown">
                                        <span class="material-icons">file_download</span> {{translate('download')}}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                        <li><a class="dropdown-item"
                                               href="{{route('admin.report.business.earning.download').'?'.http_build_query($queryParams)}}">{{translate('Excel')}}</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        @endcan
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="align-middle">
                            <tr>
                                <th>{{translate('Booking_ID')}}</th>
                                <th>{{translate('Booking_Amount')}}</th>

                                <th>{{translate('Total_Service_Discount')}}</th>
                                <th style="min-width: 150px!important;">{{translate('Discount_on_service_by_admin')}}</th>
                                <th style="min-width: 150px!important;">{{translate('Discount_on_service_by_provider')}}</th>
                                <th>{{translate('Total_Coupon_Discount')}}</th>
                                <th style="min-width: 150px!important;">{{translate('Coupon_Discount_on_service_by_admin')}}</th>
                                <th style="min-width: 150px!important;">{{translate('Coupon_Discount_on_service_by_provider')}}</th>
                                <th>{{translate('Total_Campaign_Discount')}}</th>
                                <th style="min-width: 150px!important;">{{translate('Campaign_Discount_on_service_by_admin')}}</th>
                                <th style="min-width: 150px!important;">{{translate('Campaign_Discount_on_service_by_provider')}}</th>

                                <th>{{translate('Subtotal')}}</th>
                                <th>{{translate('VAT_/_Tax')}}</th>
                                <th>{{translate('Admin_Commission')}}</th>
                                <th style="min-width: 150px!important;">{{translate('Provider_Net_Income')}}
                                    <span class="material-icons" data-bs-toggle="tooltip"
                                          data-bs-placement="bottom"
                                          title="{{translate('Provider net income is the amount that come from booking earning (after giving promotional cost)')}}"
                                    >info</span>
                                </th>
                                <th style="min-width: 150px!important;">{{translate('Admin_Net_Income')}}
                                    <span class="material-icons" data-bs-toggle="tooltip"
                                          data-bs-placement="bottom"
                                          title="{{translate('Admin net income is the amount that come from booking commission (after giving promotional cost)')}}"
                                    >info</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($bookings as $key=>$booking)
                                    <?php
                                    $admin_commission_without_earning = 0;

                                    $discount_by_admin = 0;
                                    $discount_by_provider = 0;
                                    $coupon_discount_by_admin = 0;
                                    $coupon_discount_by_provider = 0;
                                    $campaign_discount_by_admin = 0;
                                    $campaign_discount_by_provider = 0;

                                    $admin_commission_with_cost = 0;

                                    $admin_net_income = 0;
                                    $provider_net_income = 0;

                                    foreach ($booking->details_amounts as $key => $item) {
                                        $discount_by_admin += $item['discount_by_admin'];
                                        $discount_by_provider += $item['discount_by_provider'];
                                        $coupon_discount_by_admin += $item['coupon_discount_by_admin'];
                                        $coupon_discount_by_provider += $item['coupon_discount_by_provider'];
                                        $campaign_discount_by_admin += $item['campaign_discount_by_admin'];
                                        $campaign_discount_by_provider += $item['campaign_discount_by_provider'];

                                        $admin_commission_with_cost += $item->admin_commission;

                                    }

                                    $admin_commission_without_cost = $admin_commission_with_cost - ($discount_by_admin + $coupon_discount_by_admin + $campaign_discount_by_admin);
                                    $admin_net_income = $admin_commission_without_cost;
                                    $provider_net_income = $booking['total_booking_amount'] - $admin_commission_without_cost;
                                    ?>
                                <tr>
                                    <td>
                                        <a href="{{route('admin.booking.details', [$booking->id,'web_page'=>'details'])}}">
                                            {{$booking['readable_id']}}</a>
                                    </td>
                                    <td>{{with_currency_symbol($booking['total_booking_amount'])}}</td>

                                    <td>{{with_currency_symbol($booking['total_discount_amount'])}}</td>
                                    <td>{{with_currency_symbol($discount_by_admin)}}</td>
                                    <td>{{with_currency_symbol($discount_by_provider)}}</td>
                                    <td>{{with_currency_symbol($booking['total_coupon_discount_amount'])}}</td>
                                    <td>{{with_currency_symbol($coupon_discount_by_admin)}}</td>
                                    <td>{{with_currency_symbol($coupon_discount_by_provider)}}</td>
                                    <td>{{with_currency_symbol($booking['total_campaign_discount_amount'])}}</td>
                                    <td>{{with_currency_symbol($campaign_discount_by_admin)}}</td>
                                    <td>{{with_currency_symbol($campaign_discount_by_provider)}}</td>

                                    <td>{{with_currency_symbol($booking['total_booking_amount'])}}</td>
                                    <td>{{with_currency_symbol($booking['total_tax_amount'])}}</td>
                                    <td>{{with_currency_symbol($admin_commission_with_cost)}}</td>
                                    <td>{{with_currency_symbol($provider_net_income)}}</td>
                                    <td>{{with_currency_symbol($admin_net_income)}}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-center"
                                        colspan="18">{{translate('Data_not_available')}}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end">
                        {!! $bookings->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="formulaModal" tabindex="-1" aria-labelledby="formulaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="py-4">
                        <h4 class="fw-regular">Total Earning</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')

    <script src="{{asset('public/assets/admin-module')}}/plugins/apex/apexcharts.min.js"></script>

    <script>
        "use strict";

        $('#zone_selector__select').on('change', function () {
            var selectedValues = $(this).val();
            if (selectedValues !== null && selectedValues.includes('all')) {
                $(this).find('option').not(':disabled').prop('selected', 'selected');
                $(this).find('option[value="all"]').prop('selected', false);
            }
        });

        $('#category_selector__select').on('change', function () {
            var selectedValues = $(this).val();
            if (selectedValues !== null && selectedValues.includes('all')) {
                $(this).find('option').not(':disabled').prop('selected', 'selected');
                $(this).find('option[value="all"]').prop('selected', false);
            }
        });

        $('#sub_category_selector__select').on('change', function () {
            var selectedValues = $(this).val();
            if (selectedValues !== null && selectedValues.includes('all')) {
                $(this).find('option').not(':disabled').prop('selected', 'selected');
                $(this).find('option[value="all"]').prop('selected', false);
            }
        });

        $(document).ready(function () {
            $('.zone__select').select2({
                placeholder: "{{translate('Select_zone')}}",
            });
            $('.category__select').select2({
                placeholder: "{{translate('Select_category')}}",
            });
            $('.sub-category__select').select2({
                placeholder: "{{translate('Select_sub_category')}}",
            });

            $('#date-range').on('change', function () {
                if (this.value === 'custom_date') {
                    $('#from-filter__div').removeClass('d-none');
                    $('#to-filter__div').removeClass('d-none');
                }

                if (this.value !== 'custom_date') {
                    $('#from-filter__div').addClass('d-none');
                    $('#to-filter__div').addClass('d-none');
                }
            });
        });

        var options = {
            series: [
                {
                    name: "{{translate('total_earning')}}",
                    data: {{json_encode($chart_data['total_earning'])}}
                },
                {
                    name: "{{translate('commission_earning')}}",
                    data: {{json_encode(array_merge($chart_data['commission_earning']))}}
                },
                {
                    name: "{{translate('subscription_earning')}}",
                    data: {{json_encode(array_merge($chart_data['subscription_earning']))}}
                },
                {
                    name: "{{translate('platform_fee')}}",
                    data: {{json_encode(array_merge($chart_data['platform_fee']))}}
                }
            ],
            chart: {
                height: 390,
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
            colors: ['#4153b3', '#d6e8a7', '#67CA93', '#A7C9E8'],
            dataLabels: {
                enabled: true,
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
                categories: {{json_encode($chart_data['timeline'])}}
            },
            legend: {
                position: 'top',
                horizontalAlign: 'center',
                floating: true,
                offsetY: 0,
                offsetX: 0
            },
            padding: {
                top: 0,
                right: 0,
                bottom: 200,
                left: 10
            },
        };

        var chart = new ApexCharts(document.querySelector("#apex_line-chart"), options);
        chart.render();
    </script>
@endpush
