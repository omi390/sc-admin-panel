@extends('providermanagement::layouts.master')

@section('title',translate('Withdraw'))

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
                           href="{{route('provider.account_info', ['page_type'=>'overview'])}}">{{translate('Overview')}}</a>
                    </li>
                    @if(!$packageSubscriber)
                        <li class="nav-item">
                            <a class="nav-link {{$pageType=='commission-info'?'active':''}}"
                               href="{{url()->current()}}?page_type=commission-info">{{translate('Commission_Info')}}</a>
                        </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link {{$pageType=='review'?'active':''}}"
                           href="{{route('provider.account_info', ['page_type'=>'review'])}}">{{translate('Reviews')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$pageType=='promotional_cost'?'active':''}}"
                           href="{{route('provider.account_info', ['page_type'=>'promotional_cost'])}}">{{translate('Promotional_Cost')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$pageType=='withdraw_transaction'?'active':''}}"
                           href="{{route('provider.withdraw.list', ['page_type'=>'withdraw_transaction'])}}">{{translate('withdraw_list')}}</a>
                    </li>
                </ul>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                        <form action="{{url()->current()}}?page_type={{$pageType}}"
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
                            <button type="submit" class="btn btn--primary">
                                {{translate('search')}}
                            </button>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table id="example" class="table align-middle">
                            <thead class="text-nowrap">
                                <tr>
                                    <th>{{translate('SL')}}</th>
                                    <th>{{translate('Provide_Note')}}</th>
                                    <th>{{translate('Total_Amount')}}</th>
                                    <th>{{translate('Admin_Note')}}</th>
                                    <th>{{translate('Requested_at')}}</th>
                                    <th>{{translate('Status')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($withdrawRequests as $key=>$withdrawRequest)
                                <tr>
                                    <td>{{$withdrawRequests->firstitem()+$key}}</td>
                                    <td>{{$withdrawRequest->note}}</td>
                                    <td>{{$withdrawRequest->amount}}</td>
                                    <td>
                                        <div title="{{$withdrawRequest->admin_note}}">{{$withdrawRequest->admin_note}}</div>
                                    </td>
                                    <td>
                                        <div>{{date('d-M-y',strtotime($withdrawRequest->created_at))}}</div>
                                        <div>{{date('H:i a',strtotime($withdrawRequest->created_at))}}</div>
                                    </td>
                                    <td>
                                        <span class="badge badge badge-success">
                                            {{translate($withdrawRequest->request_status)}}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end">
                        {!! $withdrawRequests->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
