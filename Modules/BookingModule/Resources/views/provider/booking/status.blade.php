@extends('providermanagement::layouts.master')

@section('title',translate('Booking_Status'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">

            <div class="page-title-wrap mb-4">
                <h2 class="page-title">{{translate('Booking_Details')}} </h2>
            </div>

            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-3">
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                        <h3 class="c1">{{translate('Booking')}} # {{$booking['readable_id']}}</h3>
                        <span class="badge badge-info">{{ucfirst($booking->booking_status)}}</span>
                    </div>
                    <p class="opacity-75 fz-12">{{translate('Booking_Placed')}}
                        : {{date('d-M-Y h:ia',strtotime($booking->created_at))}}</p>
                </div>
                <div class="d-flex flex-wrap flex-xxl-nowrap gap-3">
                    <div class="d-flex gap-3">
                        @php($provider_can_edit_booking = (int)(business_config('provider_can_edit_booking', 'provider_config'))?->live_values)

                        @if($provider_can_edit_booking && in_array($booking['booking_status'], ['accepted', 'ongoing']) && $booking->booking_partial_payments->isEmpty())
                            <button class="btn btn--primary" data-bs-toggle="modal"
                                    data-bs-target="#serviceUpdateModal--{{$booking['id']}}"
                                    data-toggle="tooltip"
                                    title="{{translate('Add or remove services')}}">
                                <span
                                    class="material-symbols-outlined">edit</span>{{translate('Edit Services')}}
                            </button>
                        @endif
                        <a href="{{route('provider.booking.invoice',[$booking->id])}}"
                           class="btn btn-primary" target="_blank">
                            <span class="material-icons">description</span>
                            {{translate('Invoice')}}
                        </a>
                    </div>
                </div>
            </div>


            <ul class="nav nav--tabs nav--tabs__style2 mb-4">
                <li class="nav-item">
                    <a class="nav-link {{$webPage=='details'?'active':''}}"
                        href="{{url()->current()}}?web_page=details">{{translate('details')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{$webPage=='status'?'active':''}}"
                        href="{{url()->current()}}?web_page=status">{{translate('status')}}</a>
                </li>
            </ul>

            <div class="row gy-3">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center gap-3 flex-wrap">
                                    <div>
                                        <h4 class="mb-2">{{translate('Payment Method')}}</h4>
                                        <h5 class="c1 mb-2"><span
                                                class="text-capitalize">{{ str_replace(['_', '-'], ' ', $booking->payment_method) }}
                                                @if($booking->payment_method == 'offline_payment' && $booking?->booking_offline_payments?->first()?->method_name)
                                                    ({{($booking?->booking_offline_payments?->first()?->method_name)}})
                                                @endif</span>
                                        </h5>
                                        <p><span>{{translate('Amount')}} : </span> {{with_currency_symbol($booking->total_booking_amount)}}</p>
                                        @if($booking->payment_method == 'offline_payment')
                                            <h4 class="mb-2">{{translate('Payment_Info')}}</h4>
                                            <div class="d-flex gap-1 flex-column">
                                                @foreach($booking?->booking_offline_payments?->first()?->customer_information??[] as $key=>$item)
                                                    <div><span>{{translate($key)}}</span>:
                                                        <span>{{translate($item)}}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-start text-sm-end">
                                        <p class="mb-2">
                                            <span>{{translate('Payment_Status')}} : </span>
                                            <span class="text-{{$booking->is_paid ? 'success' : 'danger'}}"
                                                  id="payment_status__span">{{$booking->is_paid ? translate('Paid') : translate('Unpaid')}}</span>

                                            @if(!$booking->is_paid && $booking->booking_partial_payments->isNotEmpty())
                                                <span
                                                    class="small badge badge-info text-success p-1 fz-10">{{translate('Partially paid')}}</span>
                                            @endif
                                        </p>
                                        <h5 class="d-flex gap-1 flex-wrap align-items-center">
                                            <div>{{translate('Schedule_Date')}} :</div>
                                            <div id="service_schedule__span">
                                                <div>{{date('d-M-Y h:ia',strtotime($booking->service_schedule))}} <span
                                                        class="text-secondary">{{$booking?->schedule_histories->count() > 1 ? '(' . translate('Edited') . ')' : '' }}</span>
                                                </div>

                                                <div class="timeline-container">
                                                    <ul class="timeline-sessions">
                                                        <p class="fs-14">{{translate('Schedule Change Log')}}</p>
                                                        @foreach($booking?->schedule_histories()->orderBy('created_at', 'desc')->get() as $history)
                                                            <li class="{{$booking->service_schedule == $history->schedule ? 'active' : ''}}">
                                                                <div
                                                                    class="timeline-date">{{ \Carbon\Carbon::parse($history->schedule)->format('d-M-Y') }}</div>
                                                                <div
                                                                    class="timeline-time">{{ \Carbon\Carbon::parse($history->schedule)->format('h:i A') }}</div>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </h5>
                                    </div>
                                </div>
                            </div>

                            <div class="timeline-wrapper mt-4 ps-xl-5">
                                <div class="timeline-steps m-0">
                                    <div class="timeline-step completed">
                                        <div class="timeline-number">
                                            <svg viewBox="0 0 512 512" width="100" title="check">
                                                <path
                                                    d="M173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69 432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001z"/>
                                            </svg>
                                        </div>
                                        <div class="timeline-info">
                                            <p class="timeline-title text-capitalize">{{translate('Booking_Placed')}}</p>
                                            <p class="timeline-text">{{date('d-M-Y h:ia',strtotime($booking->created_at))}}</p>
                                            <p class="timeline-text">By
                                                - {{isset($booking->customer) ? Str::limit($booking->customer->first_name.' '.$booking->customer->last_name, 30):translate('Not_Available')}}</p>
                                        </div>
                                    </div>
                                    @foreach($booking->status_histories as $status_history)
                                        <div class="timeline-step completed">
                                            <div class="timeline-number">
                                                <svg viewBox="0 0 512 512" width="100" title="check">
                                                    <path
                                                        d="M173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69 432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001z"/>
                                                </svg>
                                            </div>
                                            <div class="timeline-info">
                                                <p class="timeline-title text-capitalize">{{$status_history->booking_status}}</p>
                                                <p class="timeline-text">{{date('d-M-Y h:ia',strtotime($status_history->created_at))}}</p>
                                                <p class="timeline-text">{{translate('By')}}
                                                    @if(isset($status_history->user->provider))
                                                        - {{Str::limit($status_history?->user?->provider?->company_name, 30)}}
                                                    @else
                                                        - {{isset($status_history->user) ? Str::limit($status_history->user->first_name.' '.$status_history->user->last_name, 30):''}}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-column gap-30">

                                @php($customer_name = $booking?->service_address?->contact_person_name)
                                @php($customer_phone = $booking?->service_address?->contact_person_number)

                                <div class="c1-light-bg radius-10 flex-grow-1">
                                    <div
                                        class="border-bottom d-flex align-items-center justify-content-between gap-2 py-3 px-4">
                                        <h4 class="d-flex align-items-center gap-2">
                                            <span class="material-icons title-color">person</span>
                                            {{translate('Customer_Information')}}
                                        </h4>

                                        <div class="btn-group">
                                            <div class="cursor-pointer" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                <span class="material-symbols-outlined">more_vert</span>
                                            </div>
                                            <ul class="dropdown-menu dropdown-menu__custom border-none dropdown-menu-end">
                                                <li data-bs-toggle="modal"
                                                    data-bs-target="#serviceAddressModal--{{$booking['id']}}"
                                                    data-toggle="tooltip" data-placement="top">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="material-symbols-outlined">chat</span>
                                                        {{translate('Chat with Customer')}}
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="py-3 px-4">
                                        @php($customer_name = $booking?->service_address?->contact_person_name)
                                        @php($customer_phone = $booking?->service_address?->contact_person_number)

                                        <div class="media gap-2 flex-wrap">
                                            @if(!$booking?->is_guest && $booking?->customer)
                                                <img width="58" height="58" class="rounded-circle border border-white"
                                                     src="{{$booking?->customer?->profile_image_full_path}}" alt="{{translate('user_image')}}">
                                            @else
                                                <img width="58" height="58" class="rounded-circle border border-white"
                                                     src="{{ asset('public/assets/provider-module/img/user2x.png') }}" alt="{{translate('user_image')}}">
                                            @endif
                                            <div class="media-body">
                                                <h5 class="c1 mb-3">
                                                    <span>{{Str::limit($customer_name??'', 30)}}</span>
                                                </h5>
                                                <ul class="list-info">
                                                    @if ($customer_phone)
                                                        <li>
                                                            <span class="material-icons">phone_iphone</span>
                                                            <a href="tel:{{$customer_phone}}">{{$customer_phone}}</a>
                                                        </li>
                                                    @endif
                                                    <li>
                                                        <span class="material-icons">map</span>
                                                        <p>{{Str::limit($booking?->service_address?->address??translate('not_available'), 100)}}</p>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="c1-light-bg radius-10 flex-grow-1">
                                    <div
                                        class="border-bottom d-flex align-items-center justify-content-between gap-2 py-3 px-4">
                                        <h4 class="d-flex align-items-center gap-2">
                                            <span class="material-icons title-color">person</span>
                                            {{translate('Serviceman_Information')}}
                                        </h4>
                                    </div>
                                    @if(isset($booking->serviceman))
                                        <div class="py-3 px-4">
                                            <div class="media gap-2 flex-wrap">
                                                <img width="58" height="58" class="rounded-circle border border-white"
                                                     src="{{$booking?->serviceman?->user?->profile_image_full_path}}" alt="{{translate('serviceman')}}">
                                                <div class="media-body">
                                                    <h5 class="c1 mb-3">{{Str::limit($booking->serviceman && $booking->serviceman->user ? $booking->serviceman->user->first_name.' '.$booking->serviceman->user->last_name:'', 30)}}</h5>
                                                    <ul class="list-info">
                                                        <li>
                                                            <span class="material-icons">phone_iphone</span>
                                                            <a href="tel:{{$booking->serviceman && $booking->serviceman->user ? $booking->serviceman->user->phone:''}}">
                                                                {{$booking->serviceman && $booking->serviceman->user ? $booking->serviceman->user->phone:''}}
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="d-flex flex-column gap-2 mt-30 align-items-center">
                                            <span class="material-icons text-muted fs-2">account_circle</span>
                                            <p class="text-muted text-center fw-medium mb-3">{{translate('No Serviceman Information')}}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="changeScheduleModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
         aria-labelledby="changeScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{route('provider.booking.schedule_update',[$booking->id])}}" method="post">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"
                            id="changeScheduleModalLabel">{{translate('Change_Booking_Schedule')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="datetime-local" class="form-control" id="service_schedule" name="service_schedule"
                               value="{{$booking->service_schedule}}">
                    </div>
                    <div class="p-3 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">{{translate('Close')}}</button>
                        <button type="submit" class="btn btn-primary">{{translate('Submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include('bookingmodule::provider.booking.partials.details._service-modal')
@endsection

@push('script')
    <script>
        $(document).ready(function () {
            $('#category_selector__select').select2({dropdownParent: "#serviceUpdateModal--{{$booking['id']}}"});
            $('#sub_category_selector__select').select2({dropdownParent: "#serviceUpdateModal--{{$booking['id']}}"});
            $('#service_selector__select').select2({dropdownParent: "#serviceUpdateModal--{{$booking['id']}}"});
            $('#service_variation_selector__select').select2({dropdownParent: "#serviceUpdateModal--{{$booking['id']}}"});
        });

        $("#service_selector__select").on('change', function () {
            $("#service_variation_selector__select").html('<option value="" selected disabled>{{translate('Select Service Variant')}}</option>');

            const serviceId = this.value;
            const route = '{{route('provider.booking.service.ajax-get-variant')}}' + '?service_id=' + serviceId + '&zone_id=' + "{{$booking->zone_id}}";

            $.get({
                url: route,
                dataType: 'json',
                data: {},
                beforeSend: function () {
                    $('.preloader').show();
                },
                success: function (response) {
                    var selectString = '<option value="" selected disabled>{{translate('Select Service Variant')}}</option>';
                    response.content.forEach((item) => {
                        selectString += `<option value="${item.variant_key}">${item.variant}</option>`;
                    });
                    $("#service_variation_selector__select").html(selectString)
                },
                complete: function () {
                    $('.preloader').hide();
                },
                error: function () {
                    toastr.error('{{translate('Failed to load')}}')
                }
            });
        })

        $("#serviceUpdateModal--{{$booking['id']}}").on('hidden.bs.modal', function () {
            $('#service_selector__select').prop('selectedIndex', 0);
            $("#service_variation_selector__select").html('<option value="" selected disabled>{{translate('Select Service Variant')}}</option>');
            $("#service_quantity").val('');
        });

        $("#add-service").on('click', function () {
            const service_id = $("[name='service_id']").val();
            const variant_key = $("[name='variant_key']").val();
            const quantity = parseInt($("[name='service_quantity']").val());
            const zone_id = '{{$booking->zone_id}}';


            if (service_id === '' || service_id === null) {
                toastr.error('{{translate('Select a service')}}', {CloseButton: true, ProgressBar: true});
                return;
            } else if (variant_key === '' || variant_key === null) {
                toastr.error('{{translate('Select a variation')}}', {CloseButton: true, ProgressBar: true});
                return;
            } else if (quantity < 1) {
                toastr.error('{{translate('Quantity must not be empty')}}', {CloseButton: true, ProgressBar: true});
                return;
            }

            let variant_key_array = [];
            $('input[name="variant_keys[]"]').each(function () {
                variant_key_array.push($(this).val());
            });

            if (variant_key_array.includes(variant_key)) {
                const decimal_point = parseInt('{{(business_config('currency_decimal_point', 'business_information'))->live_values ?? 2}}');

                const old_qty = parseInt($(`#qty-${variant_key}`).val());
                const updated_qty = old_qty + quantity;

                const old_total_cost = parseFloat($(`#total-cost-${variant_key}`).text());
                const updated_total_cost = ((old_total_cost * updated_qty) / old_qty).toFixed(decimal_point);

                const old_discount_amount = parseFloat($(`#discount-amount-${variant_key}`).text());
                const updated_discount_amount = ((old_discount_amount * updated_qty) / old_qty).toFixed(decimal_point);


                $(`#qty-${variant_key}`).val(updated_qty);
                $(`#total-cost-${variant_key}`).text(updated_total_cost);
                $(`#discount-amount-${variant_key}`).text(updated_discount_amount);

                toastr.success('{{translate('Added successfully')}}', {CloseButton: true, ProgressBar: true});
                return;
            }

            let query_string = 'service_id=' + service_id + '&variant_key=' + variant_key + '&quantity=' + quantity + '&zone_id=' + zone_id;
            $.ajax({
                type: 'GET',
                url: "{{route('provider.booking.service.ajax-get-service-info')}}" + '?' + query_string,
                data: {},
                processData: false,
                contentType: false,
                beforeSend: function () {
                    $('.preloader').show();
                },
                success: function (response) {
                    $("#service-edit-tbody").append(response.view);
                    toastr.success('{{translate('Added successfully')}}', {CloseButton: true, ProgressBar: true});
                },
                complete: function () {
                    $('.preloader').hide();
                },
            });
        })

        function removeServiceRow(row) {
            const row_count = $('#service-edit-tbody tr').length;
            if (row_count <= 1) {
                toastr.error('{{translate('Can not remove the only service')}}');
                return;
            }

            Swal.fire({
                title: "{{translate('are_you_sure')}}?",
                text: '{{translate('want to remove the service from the booking')}}',
                type: 'warning',
                showCloseButton: true,
                showCancelButton: true,
                cancelButtonColor: 'var(--c2)',
                confirmButtonColor: 'var(--c1)',
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Yes',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $(`#${row}`).remove();
                }
            })
        }
    </script>
@endpush
