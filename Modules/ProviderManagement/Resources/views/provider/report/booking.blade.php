@extends('providermanagement::layouts.master')

@section('title',translate('Booking_Report'))

@push('css_or_js')

@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Booking_Reports')}}</h2>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3 fz-16 fw-bold text-dark">{{translate('Search_Data')}}</div>

                            <form action="{{route('provider.report.booking')}}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-4 col-sm-6 mb-30">
                                        <label class="mb-2">{{translate('zone')}}</label>
                                        <select class="js-select zone__select" name="zone_ids[]" multiple>
                                            @foreach($zones as $zone)
                                                <option value="{{$zone['id']}}" {{array_key_exists('zone_ids', $queryParams) && in_array($zone['id'], $queryParams['zone_ids']) ? 'selected' : '' }}>{{$zone['name']}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-4 col-sm-6 mb-30">
                                        <label class="mb-2">{{translate('category')}}</label>
                                        <select class="js-select category__select" name="category_ids[]" multiple>
                                            @foreach($categories as $category)
                                                <option value="{{$category['id']}}" {{array_key_exists('category_ids', $queryParams) && in_array($category['id'], $queryParams['category_ids']) ? 'selected' : '' }}>{{$category['name']}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-4 col-sm-6 mb-30">
                                        <label class="mb-2">{{translate('sub_category')}}</label>
                                        <select class="js-select sub-category__select" name="sub_category_ids[]" multiple>
                                            @foreach($subCategories as $subCategory)
                                                <option value="{{$subCategory['id']}}" {{array_key_exists('sub_category_ids', $queryParams) && in_array($subCategory['id'], $queryParams['sub_category_ids']) ? 'selected' : '' }}>{{$subCategory['name']}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-4 col-sm-6 mb-30">
                                        <label class="mb-2">{{translate('date_range')}}</label>
                                        <select class="js-select" id="date-range" name="date_range">
                                            <option value="0" disabled selected>{{translate('Date_Range')}}</option>
                                            <option value="all_time" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='all_time'?'selected':''}}>{{translate('All_Time')}}</option>
                                            <option value="this_week" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='this_week'?'selected':''}}>{{translate('This_Week')}}</option>
                                            <option value="last_week" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='last_week'?'selected':''}}>{{translate('Last_Week')}}</option>
                                            <option value="this_month" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='this_month'?'selected':''}}>{{translate('This_Month')}}</option>
                                            <option value="last_month" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='last_month'?'selected':''}}>{{translate('Last_Month')}}</option>
                                            <option value="last_15_days" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='last_15_days'?'selected':''}}>{{translate('Last_15_Days')}}</option>
                                            <option value="this_year" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='this_year'?'selected':''}}>{{translate('This_Year')}}</option>
                                            <option value="last_year" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='last_year'?'selected':''}}>{{translate('Last_Year')}}</option>
                                            <option value="last_6_month" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='last_6_month'?'selected':''}}>{{translate('Last_6_Month')}}</option>
                                            <option value="this_year_1st_quarter" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='this_year_1st_quarter'?'selected':''}}>{{translate('This_Year_1st_Quarter')}}</option>
                                            <option value="this_year_2nd_quarter" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='this_year_2nd_quarter'?'selected':''}}>{{translate('This_Year_2nd_Quarter')}}</option>
                                            <option value="this_year_3rd_quarter" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='this_year_3rd_quarter'?'selected':''}}>{{translate('This_Year_3rd_Quarter')}}</option>
                                            <option value="this_year_4th_quarter" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='this_year_4th_quarter'?'selected':''}}>{{translate('this_year_4th_quarter')}}</option>
                                            <option value="custom_date" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='custom_date'?'selected':''}}>{{translate('Custom_Date')}}</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-4 col-sm-6 {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='custom_date'?'':'d-none'}} align-self-end" id="from-filter__div">
                                        <div class="form-floating mb-30">
                                            <input type="date" class="form-control" id="from" name="from" value="{{array_key_exists('from', $queryParams)?$queryParams['from']:''}}">
                                            <label for="from">{{translate('From')}}</label>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6 {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='custom_date'?'':'d-none'}} align-self-end" id="to-filter__div">
                                        <div class="form-floating mb-30">
                                            <input type="date" class="form-control" id="to" name="to" value="{{array_key_exists('to', $queryParams)?$queryParams['to']:''}}">
                                            <label for="to">{{translate('To')}}</label>
                                        </div>
                                    </div>
                                    <div class="col-12 gap-3 d-flex justify-content-end">
                                        <button type="reset" class="btn btn--secondary btn-sm">{{translate('Reset')}}</button>
                                        <button type="submit" class="btn btn--primary btn-sm">{{translate('Submit')}}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="row g-2 pt-2">
                        <div class="col-xl-3">
                            <div class="d-flex flex-wrap gap-2">
                                <div class="card p-30 flex-grow-1">
                                    <div class="d-flex gap-2 justify-content-center align-items-center flex-wrap">
                                        <img width="35" class="avatar" src="{{asset('public/assets/admin-module')}}/img/icons/total_booking.png" alt="">
                                        <div class="">
                                            <h2 class="fz-26">{{$bookingsCount['total_bookings']}}</h2>
                                            <span class="fz-12">{{translate('Total_Bookings')}}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap justify-content-between gap-2 mt-30">
                                        <div class="d-flex flex-column align-items-center gap-2 fz-12">
                                            <span class="fw-semibold text-danger">{{$bookingsCount['canceled']}}</span>
                                            <span class="opacity-50">{{translate('Canceled')}}</span>
                                        </div>
                                        <div class="d-flex flex-column align-items-center gap-2 fz-12">
                                            <span class="fw-semibold text-success">{{$bookingsCount['accepted']}}</span>
                                            <span class="opacity-50">{{translate('Accepted')}}</span>
                                        </div>
                                        <div class="d-flex flex-column align-items-center gap-2 fz-12">
                                            <span class="c1 fw-semibold">{{$bookingsCount['ongoing']}}</span>
                                            <span class="opacity-50">{{translate('On_Going')}}</span>
                                        </div>
                                        <div class="d-flex flex-column align-items-center gap-2 fz-12">
                                            <span class="fw-semibold text-success">{{$bookingsCount['completed']}}</span>
                                            <span class="opacity-50">{{translate('Completed')}}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="card p-30 flex-grow-1">
                                    <div class="d-flex gap-2 align-items-center justify-content-center flex-wrap">
                                        <img width="35" class="avatar" src="{{asset('public/assets/admin-module')}}/img/icons/booking_amount.png" alt="">
                                        <div class="">
                                            <h2 class="fz-26">{{with_currency_symbol($bookingAmount['total_booking_amount'])}}</h2>
                                            <span class="fz-12">{{translate('Total_Booking_Amount')}}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap justify-content-between gap-2 mt-30">
                                        <div class="d-flex flex-column align-items-center gap-2 fz-12">
                                            <span class="text-danger fw-semibold">{{with_currency_symbol($bookingAmount['total_unpaid_booking_amount'])}}</span>
                                            <span class="opacity-50 gap-1 d-flex align-items-center">{{translate('Due_Amount')}}
                                                <i class="material-symbols-outlined" data-bs-toggle="tooltip" data-bs-placement="top"
                                                   title="{{translate('Digitally paid but yet to disburse the amount')}}"
                                                >info</i>
                                            </span>
                                        </div>
                                        <div class="d-flex flex-column align-items-center gap-2 fz-12">
                                            <span class="text-success fw-semibold">{{with_currency_symbol($bookingAmount['total_paid_booking_amount'])}}</span>
                                            <span class="opacity-50 gap-1 d-flex align-items-center">{{translate('Already_Settled')}}
                                                <i class="material-symbols-outlined" data-bs-toggle="tooltip" data-bs-placement="top"
                                                   title="{{translate('Digitally paid & already disbursed the amount')}}"
                                                >info</i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-9">
                            <div class="card">
                                <div class="card-body ps-0">
                                    <h4 class="ps-20">{{translate('Booking_Statistics')}}</h4>
                                    <div id="apex_column-chart"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-2">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                <form action="{{url()->current()}}" class="search-form search-form_style-two" method="GET">
                                    <div class="input-group search-form__input_group">
                                        <span class="search-form__icon">
                                            <span class="material-icons">search</span>
                                        </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                               value="{{array_key_exists('search', $queryParams)?$queryParams['search']:''}}" name="search"
                                               placeholder="{{translate('search_by_Booking_ID')}}">
                                    </div>
                                    <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                                </form>

                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <div>
                                        <select class="js-select booking-status__select" name="booking_status" id="booking-status">
                                            <option value="" selected disabled>{{translate('Booking_status')}}</option>
                                            <option value="all">{{translate('All')}}</option>
                                            @foreach(BOOKING_STATUSES as $booking_status)
                                                @if($booking_status['key'] != 'pending')
                                                    <option value="{{$booking_status['key']}}" {{ $booking_status['key'] == $queryParams['booking_status'] ? 'selected' : '' }}>{{$booking_status['value']}}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="dropdown">
                                        <button type="button"
                                                class="btn btn--secondary text-capitalize dropdown-toggle"
                                                data-bs-toggle="dropdown">
                                            <span class="material-icons">file_download</span> {{translate('download')}}
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                            <li>
                                                <a class="dropdown-item" href="{{route('provider.report.booking.download').'?'.http_build_query($queryParams)}}">
                                                    {{translate('Excel')}}
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="text-nowrap">
                                    <tr>
                                        <th>{{translate('SL')}}</th>
                                        <th>{{translate('Booking_ID')}}</th>
                                        <th>{{translate('Customer_Info')}}</th>
                                        <th>{{translate('Booking_Amount')}}</th>
                                        <th>{{translate('Service_Discount')}}</th>
                                        <th>{{translate('Coupon_Discount')}}</th>
                                        <th>{{translate('VAT_/_Tax')}}</th>
                                        <th class="text-center">{{translate('Action')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($filteredBookings as $key=>$booking)
                                        <tr>
                                            <td>{{ $filteredBookings->firstitem()+$key }}</td>
                                            <td>
                                                <a href="{{route('provider.booking.details', [$booking->id,'web_page'=>'details'])}}">
                                                    {{$booking['readable_id']}}
                                                </a>
                                            </td>
                                            <td>
                                                @if(isset($booking->customer))
                                                    <div>{{$booking->customer->first_name . ' ' . $booking->customer->last_name}}</div>
                                                    <a class="fz-12" href="tel:{{$booking->customer->phone??''}}">{{$booking->customer->phone??''}}</a>
                                                @elseif($booking->is_guest == 1)
                                                    @php($customer_name = $booking?->service_address?->contact_person_name)
                                                    @php($customer_phone = $booking?->service_address?->contact_person_number)

                                                    <span class="fw-medium badge badge badge-danger radius-50">{{translate('Guest Customer')}}
                                                    </span>
                                                    <p class="fz-12">{{$customer_name}} {{$customer_phone}}</p>
                                                @endif
                                            </td>
                                            <td>{{with_currency_symbol($booking['total_booking_amount'])}}</td>
                                            <td>
                                                @if($booking['total_campaign_discount_amount'] > $booking['total_discount_amount'])
                                                    <p class="mb-1">{{with_currency_symbol($booking['total_campaign_discount_amount'])}}</p>
                                                    <span class="fw-medium badge badge badge-info radius-50">{{translate('Campaign')}}</span>
                                                @else
                                                    {{with_currency_symbol($booking['total_discount_amount'])}}
                                                @endif
                                            </td>
                                            <td>{{with_currency_symbol($booking['total_coupon_discount_amount'])}}</td>
                                            <td>{{with_currency_symbol($booking['total_tax_amount'])}}</td>
                                            <td>
                                                <div class="d-flex justify-content-center">
                                                    <a href="{{route('provider.booking.details', [$booking->id,'web_page'=>'details'])}}"
                                                        class="btn btn--light-primary action-btn"><span class="material-icons m-0">visibility</span>
                                                     </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td class="text-center" colspan="9">{{translate('Data_not_available')}}</td></tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                {!! $filteredBookings->links() !!}
                            </div>
                        </div>
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

        $(document).ready(function () {
            $('.zone__select').select2({
                placeholder: "{{translate('Select_zone')}}",
            });
            $('.provider__select').select2({
                placeholder: "{{translate('Select_provider')}}",
            });
            $('.category__select').select2({
                placeholder: "{{translate('Select_category')}}",
            });
            $('.sub-category__select').select2({
                placeholder: "{{translate('Select_sub_category')}}",
            });
            $('.booking-status__select').select2({
                placeholder: "{{translate('Booking_status')}}",
            });
        });

        $(document).ready(function () {
            $('#date-range').on('change', function() {
                //show 'from' & 'to' div
                if(this.value === 'custom_date') {
                    $('#from-filter__div').removeClass('d-none');
                    $('#to-filter__div').removeClass('d-none');
                }

                //hide 'from' & 'to' div
                if(this.value !== 'custom_date') {
                    $('#from-filter__div').addClass('d-none');
                    $('#to-filter__div').addClass('d-none');
                }
            });
        });

        $(document).ready(function () {
            $('#booking-status').on('change', function() {
                location.href = "{{route('provider.report.booking')}}" + "?booking_status=" + this.value;
            });
        });

        var options = {
            series: [{
                name: '{{translate('Total_Booking')}}',
                data: {{json_encode($chartData['booking_amount'])}}
            }, {
                name: '{{translate('Commission')}}',
                data: {{json_encode($chartData['admin_commission'])}}
            }, {
                name: '{{translate('VAT_/_Tax')}}',
                data: {{json_encode($chartData['tax_amount'])}}
            }],
            chart: {
                type: 'bar',
                height: 299
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '12px',
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: {{json_encode($chartData['timeline'])}},
            },
            yaxis: {
                title: {
                    text: '{{currency_symbol()}}'
                }
            },
            fill: {
                opacity: 1
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return " " + val + " "
                    }
                }
            },
            legend: {
                show: false
            },
        };

        var chart = new ApexCharts(document.querySelector("#apex_column-chart"), options);
        chart.render();
    </script>
@endpush
