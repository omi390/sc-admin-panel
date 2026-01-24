@extends('adminmodule::layouts.master')

@section('title',translate('campaigns'))

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
                        <h2 class="page-title">{{translate('campaigns')}}</h2>
                    </div>

                    <div
                        class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                        <ul class="nav nav--tabs">
                            <li class="nav-item">
                                <a class="nav-link {{$discountType=='all'?'active':''}}"
                                   href="{{url()->current()}}?discount_type=all">
                                    {{translate('all')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$discountType=='service'?'active':''}}"
                                   href="{{url()->current()}}?discount_type=service">
                                    {{translate('service_wise')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$discountType=='category'?'active':''}}"
                                   href="{{url()->current()}}?discount_type=category">
                                    {{translate('category_wise')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$discountType=='mixed'?'active':''}}"
                                   href="{{url()->current()}}?discount_type=mixed">
                                    {{translate('mixed')}}
                                </a>
                            </li>
                        </ul>

                        <div class="d-flex gap-2 fw-medium">
                            <span class="opacity-75">{{translate('Total_Campaigns')}}:</span>
                            <span class="title-color">{{$campaigns->total()}}</span>
                        </div>
                    </div>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="all-tab-pane">
                            <div class="card">
                                <div class="card-body">
                                    <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                        <form action="{{url()->current()}}?discount_type={{$discountType}}"
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
                                            @can('campaign_view')
                                                <div class="dropdown">
                                                    <button type="button"
                                                            class="btn btn--secondary text-capitalize dropdown-toggle"
                                                            data-bs-toggle="dropdown">
                                                    <span
                                                        class="material-icons">file_download</span> {{translate('download')}}
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                        <a class="dropdown-item"
                                                           href="{{route('admin.campaign.download')}}?search={{$search}}">
                                                            {{translate('excel')}}
                                                        </a>
                                                    </ul>
                                                </div>
                                            @endcan
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="example" class="table align-middle">
                                            <thead class="text-nowrap">
                                            <tr>
                                                <th>{{translate('Sl')}}</th>
                                                <th>{{translate('campaign_name')}}</th>
                                                <th>{{translate('discount_type')}}</th>
                                                <th>{{translate('discount_title')}}</th>
                                                <th>{{translate('Applicable On')}}</th>
                                                <th>{{translate('zones')}}</th>
                                                @can('campaign_manage_status')
                                                    <th>{{translate('status')}}
                                                @endcan
                                                @canany(['campaign_delete', 'campaign_update'])
                                                    <th>{{translate('action')}}</th>
                                                @endcan
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($campaigns as $key=>$item)
                                                <tr>
                                                    <td>{{$key+$campaigns->firstItem()}}</td>
                                                    <td>{{$item->campaign_name}}</td>
                                                    <td>{{$item->discount->discount_type}}</td>
                                                    <td>{{$item->discount->discount_title}}</td>
                                                    <td>
                                                        @if($item->discount->discount_type == 'category')
                                                            @if($item->discount->category_types && count($item->discount->category_types) > 0)
                                                                <b>{{translate('Category') . ' : '}}</b>
                                                                @foreach($item->discount->category_types as $key=>$type)
                                                                    <span
                                                                        class="opacity-75">{{$type->category?$type->category->name:''}}</span>
                                                                    {{$key < count($item->discount->category_types)-1 ? ',' : null}}
                                                                @endforeach
                                                            @endif
                                                        @elseif($item->discount->discount_type == 'service')
                                                            @if($item->discount->category_types && $item->discount->service_types)
                                                                <br/>
                                                            @endif

                                                            @if($item->discount->service_types && count($item->discount->service_types) > 0)
                                                                <b>{{translate('Service') . ' : '}}</b>
                                                                @foreach($item->discount->service_types as $key=>$type)
                                                                    @if($type->service)
                                                                        <a href="{{route('admin.service.detail',[$type->service->id])}}"
                                                                           class="opacity-75">{{$type->service->name}}</a>
                                                                        {{$key < count($item->discount->service_types)-1 ? ',' : null}}
                                                                    @endif
                                                                @endforeach
                                                            @endif
                                                        @else
                                                            @if($item->discount->category_types && count($item->discount->category_types) > 0)
                                                                <b>{{translate('Category') . ' : '}}</b>
                                                                @foreach($item->discount->category_types as $key=>$type)
                                                                    <span
                                                                        class="opacity-75">{{$type->category?$type->category->name:''}}</span>
                                                                    {{$key < count($item->discount->category_types)-1 ? ',' : null}}
                                                                @endforeach
                                                            @endif

                                                            @if($item->discount->category_types && $item->discount->service_types)
                                                                <br/>
                                                            @endif

                                                            @if($item->discount->service_types && count($item->discount->service_types) > 0)
                                                                <b>{{translate('Service') . ' : '}}</b>
                                                                @foreach($item->discount->service_types as $key=>$type)
                                                                    @if($type->service)
                                                                        <a href="{{route('admin.service.detail',[$type->service->id])}}"
                                                                           class="opacity-75">{{$type->service->name}}</a>
                                                                        {{$key < count($item->discount->service_types)-1 ? ',' : null}}
                                                                    @endif
                                                                @endforeach
                                                            @endif
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @foreach($item->discount->zone_types as $type)
                                                            {{$type->zone?$type->zone->name.',':''}}
                                                        @endforeach
                                                    </td>
                                                    @can('campaign_manage_status')
                                                        <td>
                                                            <label class="switcher">
                                                                <input class="switcher_input"
                                                                       data-status="{{$item->id}}"
                                                                       type="checkbox" {{$item->is_active?'checked':''}}>
                                                                <span class="switcher_control"></span>
                                                            </label>
                                                        </td>
                                                    @endcan
                                                    @canany(['campaign_delete', 'campaign_update'])
                                                        <td>
                                                            <div class="d-flex gap-2">
                                                                @can('campaign_update')
                                                                    <a href="{{route('admin.campaign.edit',[$item->id])}}"
                                                                       class="action-btn btn--light-primary"
                                                                       style="--size: 30px">
                                                                        <span class="material-icons">edit</span>
                                                                    </a>
                                                                @endcan
                                                                @can('campaign_delete')
                                                                    <button type="button"
                                                                            data-id="{{$item->id}}"
                                                                            class="action-btn btn--danger delete_section"
                                                                            style="--size: 30px">
                                                                    <span
                                                                        class="material-symbols-outlined">delete</span>
                                                                    </button>
                                                                    <form
                                                                        action="{{route('admin.campaign.delete',[$item->id])}}"
                                                                        method="post" id="delete-{{$item->id}}"
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
                                        {!! $campaigns->links() !!}
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
    <script src="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.js"></script>
    <script>
        "use Strict";

        $('.switcher_input').on('click', function () {
            let itemId = $(this).data('status');
            let route = '{{ route('admin.campaign.status-update', ['id' => ':itemId']) }}';
            route = route.replace(':itemId', itemId);
            route_alert_reload(route, '{{ translate('want_to_update_status') }}');
        })

        $('.delete_section').on('click', function () {
            let itemId = $(this).data('id');
            form_alert('delete-' + itemId, '{{ translate('want_to_delete_this') }}');
        })

        $(document).ready(function () {
            $('.js-select').select2();
        });
    </script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/dataTables.select.min.js"></script>
@endpush
