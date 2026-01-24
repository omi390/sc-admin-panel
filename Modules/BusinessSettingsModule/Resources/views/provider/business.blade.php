@extends('providermanagement::layouts.master')

@section('title',translate('business_settings'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/select.dataTables.min.css"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('business_settings')}}</h2>
                    </div>

                    <div class="mb-3">
                        <ul class="nav nav--tabs nav--tabs__style2">
                            <li class="nav-item">
                                <a href="{{url()->current()}}?web_page=service_availability"
                                   class="nav-link {{$webPage=='service_availability'?'active':''}}">
                                    {{translate('Service Availability')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{url()->current()}}?web_page=bookings"
                                   class="nav-link {{$webPage=='bookings'?'active':''}}">
                                    {{translate('bookings')}}
                                </a>
                            </li>
                        </ul>
                    </div>

                    @if($webPage=='service_availability')
                        <div class="tab-content">
                            <div class="tab-pane fade {{$webPage=='service_availability'?'active show':''}}">
                                <div class="card mb-3">
                                    <div class="card-body p-30">
                                        <div
                                            class="border border-primary rounded d-flex justify-content-between p-3 align-items-center">
                                            <div
                                                class="text-capitalize text-primary">{{translate('Service Availability Mode')}}</div>
                                            <label class="switcher m-0">
                                                <input class="switcher_input service-availability"
                                                       type="checkbox" {{Auth::user()->provider->service_availability == '1' ? 'checked' : ''}}>
                                                <span class="switcher_control"></span>
                                            </label>
                                        </div>
                                        <span
                                            class="mt-2 d-block">* {{translate('By turning off availability mode, you will not get any new booking request from customers and customer will that you are currently unavailable  to provide service')}} </span>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="subtitle text-body">
                                            <span class="material-icons translate-y-4px">event_note</span>
                                            <span
                                                class="mb-2">{{translate('Service Provider Availability Schedules')}}</span>
                                            <i class="material-icons px-1 translate-y-4px" data-bs-toggle="tooltip"
                                               data-bs-placement="top" title="{{translate('Using the current time slot, the system shows your availability to customers in app & web, enabling them to make successful bookings')}}">info</i>
                                        </h5>
                                    </div>
                                    <div class="card-body p-30">
                                        <form action="{{route('provider.business-settings.availability-schedule')}}" method="POST">
                                            @csrf
                                            @method('PUT')

                                            <div class="__schedule-item">
                                                <div class="subtitle">
                                                    {{translate('Service Providing Time')}}
                                                </div>
                                                <div class="__schedule-area">
                                                    <div class="clock">
                                                        <div class="clock__item">
                                                            <div class="clock__item__title">
                                                                {{translate('From')}}
                                                            </div>
                                                            <div class="clock__item__input">
                                                                <input type="time" name="start_time" class="form-control" value="{{isset($timeSchedule['start_time']) ? $timeSchedule['start_time'] : ''}}">
                                                            </div>
                                                        </div>
                                                        <div class="clock__item">
                                                            <div class="clock__item__title">
                                                                {{translate('Till')}}
                                                            </div>
                                                            <div class="clock__item__input">
                                                                <input type="time" name="end_time" class="form-control" value="{{isset($timeSchedule['end_time']) ? $timeSchedule['end_time'] : ''}}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <hr class="__border"/>

                                            <div class="__schedule-item">
                                                <div class="subtitle">
                                                    {{ translate('Weekend') }}
                                                </div>
                                                <div class="__schedule-area">
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach(['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'] as $day)
                                                            <label class="form-check m-0 me-xl-3">
                                                                <input type="checkbox" class="form-check-input" name="day[]" value="{{ $day }}" {{ in_array($day, $weekEnds ?? []) ? 'checked' : '' }}>
                                                                <span class="form-check-label">{{ translate(ucfirst($day)) }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex gap-2 justify-content-end mt-4">
                                                <button type="reset" class="btn btn-secondary">{{translate('reset')}}
                                                </button>
                                                <button type="submit" class="btn btn--primary">{{translate('update')}}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($webPage=='bookings')
                        <div class="tab-content">
                            <div class="tab-pane fade {{$webPage=='bookings'?'active show':''}}">
                                <div class="card">
                                    <div class="card-body p-30">
                                        <form action="{{route('provider.business-settings.set-business-information')}}"
                                              method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="row g-4">
                                                @php($servicemanCanBookingCancel = collect([ ['key' => 'provider_serviceman_can_cancel_booking','info_message' => 'Service Men Can Cancel Booking', 'title' => 'Cancel Booking Req'] ]))
                                                @php($servicemanCanBookingEdit = collect([ ['key' => 'provider_serviceman_can_edit_booking','info_message' => 'Service Men Can Edit Booking', 'title' => 'Edit Booking Req'] ]))

                                                <div class="col-md-6 col-12">
                                                    <div class="border p-3 rounded d-flex justify-content-between">
                                                        <div>
                                                            <span
                                                                class="text-capitalize">{{translate($servicemanCanBookingCancel[0]['title'])}}</span>
                                                            <i class="material-icons px-1" data-bs-toggle="tooltip"
                                                               data-bs-placement="top"
                                                               title="{{translate($servicemanCanBookingCancel[0]['info_message'] ?? '')}}"
                                                            >info</i>
                                                        </div>
                                                        <label class="switcher">
                                                            @php($value = $dataValues->where('key_name', $servicemanCanBookingCancel[0]['key'])->where('settings_type', 'serviceman_config')->where('provider_id', $providerId)?->first()?->live_values ?? null)
                                                            <input class="switcher_input switcher-btn"
                                                                   id="{{$servicemanCanBookingCancel[0]['key']}}"
                                                                   type="checkbox"
                                                                   name="{{$servicemanCanBookingCancel[0]['key']}}"
                                                                   value="1" {{$value ? 'checked' : ''}}
                                                                   data-id="{{$servicemanCanBookingCancel[0]['key']}}"
                                                                   data-message="{{ucfirst(translate($servicemanCanBookingCancel[0]['key']))}}">
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-12">
                                                    <div class="border p-3 rounded d-flex justify-content-between">
                                                        <div>
                                                            <span
                                                                class="text-capitalize">{{translate($servicemanCanBookingEdit[0]['title'])}}</span>
                                                            <i class="material-icons px-1" data-bs-toggle="tooltip"
                                                               data-bs-placement="top"
                                                               title="{{translate($servicemanCanBookingEdit[0]['info_message'] ?? '')}}"
                                                            >info</i>
                                                        </div>
                                                        <label class="switcher">
                                                            @php($value = $dataValues->where('key_name', $servicemanCanBookingEdit[0]['key'])->where('settings_type', 'serviceman_config')->where('provider_id', $providerId)?->first()?->live_values ?? null)
                                                            <input class="switcher_input switcher-btn"
                                                                   id="{{$servicemanCanBookingEdit[0]['key']}}"
                                                                   type="checkbox"
                                                                   name="{{$servicemanCanBookingEdit[0]['key']}}"
                                                                   value="1" {{$value ? 'checked' : ''}}
                                                                   data-id="{{$servicemanCanBookingEdit[0]['key']}}"
                                                                   data-message="{{ucfirst(translate($servicemanCanBookingEdit[0]['key']))}}">
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="d-flex gap-2 justify-content-end mt-4">
                                                <button type="reset" class="btn btn-secondary">{{translate('reset')}}
                                                </button>
                                                <button type="submit" class="btn btn--primary">{{translate('update')}}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/dataTables.select.min.js"></script>

    <script>
        "use strict";

        $('.switcher-btn').on('click', function () {
            let id = $(this).data('id');
            let status = $(this).is(':checked') === true ? 1 : 0;
            let message = $(this).data('message');
            switch_alert(id, status, message)
        });

        $('.service-availability').on('click', function () {
            @if(env('APP_ENV')!='demo')
                updateAvailability($(this).is(':checked')===true?1:0)
            @endif
        });

        function switch_alert(id, status, message) {
            Swal.fire({
                title: "{{translate('are_you_sure')}}?",
                text: message,
                type: 'warning',
                showDenyButton: true,
                showCancelButton: true,
                denyButtonColor: 'var(--c2)',
                confirmButtonColor: 'var(--c1)',
                confirmButtonText: 'Save',
                denyButtonText: `Don't save`,
            }).then((result) => {
                if (result.value) {
                } else {
                    if (status === 1) $(`#${id}`).prop('checked', false);
                    if (status === 0) $(`#${id}`).prop('checked', true);

                    Swal.fire('{{translate('Changes are not saved')}}', '', 'info')
                }
            })
        }

        function updateAvailability(status) {
            Swal.fire({
                title: "{{translate('are_you_sure')}}?",
                text: '{{translate('want_to_update_status')}}',
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
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{route('provider.business-settings.availability-status')}}",
                        data: {
                            service_availability: status,
                        },
                        type: 'put',
                        success: function (response) {
                            toastr.success('{{translate('successfully_updated')}}')
                        },
                        error: function () {

                        }
                    });
                }
            })
        }

        $(document).ready(function () {
            $('.js-select').select2();
        });
    </script>
@endpush
