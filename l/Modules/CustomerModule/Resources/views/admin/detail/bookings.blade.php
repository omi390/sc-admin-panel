@extends('adminmodule::layouts.master')

@section('title',translate('Bookings'))

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

            <div class="tab-content">
                <div class="tab-pane fade show active" id="boookings-tab-pane">
                    <div class="d-flex justify-content-end border-bottom mb-10">
                        <div class="d-flex gap-2 fw-medium me-4">
                            <span class="opacity-75">{{translate('Total_Booking')}}:</span>
                            <span class="title-color">{{$bookings->total()}}</span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                <form action="{{url()->current()}}?web_page=bookings"
                                      class="search-form search-form_style-two"
                                      method="POST">
                                    @csrf
                                    <div class="input-group search-form__input_group">
                                            <span class="search-form__icon">
                                                <span class="material-icons">search</span>
                                            </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                               value="{{$search??''}}" name="search"
                                               placeholder="{{translate('search_here')}}">
                                    </div>
                                    <button type="submit" class="btn btn--primary">
                                        {{translate('search')}}
                                    </button>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table id="example" class="table align-middle">
                                    <thead class="align-middle">
                                    <tr>
                                        <th>{{translate('Booking_ID')}}</th>
                                        <th>{{translate('Provider_Info')}}</th>
                                        <th>{{translate('Total_Amount')}}</th>
                                        <th>{{translate('Booking_Status')}}</th>
                                        <th>{{translate('Payment_Status')}}</th>
                                        <th>{{translate('Schedule_Time')}}</th>
                                        <th>{{translate('Booking_Date')}}</th>
                                        <th>{{translate('Action')}} </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($bookings as $booking)
                                        <tr>
                                            <td>{{$booking->readable_id}}</td>
                                            <td>
                                                {{Str::limit(($booking->provider) ? ($booking?->provider->company_name) : translate('No provider accepted yet'), 30)}}
                                            </td>
                                            <td>{{with_currency_symbol($booking->total_booking_amount)}}</td>
                                            <td>
                                                {{translate($booking->booking_status)}}
                                            </td>
                                            <td>
                                                <span
                                                    class="badge badge badge-{{$booking->is_paid == 1 ? 'success' : 'danger'}} radius-50">
                                                    <span class="dot"></span>
                                                    {{$booking->is_paid == 1 ? translate('Paid') : translate('Unpaid')}}
                                                </span>
                                            </td>
                                            <td>{{date('d-M-Y h:ia',strtotime($booking->service_schedule))}}</td>
                                            <td>{{date('d-M-Y h:ia',strtotime($booking->created_at))}}</td>
                                            <td>
                                                <div class="table-actions">
                                                    <a href="{{route('provider.booking.details', [$booking->id,'web_page'=>'details'])}}"
                                                       type="button"
                                                       class="action-btn btn--light-primary fw-medium text-capitalize fz-14" style="--size: 30px">
                                                        <span class="material-icons">visibility</span>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
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
        </div>
    </div>
@endsection

