@extends('adminmodule::layouts.master')

@section('title',translate('provider_details'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{translate('Provider_Details')}}</h2>
            </div>

            <div class="mb-3">
                <ul class="nav nav--tabs nav--tabs__style2">
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='overview'?'active':''}}"
                           href="{{url()->current()}}?web_page=overview">{{translate('Overview')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='subscribed_services'?'active':''}}"
                           href="{{url()->current()}}?web_page=subscribed_services">{{translate('Subscribed_Services')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='bookings'?'active':''}}"
                           href="{{url()->current()}}?web_page=bookings">{{translate('Bookings')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='serviceman_list'?'active':''}}"
                           href="{{url()->current()}}?web_page=serviceman_list">{{translate('Service_Man_List')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='settings'?'active':''}}"
                           href="{{url()->current()}}?web_page=settings">{{translate('Settings')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='bank_information'?'active':''}}"
                           href="{{url()->current()}}?web_page=bank_information">{{translate('Bank_Information')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='reviews'?'active':''}}"
                           href="{{url()->current()}}?web_page=reviews">{{translate('Reviews')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='subscription'?'active':''}}"
                           href="{{url()->current()}}?web_page=subscription&provider_id={{ request()->id }}">{{translate('Business Plan')}}</a>
                    </li>
                </ul>
            </div>

            <div class="card mb-30">
                <div class="card-body p-30">
                    <form action="{{route('admin.provider.commission_update', [$provider->id])}}" method="post">
                        @csrf
                        <div class="mb-3">{{translate('Commission_Settings')}}</div>
                        <div class="d-flex flex-wrap align-items-center gap-4 mb-30">
                            <div class="custom-radio">
                                <input type="radio" name="commission_status" id="default_commission"
                                       value="default" {{$provider->commission_status == 0 ? 'checked' : ''}}>
                                <label for="default_commission">{{translate('Use Default')}}</label>
                            </div>
                            <div class="custom-radio">
                                <input type="radio" name="commission_status" id="custom_commission"
                                       value="custom" {{$provider->commission_status == 1 ? 'checked' : ''}}>
                                <label for="custom_commission">{{translate('Set_Custom_Commission')}}</label>
                            </div>
                        </div>

                        <div class="form-floating {{$provider->commission_status == 0 ? 'd-none' : ''}}"
                             id="percentage">
                            <input type="number" min="0" max="100" step="any" class="form-control"
                                   placeholder="{{translate('Percentage')}}"
                                   id="percentage__input" name="custom_commission_value"
                                   value="{{$provider->commission_percentage}}"
                                {{$provider->commission_status == 1 ? 'required' : ''}}>
                            <label>{{translate('Percentage')}}</label>
                        </div>

                        @can('provider_manage_status')
                            <div class="d-flex justify-content-end mt-30">
                                <button type="submit" class="btn btn--primary">{{translate('Save')}}</button>
                            </div>
                        @endcan
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')

    <script>
        "use strict";

        $('#default_commission').click(function () {
            if ($('#default_commission').is(':checked')) {
                $('#percentage').addClass('d-none');
                $('#percentage__input').removeAttr('required');
            }
        });

        $('#custom_commission').click(function () {
            if ($('#custom_commission').is(':checked')) {
                $('#percentage').removeClass('d-none');
                $('#percentage__input').prop('required', true);
            }
        });
    </script>
@endpush
