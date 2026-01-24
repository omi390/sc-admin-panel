@extends('adminmodule::layouts.master')

@section('title',translate('Overview'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-4">
                <h2 class="page-title mb-2">{{translate('Customer')}}</h2>
                <div>{{translate('Joined_on')}} {{date('d-M-y H:iA', strtotime($customer?->created_at))}}</div>
            </div>

            <div class="mb-3">
                <ul class="nav nav--tabs nav--tabs__style2">
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='overview'?'active':''}}"
                           href="{{url()->current()}}?web_page=overview">{{translate('Overview')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='bookings'?'active':''}}"
                           href="{{url()->current()}}?web_page=bookings">{{translate('Bookings')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='reviews'?'active':''}}"
                           href="{{url()->current()}}?web_page=reviews">{{translate('Reviews')}}</a>
                    </li>
                </ul>
            </div>

            <div class="card">
                <div class="card-body p-30">
                    <div class="row justify-content-center g-2 mb-30">
                        <div class="col-sm-6 col-lg-4 provider-details-overview__statistics d-flex flex-column">
                            <div class="statistics-card statistics-card__style2 statistics-card__pending-withdraw">
                                <h2>{{$customer->bookings_count}}</h2>
                                <h3>{{translate('Total_Booking_Placed')}}</h3>
                            </div>

                            <div class="statistics-card statistics-card__style2 statistics-card__already-withdraw">
                                <h2>{{with_currency_symbol($totalBookingAmount)}}</h2>
                                <h3>{{translate('Total_Booking_Amount')}}</h3>
                            </div>

                        </div>
                        <div class="col-sm-6 col-lg-4 provider-details-overview__statistics d-flex flex-column">

                            <div class="statistics-card statistics-card__style2 statistics-card__total-earning">
                                <h2>{{with_currency_symbol($customer['wallet_balance'])}}</h2>
                                <h3>{{translate('Wallet Balance')}}</h3>
                            </div>

                            <div class="statistics-card statistics-card__style2 statistics-card__withdrawable-amount">
                                <h2>{{$customer['loyalty_point']}}</h2>
                                <h3>{{translate('Loyalty Point')}}</h3>
                            </div>

                        </div>
                        <div class="col-sm-6 col-lg-4 provider-details-overview__order-overview">
                            <div class="statistics-card statistics-card__order-overview h-100 pb-2">
                                <h3 class="mb-0">{{translate('Booking_Overview')}}</h3>
                                <div id="apex-pie-chart" class="d-flex justify-content-center"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h2>{{translate('Personal_Details')}}</h2>
                    </div>

                    <div>
                        <div class="information-details-box media flex-column flex-sm-row gap-20 mb-3">
                            <img class="avatar-img radius-5"
                                 src="{{$customer->profile_image_full_path}}" alt="{{translate('image')}}">
                            <div class="media-body d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <h2 class="information-details-box__title">{{Str::limit($customer->first_name, 30)}}</h2>

                                    <ul class="contact-list">
                                        <li>
                                            <span class="material-symbols-outlined">phone_iphone</span>
                                            <a href="tel:{{$customer->phone}}">{{$customer->phone}}</a>
                                        </li>
                                        <li>
                                            <span class="material-symbols-outlined">mail</span>
                                            <a href="mailto:{{$customer->email}}">{{$customer->email}}</a>
                                        </li>
                                    </ul>
                                </div>
                                @can('customer_update')
                                    <a href="{{route('admin.customer.edit',[$customer->id])}}" class="btn btn--primary">
                                        <span class="material-icons">border_color</span>
                                        {{translate('Edit')}}
                                    </a>
                                @endcan
                            </div>
                        </div>

                        @if($customer->addresses && $customer->addresses->count() > 0)
                            <div class="information-details-box customer-address">
                                @foreach($customer->addresses as $key=>$address)
                                    <div class="d-flex justify-content-between gap-2 mb-20">
                                        <div class="media gap-2 gap-xl-3">
                                            <span class="material-icons fz-30 c1">home</span>
                                            <div class="media-body">
                                                <h4 class="fw-medium mb-1">{{$address->address_label}}</h4>
                                                <div class="text-muted">{{ Str::limit($address->address, 100) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addAddressModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header px-4">
                    <h5 class="modal-title" id="exampleModalLabel">{{translate('Add_Customer_Address')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-0 pt-4 mt-2 px-4">
                    <div class="form-floating mb-30">
                        <input type="text" class="form-control" id="street" name="street"
                               placeholder="{{translate('Street')}}" value="{{old('street')}}" required>
                        <label>{{translate('Street')}}</label>
                    </div>
                    <div class="form-floating mb-30">
                        <input type="text" class="form-control" id="city" name="city"
                               placeholder="{{translate('City')}}" value="{{old('city')}}" required>
                        <label>{{translate('City')}}</label>
                    </div>
                    <div class="form-floating mb-30">
                        <input type="text" class="form-control" id="country" name="country"
                               placeholder="{{translate('Country')}}" value="{{old('country')}}" required>
                        <label>{{translate('Country')}}</label>
                    </div>
                    <div class="form-floating mb-30">
                        <input type="text" class="form-control" id="zip_code" name="zip_code"
                               placeholder="{{translate('Zip_Code')}}" value="{{old('zip_code')}}" required>
                        <label>{{translate('Zip_Code')}}</label>
                    </div>
                    <div class="form-floating mb-30">
                        <textarea type="text" class="form-control" id="address" name="address"
                                  placeholder="{{translate('Address')}}" value="{{old('address')}}" required></textarea>
                        <label>{{translate('Address')}}</label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn--secondary"
                            data-bs-dismiss="modal">{{translate('Close')}}</button>
                    <button type="button" class="btn btn--primary">{{translate('Save_changes')}}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')

    <script src="{{asset('public/assets/admin-module/plugins/apex/apexcharts.min.js')}}"></script>

    <script>
        "use strict"
        var options = {
            labels: ['pending', 'accepted', 'ongoing', 'completed', 'canceled'],
            series: {{json_encode($total)}},
            chart: {
                width: 235,
                height: 160,
                type: 'donut',
            },
            dataLabels: {
                enabled: false
            },
            title: {
                text: "{{$customer->bookings_count}} Bookings",
                align: 'center',
                offsetX: 0,
                offsetY: 58,
                floating: true,
                style: {
                    fontSize: '12px',
                    fontWeight: '500',
                },
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    legend: {
                        show: true
                    }
                }
            }],
            legend: {
                position: 'bottom',
                offsetY: -5,
                height: 30,
            },
        };

        var chart = new ApexCharts(document.querySelector("#apex-pie-chart"), options);
        chart.render();
    </script>
@endpush
