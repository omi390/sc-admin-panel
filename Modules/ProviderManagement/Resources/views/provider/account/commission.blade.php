@extends('providermanagement::layouts.master')

@section('title',translate('Commission_Info'))

@push('css_or_js')

@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{translate('Account_Information')}}</h2>
            </div>

            <div class="mb-3">
                <ul class="nav nav--tabs nav--tabs__style2">
                    <li class="nav-item">
                        <a class="nav-link {{$pageType=='overview'?'active':''}}"
                           href="{{url()->current()}}?page_type=overview">{{translate('Overview')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$pageType=='commission-info'?'active':''}}"
                           href="{{url()->current()}}?page_type=commission-info">{{translate('Commission_Info')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$pageType=='review'?'active':''}}"
                           href="{{url()->current()}}?page_type=review">{{translate('Reviews')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$pageType=='promotional_cost'?'active':''}}"
                           href="{{url()->current()}}?page_type=promotional_cost">{{translate('Promotional_Cost')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$pageType=='withdraw_transaction'?'active':''}}"
                           href="{{route('provider.withdraw.list', ['page_type'=>'withdraw_transaction'])}}">{{translate('withdraw_list')}}</a>
                    </li>
                </ul>
            </div>

            <div class="card">
                <div class="card-body p-30">
                    <div class="media flex-wrap gap-3 align-items-center discount-type">
                        <div class="bg-light rounded position-relative p-5">
                            <h2 class="c1 fs-1">{{ $commission }}%</h2>
                            <div>{{translate('Commission')}}</div>
                        </div>

                        <div class="d-flex gap-2 align-items-center justify-content-between flex-wrap media-body">
                            <span>{{translate('Currently_you_are_using_business_commission_percentage_set_by_admin._If_you_want_to_change_the_percentage_please_contact_with_business_admin.')}}</span>
                            <a class="btn btn-primary" href="{{route('provider.chat.index', ['user_type' => 'super_admin'])}}">
                                <span class="material-icons">forum</span>{{translate('Conversation')}}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')


@endpush
