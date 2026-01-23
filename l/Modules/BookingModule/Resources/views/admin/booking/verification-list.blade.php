@extends('adminmodule::layouts.master')

@section('title',translate('Booking_List'))

@section('content')
    <div class="filter-aside">
        <div class="filter-aside__header d-flex justify-content-between align-items-center">
            <h3 class="filter-aside__title">{{translate('Filter_your_Booking')}}</h3>
            <button type="button" class="btn-close p-2"></button>
        </div>
        <form action="{{url()->current()}}?booking_status={{$queryParams['booking_status']}}&type={{$queryParams['type']}}" method="POST"
              enctype="multipart/form-data" id="filter-form">
            @csrf
            <div class="filter-aside__body d-flex flex-column">
                <div class="filter-aside__date_range">
                    <h4 class="fw-normal mb-4">{{translate('Select_Date_Range')}}</h4>
                    <div class="mb-30">
                        <div class="form-floating">
                            <input type="date" class="form-control" placeholder="{{translate('start_date')}}"
                                   name="start_date"
                                   value="{{$queryParams['start_date']}}">
                            <label for="floatingInput">{{translate('Start_Date')}}</label>
                        </div>
                    </div>
                    <div class="fw-normal mb-30">
                        <div class="form-floating">
                            <input type="date" class="form-control" placeholder="{{translate('end_date')}}"
                                   name="end_date"
                                   value="{{$queryParams['end_date']}}">
                            <label for="floatingInput">{{translate('End_Date')}}</label>
                        </div>
                    </div>
                </div>

                <div class="filter-aside__category_select">
                    <h4 class="fw-normal mb-2">{{translate('Select_Categories')}}</h4>
                    <div class="mb-30">
                        <select class="category-select theme-input-style w-100" name="category_ids[]"
                                multiple="multiple">
                            @foreach($categories as $category)
                                <option
                                    value="{{$category->id}}" {{in_array($category->id,$queryParams['category_ids']??[])?'selected':''}}>
                                    {{$category->name}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="filter-aside__category_select">
                    <h4 class="fw-normal mb-2">{{translate('Select_Sub_Categories')}}</h4>
                    <div class="mb-30">
                        <select class="subcategory-select theme-input-style w-100" name="sub_category_ids[]"
                                multiple="multiple">
                            @foreach($subCategories as $subCategory)
                                <option
                                    value="{{$subCategory->id}}" {{in_array($subCategory->id,$queryParams['sub_category_ids']??[])?'selected':''}}>
                                    {{$subCategory->name}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="filter-aside__zone_select">
                    <h4 class="mb-2 fw-normal">{{translate('Select_Zones')}}</h4>
                    <div class="mb-30">
                        <select class="zone-select theme-input-style w-100" name="zone_ids[]" multiple="multiple">
                            @foreach($zones as $zone)
                                <option
                                    value="{{$zone->id}}" {{in_array($zone->id,$queryParams['zone_ids']??[])?'selected':''}}>
                                    {{$zone->name}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="filter-aside__bottom_btns p-20">
                <div class="d-flex justify-content-center gap-20">
                    <button class="btn btn--secondary text-capitalize" id="reset-btn"
                            type="reset">{{translate('Clear_all_Filter')}}</button>
                    <button class="btn btn--primary text-capitalize" type="submit">{{translate('Filter')}}</button>
                </div>
            </div>
        </form>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap d-flex align-items-center justify-content-between gap-2 mb-3">
                        <h2 class="page-title">{{translate('Verify_Request')}}</h2>
                        <div class="ripple-animation" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{{translate('This booking verification list is for verifying bookings whose total amount exceeds the maximum booking amount for cash on delivery
')}}" type="button">
                            <img src="{{asset('/public/assets/admin-module/img/info.svg')}}" class="svg" alt="">
                        </div>
                    </div>

                    <div
                        class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                        <ul class="nav nav--tabs">
                            <li class="nav-item">
                                <a class="nav-link {{$type=='pending'?'active':''}}"
                                   href="{{route('admin.booking.list.verification', ['booking_status'=>'pending', 'type' => 'pending'])}}">
                                    {{translate('pending')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$type=='denied'?'active':''}}"
                                   href="{{route('admin.booking.list.verification', ['booking_status'=>'pending', 'type' => 'denied'])}}">
                                    {{translate('denied')}}
                                </a>
                            </li>
                        </ul>

                        <div class="d-flex gap-2 fw-medium">
                            <span class="opacity-75">{{translate('Total_Request')}}:</span>
                            <span class="title-color">{{$bookings->total()}}</span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">

                                <form action="{{url()->current()}}?booking_status={{$queryParams['booking_status']}}&type={{$queryParams['type']}}"
                                      class="search-form search-form_style-two"
                                      method="POST">
                                    @csrf
                                    <div class="input-group search-form__input_group">
                                            <span class="search-form__icon">
                                                <span class="material-icons">search</span>
                                            </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                               value="{{$queryParams['search']??''}}" name="search"
                                               placeholder="{{translate('search_here')}}">
                                    </div>
                                    <button type="submit"
                                            class="btn btn--primary">{{translate('search')}}</button>
                                </form>

                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    @can('booking_export')
                                    <div class="dropdown">
                                        <button type="button"
                                                class="btn btn--secondary text-capitalize dropdown-toggle"
                                                data-bs-toggle="dropdown">
                                            <span class="material-icons">file_download</span> {{translate('download')}}
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                            <li><a class="dropdown-item"
                                                   href="{{ route('admin.booking.download',$queryParams) }}">{{translate('excel')}}</a>
                                            </li>
                                        </ul>
                                    </div>
                                    @endcan

                                    <button type="button" class="btn text-capitalize filter-btn border px-3">
                                        <span class="material-icons">filter_list</span> {{translate('Filter')}}
                                        <span class="count">{{$filterCounter??0}}</span>
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="example" class="table align-middle">
                                    <thead class="text-nowrap">
                                    <tr>
                                        <th>{{translate('SL')}}</th>
                                        <th>{{translate('Booking_ID')}}</th>
                                        <th>{{translate('Customer_Info')}}</th>
                                        <th>{{translate('Total_Amount')}}</th>
                                        <th>{{translate('Payment_Status')}}</th>
                                        <th>{{translate('Schedule_Date')}}</th>
                                        <th>{{translate('Booking_Date')}}</th>
                                        <th>{{translate('Status')}}</th>
                                        <th>{{translate('Action')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($bookings as $key=>$booking)
                                        <tr>
                                            <td>{{$key+$bookings?->firstItem()}}</td>
                                            <td>
                                                <a href="{{route('admin.booking.details', [$booking->id,'web_page'=>'details'])}}">
                                                    {{$booking->readable_id}}</a>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column gap-1">
                                                    @if($booking->customer)
                                                        <a href="{{route('admin.customer.detail',[$booking?->customer?->id, 'web_page'=>'overview'])}}">
                                                            @php
                                                                $fullName = ($booking?->customer?->first_name ?? '') . ' ' . ($booking?->customer?->last_name ?? '');
                                                                $limitedFullName = Str::limit($fullName, 30);
                                                            @endphp

                                                            {{ $limitedFullName }}
                                                        </a>
                                                    @else
                                                        <span>
                                                            {{Str::limit($booking?->service_address?->contact_person_name, 30)}}
                                                        </span>
                                                    @endif
                                                    <a href="tel:{{$booking->customer ? $booking?->customer?->phone : $booking?->service_address?->contact_person_number}}">{{$booking->customer ? $booking?->customer?->phone : $booking?->service_address?->contact_person_number}}</a>
                                                </div>
                                            </td>
                                            <td>{{with_currency_symbol($booking->total_booking_amount)}}</td>
                                            <td>
                                                <span
                                                    class="badge badge badge-{{$booking->is_paid?'success':'danger'}} radius-50">
                                                    <span class="dot"></span>
                                                    {{$booking->is_paid?translate('paid'):translate('unpaid')}}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column gap-1">
                                                    <div>{{date('d-M-Y',strtotime($booking->service_schedule))}}</div>
                                                    <div>{{date('h:ia',strtotime($booking->service_schedule))}}</div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column gap-1">
                                                    <div>{{date('d-M-Y',strtotime($booking->created_at))}}</div>
                                                    <div>{{date('h:ia',strtotime($booking->created_at))}}</div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($booking->is_verified == '0')
                                                    <label class="badge badge-info bg-transparent p-0">
                                                        <span class="dot"></span>
                                                        {{translate('pending')}}
                                                    </label>
                                                @elseif($booking->is_verified == '1')
                                                    <label class="badge badge-success bg-transparent p-0">
                                                        <span class="dot"></span>
                                                        {{translate('verified')}}
                                                    </label>
                                                @else
                                                    <label class="badge badge-danger bg-transparent p-0">
                                                        <span class="dot"></span>
                                                        {{translate('denied')}}
                                                    </label>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-2">
                                                    <a href="{{route('admin.booking.details', [$booking->id,'web_page'=>'details'])}}"
                                                        type="button"
                                                        class="action-btn btn--light-primary" style="--size: 30px">
                                                        <span class="material-icons m-0">visibility</span>
                                                    </a>
                                                    @if($booking->is_verified == '0')
                                                        @can('booking_can_approve_or_deny')
                                                        <button type="button" data-verify="{{$booking->id}}" class="action-btn btn--success booking-verify" style="--size: 30px">
                                                            <span class="material-icons m-0">done_outline</span>
                                                        </button>

                                                        <button type="button" data-deny="{{$booking->id}}" style="--size: 30px"
                                                                class="action-btn btn--danger booking-deny"
                                                                data-bs-toggle="modal" data-bs-target="#exampleModal--{{$booking->id}}">
                                                            <span class="material-icons m-0">close</span>
                                                        </button>

                                                        @endcan

                                                        <div class="modal fade" id="exampleModal--{{$booking->id}}" tabindex="-1"
                                                             aria-labelledby="exampleModalLabel--{{$booking->id}}" aria-hidden="true">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-body pt-5 p-md-5">
                                                                        <button type="button" class="btn-close"
                                                                                data-bs-dismiss="modal"
                                                                                aria-label="Close"></button>

                                                                        <div class="d-flex justify-content-center mb-4">
                                                                            <img width="75" height="75" src="{{asset('public/assets/admin-module/img/delete2.png')}}" class="rounded-circle" alt="">
                                                                        </div>

                                                                        <h3 class="text-start mb-1 fw-medium text-center">{{translate('Are you sure you want to cancel the request?')}}</h3>
                                                                        <p class="text-start fs-12 fw-medium text-muted text-center">{{translate('You will lost the custom booking request?')}}</p>
                                                                        <form method="post"
                                                                              action="{{route('admin.booking.verification-status',[$booking->id])}}">
                                                                            @csrf
                                                                            <div class="form-floating">
                                                                                <textarea class="form-control h-69px"
                                                                                          placeholder="{{translate('Cancellation Note')}}"
                                                                                          name="booking_deny_note"
                                                                                          id="add-your-note" required></textarea>
                                                                                <label for="add-your-note"
                                                                                       class="d-flex align-items-center gap-1">
                                                                                    {{translate('Cancellation Note')}}
                                                                                </label>
                                                                                <input type="hidden" value="deny" name="status">
                                                                                <div class="d-flex justify-content-center mt-3 gap-3">
                                                                                    <button type="button" data-bs-dismiss="modal"
                                                                                            aria-label="Close" class="btn btn--secondary min-w-92px px-2">{{translate('Not Now')}}</button>
                                                                                    <button type="submit" class="btn btn--danger min-w-92px">{{translate('Yes')}}</button>
                                                                                </div>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
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

@push('script')
    <script>
        (function ($) {
            "use strict";

            $('.category-select').select2({
                placeholder: "{{translate('Select Category')}}"
            });
            $('.subcategory-select').select2({
                placeholder: "{{translate('Select Subcategory')}}"
            });
            $('.zone-select').select2({
                placeholder: "{{translate('Select Zone')}}"
            })

            $('.booking-verify').on('click', function () {
                let itemId = $(this).data('verify');
                let route = '{{ route('admin.booking.verification_status_update', [':itemId']) }}';
                route = route.replace(':itemId', itemId);
                route_alert_reload(route, '{{ translate('want_to_verified_this_booking') }}');
            })

        })(jQuery);
    </script>

    <script>
        $(document).ready(function() {
            $('#reset-btn').on('click', function() {
                $('#filter-form')[0].reset();
                $('.subcategory-select').val([]).trigger('change');
                $('.category-select').val([]).trigger('change');
                $('.zone-select').val([]).trigger('change');
            });
        });
    </script>
@endpush
