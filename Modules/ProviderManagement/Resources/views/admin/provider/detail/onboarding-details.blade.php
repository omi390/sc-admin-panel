@extends('adminmodule::layouts.master')

@section('title',translate('provider_preview'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex align-items-center flex-wrap-reverse justify-content-between gap-3 mb-3">
                <div class="page-title-wrap mb-3">
                    <h2 class="page-title mb-1">{{translate('Provider_Preview')}}</h2>
                    <p>{{translate('Requested to join at 12')}} {{date('d-M-Y h:ia',strtotime($provider->created_at))}}</p>
                </div>
                <div class="d-flex align-items-center flex-wrap gap-3">
                    @can('onboarding_request_update')
                        <a href="{{route('admin.provider.edit',[$provider->id])}}" class="btn btn--primary">
                            <span class="material-icons">border_color</span>
                            {{translate('Edit & Approve')}}
                        </a>
                    @endcan

                    @can('onboarding_request_approve_or_deny')
                        @if($provider->is_approved == 2)
                            <a type="button"
                               class="btn btn-danger text-capitalize provider_approval"
                               id="button-deny-{{$provider->id}}" data-approve="{{$provider->id}}"
                               data-status="deny">
                                {{translate('Reject')}}
                            </a>
                        @endif
                        @if($provider->is_approved == 0 || $provider->is_approved == 2)
                            <a type="button" class="btn btn--success text-capitalize approval_provider"
                               id="button-{{$provider->id}}" data-approve="{{$provider->id}}"
                               data-approve="approve">
                                {{translate('Approve')}}
                            </a>
                        @endif
                    @endcan
                </div>
            </div>

            <div class="card">
                <div class="card-body p-30">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="information-details-box media flex-column flex-sm-row gap-20">
                                <img class="avatar-img radius-5"
                                     src="{{$provider->logo_full_path}}"
                                     alt="{{ translate('logo') }}">
                                <div class="media-body ">
                                    <h2 class="information-details-box__title">{{Str::limit($provider->company_name, 30)}}</h2>

                                    <ul class="contact-list">
                                        <li>
                                            <span class="material-symbols-outlined">phone_iphone</span>
                                            <a href="tel:{{$provider->company_phone}}">{{$provider->company_phone}}</a>
                                        </li>
                                        <li>
                                            <span class="material-symbols-outlined">mail</span>
                                            <a href="mailto:{{$provider->company_email}}">{{$provider->company_email}}</a>
                                        </li>
                                        <li>
                                            <span class="material-symbols-outlined">map</span>
                                            {{Str::limit($provider->company_address, 100)}}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="information-details-box h-100">
                                <h2 class="information-details-box__title c1 fw-medium">{{translate('Contact_Person_Information')}}
                                </h2>
                                <h3 class="information-details-box__subtitle">{{Str::limit($provider->contact_person_name, 30)}}</h3>

                                <ul class="contact-list">
                                    <li>
                                        <span class="material-symbols-outlined">phone_iphone</span>
                                        <a href="tel:{{$provider->contact_person_phone}}">{{$provider->contact_person_phone}}</a>
                                    </li>
                                    <li>
                                        <span class="material-symbols-outlined">mail</span>
                                        <a href="mailto:{{$provider->contact_person_email}}">{{$provider->contact_person_email}}</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="information-details-box">
                                <div class="border-bottom mb-4 d-flex align-items-center gap-2 pb-2">
                                    <span class="material-icons c1">image</span>
                                    <h2 class="information-details-box__title mb-0 c1">{{translate('Registration Info')}}
                                    </h2>
                                    <span class="material-symbols-outlined title-color" data-bs-toggle="tooltip"
                                          title="Tooltip on top">info</span>
                                </div>

                                <div class="row g-4">
                                    <div class="col-lg-3">
                                        <h4 class="mb-3">{{translate('Account Information')}}
                                        </h4>
                                        <p><strong
                                                class="text-capitalize">{{translate('Email')}} :
                                            </strong> {{$provider->owner->email}}</p>
                                    </div>
                                    <div class="col-lg-3">
                                        <h4 class="mb-3">{{translate('Business_Information')}}
                                        </h4>
                                        <p><strong
                                                class="text-capitalize">{{translate('Zone')}}
                                                :</strong> {{$provider?->zone?->name}}</p>
                                        <p><strong
                                                class="text-capitalize">{{translate('identity Type')}}
                                                :</strong> {{ucfirst(str_replace(['_', '-'], ' ', $provider->owner->identification_type))}}
                                        </p>
                                        <p><strong
                                                class="text-capitalize">{{translate('Identity Number')}}
                                                :</strong> {{$provider->owner->identification_number}}</p>
                                    </div>
                                    <div class="col-lg-6">
                                        <h4 class="mb-3">{{translate('Identity Image')}}</h4>
                                        <div class="d-flex flex-wrap gap-3 justify-content-lg-start mt-3">
                                            @if(isset($provider->owner->identification_image) && count($provider->owner->identification_image) > 0)
                                                @foreach($provider?->owner->identification_image_full_path as $key=>$image)
                                                    <div>
                                                        <img class="max-w320 rounded-4" src="{{$image}}" alt="{{ translate('identity-image') }}">
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
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
