@extends('adminmodule::layouts.master')

@section('title',translate('provider_list'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-30">
                <h2 class="page-title">{{translate('Provider_List')}}</h2>
            </div>

            <div class="row justify-content-center">
                <div class="col-xl-10">
                    <div class="row mb-4 g-4">
                        <div class="col-lg-3 col-sm-6">
                            <div class="statistics-card statistics-card__total_provider">
                                <h2>{{$topCards['total_providers']}}</h2>
                                <h3>{{translate('Total_Providers')}}</h3>
                                <img src="{{asset('public/assets/admin-module/img/icons/subscribed-providers.png')}}"
                                     class="absolute-img" alt="{{ translate('providers') }}">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="statistics-card statistics-card__ongoing">
                                <h2>{{$topCards['total_onboarding_requests']}}</h2>
                                <h3>{{translate('Onboarding_Request')}}</h3>
                                <img src="{{asset('public/assets/admin-module/img/icons/onboarding-request.png')}}"
                                     class="absolute-img" alt="{{ translate('providers') }}">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="statistics-card statistics-card__newly_joined">
                                <h2>{{$topCards['total_active_providers']}}</h2>
                                <h3>{{translate('Active_Providers')}}</h3>
                                <img src="{{asset('public/assets/admin-module/img/icons/newly-joined.png')}}"
                                     class="absolute-img" alt="{{ translate('providers') }}">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="statistics-card statistics-card__not_served">
                                <h2>{{$topCards['total_inactive_providers']}}</h2>
                                <h3>{{translate('Inactive_Providers')}}</h3>
                                <img src="{{asset('public/assets/admin-module/img/icons/not-served.png')}}"
                                     class="absolute-img" alt="{{ translate('providers') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                <ul class="nav nav--tabs">
                    <li class="nav-item">
                        <a class="nav-link {{$status=='all'?'active':''}}"
                           href="{{url()->current()}}?status=all">
                            {{translate('all')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$status=='active'?'active':''}}"
                           href="{{url()->current()}}?status=active">
                            {{translate('active')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$status=='inactive'?'active':''}}"
                           href="{{url()->current()}}?status=inactive">
                            {{translate('inactive')}}
                        </a>
                    </li>
                </ul>

                <div class="d-flex gap-2 fw-medium">
                    <span class="opacity-75">{{translate('Total_Providers')}}:</span>
                    <span class="title-color">{{$providers->total()}}</span>
                </div>
            </div>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="all-tab-pane">
                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                <form action="{{url()->current()}}?status={{$status}}"
                                      class="search-form search-form_style-two"
                                      method="POST">
                                    @csrf
                                    <div class="input-group search-form__input_group">
                                            <span class="search-form__icon">
                                                <span class="material-icons">search</span>
                                            </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                               value="{{$search}}" name="search"
                                               placeholder="{{translate('search_here')}}">
                                    </div>
                                    <button type="submit"
                                            class="btn btn--primary">{{translate('search')}}</button>
                                </form>

                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    @can('provider_export')
                                        <div class="dropdown">
                                            <button type="button"
                                                    class="btn btn--secondary text-capitalize dropdown-toggle"
                                                    data-bs-toggle="dropdown">
                                                <span
                                                    class="material-icons">file_download</span> {{translate('download')}}
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                <a class="dropdown-item"
                                                   href="{{route('admin.provider.download')}}?search={{$search}}">
                                                    {{translate('excel')}}
                                                </a>
                                            </ul>
                                        </div>
                                    @endcan

                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="example" class="table align-middle">
                                    <thead class="align-middle">
                                    <tr>
                                        <th>{{translate('Sl')}}</th>
                                        <th>{{translate('Provider')}}</th>
                                        <th>{{translate('Contact_Info')}}</th>
                                        <th>{{translate('Total_Subscribed_Sub_Categories')}}</th>
                                        <th>{{translate('Total_Booking_Served')}}</th>
                                        @can('provider_manage_status')
                                            <th>{{translate('Service Availability')}}</th>
                                            <th>{{translate('Status')}}</th>
                                        @endcan
                                        @canany(['provider_delete', 'provider_update'])
                                            <th>{{translate('Action')}}</th>
                                        @endcan
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @php
                                        $ongoingBookings = 0;
                                        $acceptedBookings = 0;
                                    @endphp
                                    @foreach($providers as $key => $provider)
                                        <tr>
                                            <td>{{$key+$providers->firstItem()}}</td>
                                            <td>
                                                <div class="media align-items-center gap-3">
                                                    <div class="avatar avatar-lg">
                                                        <a href="{{route('admin.provider.details',[$provider->id, 'web_page'=>'overview'])}}">
                                                            <img class="avatar-img radius-5" src="{{ $provider->logo_full_path }}" alt="{{ translate('provider-logo') }}">
                                                        </a>
                                                    </div>
                                                    <div class="media-body">
                                                        <h5 class="mb-1">
                                                            <a href="{{route('admin.provider.details',[$provider->id, 'web_page'=>'overview'])}}&provider={{ $provider->id}}">
                                                                {{$provider->company_name}}
                                                                @if($provider?->is_suspended && business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values)
                                                                    <span
                                                                        class="text-danger fz-12">{{('(' . translate('Suspended') . ')')}}</span>
                                                                @endif

                                                            </a>
                                                        </h5>
                                                        <span
                                                            class="common-list_rating d-flex align-items-center gap-1">
                                                            <span class="material-icons">star</span>
                                                            {{$provider->avg_rating}}
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <h5 class="mb-1">{{Str::limit($provider->contact_person_name, 30)}}</h5>
                                                    <a class="fz-12"
                                                       href="mobileto:{{$provider->contact_person_phone}}">{{$provider->contact_person_phone}}</a>
                                                    <a class="fz-12"
                                                       href="mobileto:{{$provider->contact_person_email}}">{{$provider->contact_person_email}}</a>
                                                </div>
                                            </td>
                                            <td>
                                                <p>{{$provider->subscribed_services_count}}</p>
                                            </td>
                                            <td>{{$provider->bookings_count}}</td>
                                            @can('provider_manage_status')
                                                <td>
                                                    <label class="switcher" data-bs-toggle="modal"
                                                           data-bs-target="#deactivateAlertModal">
                                                        <input class="switcher_input route-alert"
                                                               data-route="{{route('admin.provider.service_availability', [$provider->id])}}"
                                                               data-message="{{translate('want_to_update_status')}}"
                                                               type="checkbox" {{$provider->service_availability?'checked':''}}>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>


                                                <td>
                                                    <label class="switcher" data-bs-toggle="modal"
                                                           data-bs-target="#deactivateAlertModal">
                                                        <input class="switcher_input route-alert"
                                                               data-route="{{route('admin.provider.status_update', [$provider->id])}}"
                                                               data-message="{{translate('want_to_update_status')}}"
                                                               type="checkbox" {{$provider?->owner?->is_active?'checked':''}}>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>
                                            @endcan
                                            @canany(['provider_delete', 'provider_update'])
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        @can('provider_update')
                                                            <a href="{{route('admin.provider.edit',[$provider->id])}}"
                                                               class="action-btn btn--light-primary"
                                                               style="--size: 30px">
                                                                <span class="material-icons">edit</span>
                                                            </a>
                                                        @endcan
                                                        @php
                                                            $maxBookingAmount = business_config('max_booking_amount', 'booking_setup')->live_values; @endphp
                                                        @can('provider_delete')
                                                            <button type="button"
                                                                    class="action-btn btn--danger provider-delete"
                                                                    style="--size: 30px"
                                                                    data-provider="delete-{{$provider->id}}"
                                                                    data-ongoing="{{$provider->bookings->where('booking_status', 'ongoing')->count() ?? 0}}"
                                                                    data-accepted="{{$provider->bookings->where('booking_status', 'accepted')
                                                            ->where('provider_id', $provider->id)
                                                            ->when($maxBookingAmount > 0, function ($query) use ($maxBookingAmount) {
                                                                $query->where(function ($query) use ($maxBookingAmount) {
                                                                    $query->where('payment_method', 'cash_after_service')
                                                                        ->where(function ($query) use ($maxBookingAmount) {
                                                                            $query->where('total_booking_amount', '<=', $maxBookingAmount)
                                                                                ->orWhere('is_verified', 1);
                                                                        })
                                                                        ->orWhere('payment_method', '<>', 'cash_after_service');
                                                                });
                                                            })->count() ?? 0}}"
                                                                    data-url="{{route('admin.provider.details',[$provider->id, 'web_page'=>'bookings'])}}">
                                                                <span class="material-symbols-outlined">delete</span>
                                                            </button>
                                                            <form
                                                                action="{{route('admin.provider.delete',[$provider->id])}}"
                                                                method="post" id="delete-{{$provider->id}}"
                                                                class="hidden">
                                                                @csrf
                                                                @method('DELETE')
                                                            </form>
                                                        @endcan
                                                    </div>
                                                </td>
                                            @endcan
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                {!! $providers->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header pb-0 border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-sm-5 px-sm-5">
                    <div class="d-flex flex-column align-items-center gap-2 text-center">
                        <img src="{{ asset('/public/assets/provider-module/img/profile-delete.png') }}" alt="">
                        <h3>{{ translate('Sorry you can’t delete this provider account!') }}</h3>
                        <p class="fw-medium">{{ translate('Provider must have to complete the ongoing and accepted bookings.') }}</p>
                        <a href="#" id="bookingRequestLink">
                            <button type="reset" class="btn btn--primary">{{ translate('Booking Request') }}</button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script>

        $('.provider-delete').on('click', function () {
            let provider = $(this).data('provider');
            let url = $(this).data('url');
            let accepted = $(this).data('accepted');
            let ongoing = $(this).data('ongoing');
            let message = "{{ translate('want_to_delete_your_account') }}";

            if ('{{ env('APP_ENV') == 'demo' }}') {
                toastr.info('This function is disabled for demo mode', {
                    closeButton: true,
                    progressBar: true
                });
            } else {
                if (accepted !== 0 || ongoing !== 0) {
                    $('#exampleModal').data('url', url);
                    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('exampleModal'));
                    modal.show();
                } else {
                    form_alert(provider, message);
                }
            }
        });

        $('#exampleModal').on('show.bs.modal', function (event) {
            let url = $(this).data('url');
            $('#bookingRequestLink').attr('href', url);
        });

    </script>
@endpush
