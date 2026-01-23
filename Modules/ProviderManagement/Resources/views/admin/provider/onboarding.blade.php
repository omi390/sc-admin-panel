@extends('adminmodule::layouts.master')

@section('title',translate('onboarding_requests'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{translate('Onboarding_Request')}}</h2>
            </div>

            <div
                class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                <ul class="nav nav--tabs">
                    <li class="nav-item">
                        <a class="nav-link {{$status=='onboarding'?'active':''}}"
                           href="{{url()->current()}}?status=onboarding">
                            {{translate('Onboarding_Requests')}}
                            <sup class="c2-bg py-1 px-2 radius-50 text-white">{{$providersCount['onboarding']}}</sup>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$status=='denied'?'active':''}}"
                           href="{{url()->current()}}?status=denied">
                            {{translate('Denied_Requests')}}
                            <sup class="c2-bg py-1 px-2 radius-50 text-white">{{$providersCount['denied']}}</sup>
                        </a>
                    </li>
                </ul>

                <div class="d-flex gap-2 fw-medium">
                    <span class="opacity-75">{{translate('Total_Requests')}}:</span>
                    <span class="title-color">{{$providers->total()}}</span>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                        <form action="{{url()->current()}}"
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
                            <thead>
                            <tr>
                                <th>{{translate('SL')}}</th>
                                <th>{{translate('Provider')}}</th>
                                <th>{{translate('Contact_Info')}}</th>
                                <th>{{translate('Zone')}}</th>
                                @can('onboarding_request_approve_or_deny')
                                    <th class="text-center">{{translate('Action')}}</th>
                                @endcan
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($providers as $key=>$provider)
                                <tr>
                                    <td>{{$providers->firstitem()+$key}}</td>
                                    <td>
                                        <a class="media align-items-center gap-2"
                                           href="{{route('admin.provider.onboarding_details',[$provider->id])}}">
                                            <img class="avatar avatar-lg radius-5"
                                                 alt="{{ translate('image') }}"
                                                 src="{{$provider->logo_full_path}}">
                                            <h5 class="media-body">
                                                {{Str::limit($provider->company_name, 30)}}
                                            </h5>
                                        </a>
                                    </td>
                                    <td>
                                        <div>
                                            <h5 class="mb-2">{{$provider->contact_person_name}}</h5>
                                            <a class="d-flex fz-12"
                                               href="tel:{{$provider->contact_person_phone}}">{{$provider->contact_person_phone}}</a>
                                            <a class="d-flex fz-12"
                                               href="mailto:{{$provider->contact_person_email}}">{{$provider->contact_person_email}}</a>
                                        </div>
                                    </td>
                                    <td>
                                        @if($provider->zone)
                                            {{$provider->zone->name}}
                                        @else
                                            <div
                                                class="fz-12 badge badge-danger opacity-50">{{translate('Zone is not available')}}</div>
                                        @endif
                                    </td>
                                    @can('onboarding_request_approve_or_deny')
                                        <td>
                                            <div class="table-actions justify-content-center">
                                                @if($provider->is_approved != 0)
                                                    <a type="button"
                                                       class="btn btn-soft--danger text-capitalize provider_approval"
                                                       id="button-deny-{{$provider->id}}"
                                                       data-approve="{{$provider->id}}"
                                                       data-status="deny">
                                                        {{translate('Deny')}}
                                                    </a>
                                                @endif
                                                <a type="button"
                                                   class="btn btn--success text-capitalize approval_provider"
                                                   id="button-{{$provider->id}}" data-approve="{{$provider->id}}"
                                                   data-approve="approve">
                                                    {{translate('Accept')}}
                                                </a>
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
@endsection

@push('script')
    <script>
        "use strict";

        $('.provider_approval').on('click', function () {
            let itemId = $(this).data('approve');
            let route = '{{ route('admin.provider.update-approval', ['id' => ':itemId', 'status' => 'deny']) }}';
            route = route.replace(':itemId', itemId);
            route_alert_reload(route, '{{ translate('want_to_deny_the_provider') }}', true);
        });

        $('.approval_provider').on('click', function () {
            let itemId = $(this).data('approve');
            let route = '{{ route('admin.provider.update-approval', ['id' => ':itemId', 'status' => 'approve']) }}';
            route = route.replace(':itemId', itemId);
            route_alert_reload(route, '{{ translate('want_to_approve_the_provider') }}', true);
        });

    </script>

@endpush
