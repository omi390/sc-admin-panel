@extends('adminmodule::layouts.master')

@section('title',translate('notification_channel'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('assets/admin-module')}}/plugins/select2/select2.min.css"/>
    <link rel="stylesheet" href="{{asset('assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="{{asset('assets/admin-module')}}/plugins/dataTables/select.dataTables.min.css"/>
    <link rel="stylesheet" href="{{asset('assets/admin-module')}}/plugins/swiper/swiper-bundle.min.css"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap d-flex justify-content-between align-items-center mb-3">
                <div class="">
                    <h2 class="page-title">{{translate('Notification Channels Setup')}}</h2>
                    <p class="mt-1">
                        {{translate('From here Admins can configure which notifications users receive and through which channels (e.g., Email, SMS, Push notification)')}}
                    </p>
                </div>
            </div>

            @can('configuration_manage_status')
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
                    <ul class="nav nav--tabs nav--tabs__style2">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->get('notification_type') == 'user' ? 'active' : '' }}" href="{{ url()->current() }}?notification_type=user">
                                Customer
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->get('notification_type') == 'provider' ? 'active' : '' }}" href="{{ url()->current() }}?notification_type=provider">
                                Provider
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->get('notification_type') == 'serviceman' ? 'active' : '' }}" href="{{ url()->current() }}?notification_type=serviceman">
                                Serviceman
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between align-items-center">
                            <div class="d-flex gap-2 fw-medium me-auto">
                                {{translate('Notifications')}}:
                            </div>
                            <form action="{{ url()->current() }}" class="search-form search-form_style-two" method="get">
                                <div class="input-group search-form__input_group">
                            <span class="search-form__icon">
                                <span class="material-icons">search</span>
                            </span>
                                    <input type="search" class="theme-input-style search-form__input" name="search" value="{{ request()->search }}" placeholder="{{translate('search_here')}}">
                                </div>
                                <input type="hidden" name="notification_type" value="{{ request()->get('notification_type') }}">

                                <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                            </form>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="text-nowrap">
                                <tr>
                                    <th>{{translate('Sl')}}</th>
                                    <th>{{translate('Topics')}}</th>
                                    <th>{{translate('Push Notification')}}</th>
                                    <th>{{translate('Mail')}}</th>
                                    <th>{{translate('SMS')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($notificationSetup as $key => $notification)
                                        <?php
                                        $data = json_decode($notification->value);
                                        $email = $data->email;
                                        $sms = $data->sms;
                                        $notificationSetting = $data->notification;
                                        ?>
                                    <tr>
                                        <td>{{$key+1}}</td>
                                        <td class="py-3">
                                            <h5 class="mb-1">{{translate($notification->title)}}</h5>
                                            <p class="text-secondary">{{translate($notification->sub_title)}}</p>
                                        </td>
                                        <td>
                                            @if(is_null($notificationSetting))
                                                N/A
                                            @else
                                                <label class="switcher">
                                                    <input class="switcher_input" name="notification" type="checkbox" data-id="{{ $notification->id }}" data-type="notification" {{ $notificationSetting ? 'checked' : '' }}>
                                                    <span class="switcher_control"></span>
                                                </label>
                                            @endif
                                        </td>
                                        <td>
                                            @if(is_null($email))
                                                N/A
                                            @else
                                                <label class="switcher">
                                                    <input class="switcher_input" name="email" type="checkbox" data-id="{{ $notification->id }}" data-type="email" {{ $email ? 'checked' : '' }}>
                                                    <span class="switcher_control"></span>
                                                </label>
                                            @endif
                                        </td>
                                        <td>
                                            @if(is_null($sms))
                                                N/A
                                            @else
                                                <label class="switcher">
                                                    <input class="switcher_input" name="sms" type="checkbox" data-id="{{ $notification->id }}" data-type="sms" {{ $sms ? 'checked' : '' }}>
                                                    <span class="switcher_control"></span>
                                                </label>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endcan
        </div>
    </div>
@endsection

@push('script')
    <script src="{{asset('assets/admin-module')}}/plugins/select2/select2.min.js"></script>
    <script src="{{asset('assets/admin-module')}}/plugins/swiper/swiper-bundle.min.js"></script>
    <script src="{{asset('assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.js"></script>
    <script src="{{asset('assets/admin-module')}}/plugins/dataTables/dataTables.select.min.js"></script>

    <script>
        "use strict";

        $('.switcher_input').change(function() {
            var id = $(this).data('id');
            var type = $(this).data('type');
            var status = $(this).is(':checked') ? 1 : 0;

            $.ajax({
                url: '{{ route("admin.business-settings.updateNotificationStatus") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    [type]: status
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('{{translate('successfully_updated')}}')
                    } else {
                        toastr.error('{{translate('something worng')}}')
                    }
                }
            });
        });
    </script>
@endpush
