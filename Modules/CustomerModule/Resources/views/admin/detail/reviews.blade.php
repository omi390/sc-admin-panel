@extends('adminmodule::layouts.master')

@section('title',translate('Reviews'))

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
                            <span class="opacity-75">{{translate('Total_Reviews')}}:</span>
                            <span class="title-color">{{$reviews->total()}}</span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                <form action="{{url()->current()}}?web_page=reviews"
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
                                    <thead class="align-middle text-nowrap">
                                    <tr>
                                        <th>{{translate('Booking_ID')}}</th>
                                        <th>{{translate('Booking_Date')}}</th>
                                        <th>{{translate('Ratings')}}</th>
                                        <th>{{translate('Reviews')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($reviews as $review)
                                        <tr>
                                            <td>
                                                <a href="{{route('admin.booking.details', [$review->booking->id,'web_page'=>'details'])}}">{{$review->booking->readable_id}}</a>
                                            </td>
                                            <td>{{date('d-M-y H:iA', strtotime($review->booking->created_at))}}</td>
                                            <td>{{$review->review_rating}}</td>
                                            <td>{{Str::limit($review->review_comment, 100)}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                {!! $reviews->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

