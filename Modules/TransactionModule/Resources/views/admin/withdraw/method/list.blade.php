@extends('adminmodule::layouts.master')

@section('title',translate('withdrawal_method_list'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div
                        class="page-title-wrap d-flex justify-content-between flex-wrap align-items-center gap-3 mb-3">
                        <h2 class="page-title">{{translate('Withdrawal_method_List')}}</h2>
                        @can('withdraw_add')
                            <a href="{{route('admin.withdraw.method.create')}}"
                               class="btn btn--primary">+ {{translate('Add_method')}}</a>
                        @endcan
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
                                    </div>

                                    <div class="table-responsive">
                                        <table id="example" class="table align-middle">
                                            <thead class="text-nowrap">
                                            <tr>
                                                <th>{{translate('SL')}}</th>
                                                <th>{{translate('Method_name')}}</th>
                                                <th>{{translate('Method_Fields')}}</th>
                                                @can('withdraw_manage_status')
                                                    <th>{{translate('Active_Status')}}</th>
                                                    <th>{{translate('Default_Method')}}</th>
                                                @endcan
                                                @canany(['withdraw_delete', 'withdraw_update'])
                                                    <th>{{translate('Action')}}</th>
                                                @endcan
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($withdrawalMethods as $key=>$withdrawalMethod)
                                                <tr>
                                                    <td>{{$withdrawalMethods->firstitem()+$key}}</td>
                                                    <td>{{$withdrawalMethod['method_name']}}</td>
                                                    <td>
                                                        @foreach($withdrawalMethod['method_fields'] as $key=>$methodField)
                                                            <span
                                                                class="badge badge-success opacity-75 fz-12 border border-white">
                                                            <b>{{translate('Name')}}:</b> {{translate($methodField['input_name'])}} |
                                                            <b>{{translate('Type')}}:</b> {{ $methodField['input_type'] }} |
                                                            <b>{{translate('Placeholder')}}:</b> {{ $methodField['placeholder'] }} |
                                                            <b>{{translate('Is Required')}}:</b> {{ $methodField['is_required'] ? translate('yes') : translate('no') }}
                                                        </span><br/>
                                                        @endforeach
                                                    </td>
                                                    @can('withdraw_manage_status')
                                                        <td>
                                                            <label class="switcher">
                                                                <input class="switcher_input route-alert-reload"
                                                                       data-route="{{route('admin.withdraw.method.status-update',[$withdrawalMethod->id])}}"
                                                                       data-message="{{translate('want_to_update_status')}}"
                                                                       type="checkbox" {{$withdrawalMethod->is_active?'checked':''}}>
                                                                <span class="switcher_control"></span>
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="switcher">
                                                                <input class="switcher_input route-alert-reload"
                                                                       data-route="{{route('admin.withdraw.method.default-status-update',[$withdrawalMethod->id])}}"
                                                                       data-message="{{translate('want_to_make_default_method')}}"
                                                                       type="checkbox" {{$withdrawalMethod->is_default?'checked':''}}>
                                                                <span class="switcher_control"></span>
                                                            </label>
                                                        </td>
                                                    @endcan
                                                    @canany(['withdraw_delete', 'withdraw_update'])
                                                        <td>
                                                            <div class="d-flex gap-2">
                                                                @can('withdraw_update')
                                                                    <a href="{{route('admin.withdraw.method.edit',[$withdrawalMethod->id])}}"
                                                                       class="action-btn btn--light-primary demo_check"
                                                                       style="--size: 30px">
                                                                        <span class="material-icons">edit</span>
                                                                    </a>
                                                                @endcan

                                                                @can('withdraw_delete')
                                                                    @if(!$withdrawalMethod->is_default)
                                                                        <button type="button"
                                                                                class="action-btn btn--danger demo_check form-alert"
                                                                                style="--size: 30px"
                                                                                data-id="delete-{{$withdrawalMethod->id}}"
                                                                                data-message="{{translate('want_to_delete_this_method')}}?"
                                                                                data-bs-toggle="modal"
                                                                                data-bs-target="#deleteAlertModal"
                                                                        >
                                                                    <span
                                                                        class="material-symbols-outlined">delete</span>
                                                                        </button>
                                                                        <form
                                                                            action="{{route('admin.withdraw.method.delete',[$withdrawalMethod->id])}}"
                                                                            method="post"
                                                                            id="delete-{{$withdrawalMethod->id}}"
                                                                            class="hidden">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                        </form>
                                                                    @endif
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
                                        {!! $withdrawalMethods->links() !!}
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

