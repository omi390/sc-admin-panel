@extends('providermanagement::layouts.master')

@section('title',translate('Serviceman_Details'))

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-wrap mb-3">
                    <h2 class="page-title">{{translate('Profile_Details')}}</h2>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 mb-30 mb-lg-0">
                        <div class="d-flex flex-column gap-2">
                            <div class="statistics-serviceman statistics-serviceman__total-assigned">
                                <h2>{{$totalAssignedBookings}}</h2>
                                <h3>{{translate('Assigned_Bookings')}}</h3>
                            </div>

                            <div class="statistics-serviceman statistics-serviceman__ongoing-booking">
                                <h2>{{$serviceman['total_ongoing_bookings']}}</h2>
                                <h3>{{translate('Ongoing_Bookings')}}</h3>
                            </div>

                            <div class="statistics-serviceman statistics-serviceman__completed-booking">
                                <h2>{{$serviceman['total_completed_bookings']}}</h2>
                                <h3>{{translate('Completed_Bookings')}}</h3>
                            </div>

                            <div class="statistics-serviceman statistics-serviceman__total-cancelled">
                                <h2>{{$serviceman['total_canceled_bookings']}}</h2>
                                <h3>{{translate('Canceled_Bookings')}}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-9">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-2">
                            <h4 class="c1">{{translate('Booking_History')}}</h4>
                            <select class="js-select form-control max-w320" id="date-range" name="date_range">
                                <option value="0" disabled {{ $dateRange == '0' ? 'selected' : '' }}>{{ translate('Date_Range') }}</option>
                                <option value="all_time" {{ $dateRange == 'all_time' ? 'selected' : '' }}>{{ translate('All_Time') }}</option>
                                <option value="this_month" {{ $dateRange == 'this_month' ? 'selected' : '' }}>{{ translate('This_Month') }}</option>
                                <option value="last_month" {{ $dateRange == 'last_month' ? 'selected' : '' }}>{{ translate('Last_Month') }}</option>
                                <option value="last_15_days" {{ $dateRange == 'last_15_days' ? 'selected' : '' }}>{{ translate('Last_15_Days') }}</option>
                                <option value="this_year" {{ $dateRange == 'this_year' ? 'selected' : '' }}>{{ translate('This_Year') }}</option>
                                <option value="last_year" {{ $dateRange == 'last_year' ? 'selected' : '' }}>{{ translate('Last_Year') }}</option>
                                <option value="last_6_month" {{ $dateRange == 'last_6_month' ? 'selected' : '' }}>{{ translate('Last_6_Month') }}</option>
                                <option value="this_year_1st_quarter" {{ $dateRange == 'this_year_1st_quarter' ? 'selected' : '' }}>{{ translate('This_Year_1st_Quarter') }}</option>
                                <option value="this_year_2nd_quarter" {{ $dateRange == 'this_year_2nd_quarter' ? 'selected' : '' }}>{{ translate('This_Year_2nd_Quarter') }}</option>
                                <option value="this_year_3rd_quarter" {{ $dateRange == 'this_year_3rd_quarter' ? 'selected' : '' }}>{{ translate('This_Year_3rd_Quarter') }}</option>
                                <option value="this_year_4th_quarter" {{ $dateRange == 'this_year_4th_quarter' ? 'selected' : '' }}>{{ translate('This_Year_4th_Quarter') }}</option>

                            </select>
                        </div>

                        <div class="card dark-support-border">
                            <div class="card-body ps-0 pt-0">
                                <div id="apex_line-chart"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                            <h4 class="">{{translate('Information_Details')}}</h4>

                            <div class="d-flex gap-3 align-items-center justify-content-end">
                                <label class="switcher">
                                    <input class="switcher_input service-man-update"
                                           type="checkbox" {{$serviceman->user->is_active?'checked':''}} data-route="{{route('provider.serviceman.status-update',[$serviceman->user->id])}}" data-message="{{translate('want_to_update_status')}}">
                                    <span class="switcher_control"></span>
                                </label>
                                <a type="button" class="bg-transparent border-0 p-0 lh-1"
                                   href="{{route('provider.serviceman.edit', [$serviceman->id])}}">
                                    <span class="material-icons text-info">edit</span>
                                </a>
                                <button type="button" class="bg-transparent border-0 p-0 lh-1 form-alert"
                                        data-id="delete-{{$serviceman->id}}"
                                        data-nessage="{{translate('want_to_delete_this_serviceman')}}?">
                                    <span class="material-icons text-danger">delete</span>
                                </button>
                                <form
                                    action="{{route('provider.serviceman.delete', [$serviceman->id])}}"
                                    method="post"
                                    id="delete-{{$serviceman->id}}"
                                    class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </div>

                        <div class="information-details-box p-3 mt-4">
                            <div class="">
                                <h4 class="c1 mb-3">{{translate('Service_Man_Information')}}</h4>

                                <div class="media flex-wrap gap-3">
                                    <img width="116" src="{{$serviceman->user->profile_image_full_path}}" alt="{{ translate('serviceman') }}">

                                    <div class="media-body">
                                        <ul class="serviceman_info-list">
                                            <li>
                                                <span class="material-icons">person</span>
                                                {{$serviceman->user->first_name . ' ' . $serviceman->user->last_name}}
                                            </li>
                                            <li>
                                                <span class="material-icons">mail</span>
                                                <a href="mailto:{{$serviceman->user->email}}">{{$serviceman->user->email}}</a>
                                            </li>
                                            <li>
                                                <span class="material-icons">phone</span>
                                                <a href="tel:{{$serviceman->user->phone}}">{{$serviceman->user->phone}}</a>
                                            </li>
                                            <li>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="information-details-box p-3 mt-4">
                    <div class="row g-4">
                        <div class="col-lg-3">
                            <h2 class="information-details-box__title c1 mb-3">{{translate('Business_Info')}}</h2>
                            <p>
                                <span>{{translate($serviceman->user->identification_type)}}: </span> {{$serviceman->user->identification_number}}
                            </p>
                        </div>
                        <div class="col-lg-9">
                            <div class="d-flex flex-wrap gap-3 justify-content-lg-end">
                                @foreach($serviceman->user->identification_image_full_path as $img)
                                    <img width="400" src="{{ $img }}" alt="{{ translate('identity-image') }}">
                                @endforeach
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

        $('.service-man-update').on('click', function () {
            let route = $(this).data('route');
            let message = $(this).data('message');
            route_alert(route,message)
        })


        $("#date-range").change(function () {
            location.href = "{{route('provider.serviceman.show', [$serviceman->id])}}?date_range=" + $(this).val();
        });

        var options = {
            series: [{
                name: "{{translate('Total_Bookings')}}",
                data: {{json_encode($chartdata['total_booking'])}}
            }],
            chart: {
                height: 392,
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
                    formatter: function (value) {
                        return Math.round(value);
                    }
                },
            },
            colors: ['#82C662', '#4FA7FF'],
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
                categories: {{json_encode($chartdata['timeline'])}}
            },
            legend: {
                show: true,
                position: 'bottom',
                horizontalAlign: 'center',
                offsetY: -10,
            },
        };

        if (localStorage.getItem('dir') === 'rtl') {
            options.yaxis.labels.offsetX = -20;
        }

        var chart = new ApexCharts(document.querySelector("#apex_line-chart"), options);
        chart.render();
    </script>
@endpush
