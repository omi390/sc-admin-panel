@extends('providermanagement::layouts.master')

@section('title',translate('Bookings'))

@section('content')
    @if(business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values && Request()->user()->provider->is_suspended == 1)
        <div class="alert alert-danger">
            <div class="media gap-3 align-items-center">
                <div class="alert-close-btn">
                    <span class="material-symbols-outlined">close</span>
                </div>
                <div class="media-body">
                    <h5 class="text-capitalize">{{translate('Attention Please')}}!</h5>
                    <p class="text-dark fs-12">
                        {{translate('Your limit to hold cash is exceeded. Your account has been suspended until you pay the due. You will not receive any new booking requests from now')}}
                    </p>
                </div>
            </div>
        </div>
    @endif
    @if(!request()->user()->provider->service_availability && $queryParams['booking_status'] == 'pending')
        <div class="alert alert-primary availability-alert">
            <div class="media gap-3 align-items-center">
                <div class="alert-close-btn">
                    <span class="material-symbols-outlined close-btn">close</span>
                </div>
                <div class="media-body">
                    <h5 class="text-capitalize">{{translate('Attention Please')}}!</h5>
                    <p class="text-dark fs-12">
                        {{translate('The service availability option has been turned off. You will not receive any new booking requests until you turn on the service availability option')}}
                        <span><a class="text-primary"
                                 href="{{route('provider.business-settings.get-business-information')}}">{{translate('Go to settings')}}</a></span>
                    </p>
                </div>
            </div>
        </div>
    @endif
    <div class="filter-aside">
        <div class="filter-aside__header d-flex justify-content-between align-items-center">
            <h3 class="filter-aside__title">{{translate('Filter_your_Bookings')}}</h3>
            <button type="button" class="btn-close p-2"></button>
        </div>
        <form action="{{route('provider.booking.list', ['booking_status'=>$queryParams['booking_status']])}}"
              id="filterForm"
              method="POST"
              enctype="multipart/form-data">
            @csrf
            <div class="filter-aside__body d-flex flex-column">
                <div class="filter-aside__date_range">
                    <h4 class="fw-normal mb-4">{{translate('Select_Date_Range')}}</h4>
                    <div class="mb-30">
                        {{--@dd($queryParams)--}}
                        <div class="form-floating">
                            <input type="date" class="form-control start_date" placeholder="{{translate('start_date')}}"
                                   name="start_date"
                                   value="{{$queryParams['start_date']}}">
                            <label for="floatingInput">{{translate('Start_Date')}}</label>
                        </div>
                    </div>
                    <div class="fw-normal mb-30">
                        <div class="form-floating">
                            <input type="date" class="form-control end_date" placeholder="{{translate('end_date')}}"
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
                        <select class="sub-category-select theme-input-style w-100" name="sub_category_ids[]"
                                multiple="multiple">
                            @foreach($subCategories as $sub_category)
                                <option
                                    value="{{$sub_category->id}}" {{in_array($sub_category->id,$queryParams['sub_category_ids']??[])?'selected':''}}>
                                    {{$sub_category->name}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="filter-aside__bottom_btns p-20">
                <div class="d-flex justify-content-center gap-20">
                    <button class="btn btn--secondary text-capitalize fz-14" id="clearFilterButton"
                            type="reset">{{translate('Clear_all_Filter')}}</button>
                    <button class="btn btn--primary text-capitalize fz-14"
                            type="submit">{{translate('Filter')}}</button>
                </div>
            </div>
        </form>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Booking_Request')}}</h2>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">

                                <form action="{{url()->current()}}?booking_status={{$queryParams['booking_status']}}"
                                      class="search-form search-form_style-two" method="POST">
                                    @csrf
                                    <div class="input-group search-form__input_group">
                                            <span class="search-form__icon">
                                                <span class="material-icons">search</span>
                                            </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                               value="{{$queryParams['search']??''}}" name="search"
                                               placeholder="{{translate('search_here')}}">
                                    </div>
                                    <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                                </form>

                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn--secondary text-capitalize dropdown-toggle"
                                                data-bs-toggle="dropdown">
                                            <span class="material-icons">file_download</span>
                                            {{translate('download')}}
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                            <li><a class="dropdown-item"
                                                   href="{{route('provider.booking.download', $queryParams)}}">{{translate('excel')}}</a>
                                            </li>
                                        </ul>
                                    </div>

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
                                        <th>{{translate('status')}}</th>
                                        <th>{{translate('Action')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($bookings as $key=>$booking)
                                        <tr>
                                            <td>{{$key+$bookings?->firstItem()}}</td>
                                            <td>
                                                <a href="{{route('provider.booking.details', [$booking->id,'web_page'=>'details'])}}"{{$booking->readable_id}}>{{$booking->readable_id}}</a>
                                            </td>
                                            <td>
                                                @if(isset($booking->customer))
                                                    {{Str::limit($booking?->customer?->first_name, 30)}} <br/>
                                                    {{$booking?->customer?->phone}}
                                                @else
                                                    {{Str::limit($booking?->service_address?->contact_person_name, 30)}}
                                                    <br/>
                                                    {{$booking?->service_address?->contact_person_number}}
                                                @endif
                                            </td>
                                            <td>{{with_currency_symbol($booking->total_booking_amount)}}</td>
                                            <td>
                                                <span
                                                    class="badge badge badge-{{$booking->is_paid?'success':'danger'}}">
                                                    {{$booking->is_paid?translate('paid'):translate('unpaid')}}
                                                </span>
                                            </td>
                                            <td>{{date('d-M-Y h:ia',strtotime($booking->service_schedule))}}</td>
                                            <td>{{date('d-M-Y h:ia',strtotime($booking->created_at))}}</td>
                                            <td>
                                                <span class="badge badge badge-success">
                                                    {{ucfirst($booking->booking_status)}}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="table-actions gap-2">
                                                    <a href="{{route('provider.booking.details', [$booking->id,'web_page'=>'details'])}}"
                                                       type="button"
                                                       class="action-btn btn--light-primary fw-medium text-capitalize fz-14"
                                                       style="--size: 30px">
                                                        <span class="material-icons">visibility</span>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="text-center">
                                            <td colspan="7">{{translate('no data available')}}</td>
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
        </div>
    </div>
@endsection

@push('script')

    <script>
        "use strict";

        $(document).ready(function () {
            $('.close-btn').on('click', function () {
                $('.availability-alert').hide();
            });
        });

        $(document).ready(function () {
            $('.category-select').select2({
                placeholder: "{{translate('Select_category')}}",
            });
            $('.sub-category-select').select2({
                placeholder: "{{translate('Select_sub_category')}}",
            });
        });

    </script>

    <script>
        $(document).ready(function () {
            $('#clearFilterButton').on('click', function () {
                let form = $('#filterForm');
                form[0].reset();
                $('.start_date').removeAttr('value');
                $('.end_date').removeAttr('value');
                $('.category-select').val(null).trigger('change');
                $('.sub-category-select').val(null).trigger('change');
            });
        });
    </script>

@endpush
