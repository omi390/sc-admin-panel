@extends('adminmodule::layouts.master')

@section('title',translate('Ads List'))

@push('css_or_js')
    <style>
        .alert--container .alert:not(.active) {
            display: none;
        }

        .alert--message-2 {
            border-left: 3px solid var(--success-color);
            border-radius: 6px;
            position: fixed;
            right: 20px;
            top: 80px;
            z-index: 9999;
            background: var(--white-color);
            width: 80vw;
            display: flex;
            max-width: 380px;
            align-items: center;
            gap: 12px;
            padding: 16px;
            font-size: 12px;
            transition: all ease 0.5s;
            box-shadow: 0 0 2rem rgba(0, 0, 0, 0.15);
        }

        .alert--message-2 h6 {
            font-size: 1rem;
        }

        .alert--message-2:not(.active) {
            transform: translateX(calc(100% + 40px));
        }
    </style>
@endpush

@section('content')

    <div class="main-content">
        @if(session('ads-store'))
            <div class="d-flex align-items-center gap-2 alert--message-2 fade show active">
                <img width="28" class="align-self-start image"
                     src="{{ asset('public/assets/admin-module/img/icons/CircleWavyCheck.svg') }}" alt="">
                <div class="">
                    <h6 class="title mb-2 text-truncate">{{ translate('Ad Created Successfully') }}!</h6>
                    <p class="message">{{translate('It will be live in time. To view the ad go to')}} <a
                            href="{{ route('admin.advertisements.ads-list') }}"
                            class="c1">{{translate('Advertisement List')}}</a>
                    </p>
                </div>
                <button type="button" class="btn-close position-relative p-0" aria-label="Close"></button>
            </div>
        @endif

        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap d-flex align-items-center justify-content-between gap-2 mb-3">
                        <h2 class="page-title">{{translate('Ads List')}}</h2>
                        <div class="ripple-animation" data-bs-toggle="tooltip" data-bs-placement="top"
                             data-bs-title="{{translate('View advertisement history here. You can see status wise advertisement history and adjust priority. Edit, pause, resume, resubmit, or delete as needed.')}}"
                             type="button">
                            <img src="{{asset('/public/assets/admin-module/img/info.svg')}}" class="svg" alt="">
                        </div>
                    </div>

                    @if($advertisements->count() > 0)
                        <div
                            class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                            <ul class="nav nav--tabs">
                                <li class="nav-item">
                                    <a class="nav-link {{$queryParams['status'] == 'all' ? 'active' : ''}}"
                                       href="{{route('admin.advertisements.ads-list', ['status' => 'all'])}}">
                                        {{translate('All')}}
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link {{$queryParams['status'] == 'approved' ? 'active' : ''}}"
                                       href="{{route('admin.advertisements.ads-list', ['status' => 'approved'])}}">
                                        {{translate('Approved')}}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{$queryParams['status'] == 'running' ? 'active' : ''}}"
                                       href="{{route('admin.advertisements.ads-list', ['status' => 'running'])}}">
                                        {{translate('Running')}}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{$queryParams['status'] == 'expired' ? 'active' : ''}}"
                                       href="{{route('admin.advertisements.ads-list', ['status' => 'expired'])}}">
                                        {{translate('Expired')}}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{$queryParams['status'] == 'denied' ? 'active' : ''}}"
                                       href="{{route('admin.advertisements.ads-list', ['status' => 'denied'])}}">
                                        {{translate('Denied')}}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{$queryParams['status'] == 'paused' ? 'active' : ''}}"
                                       href="{{route('admin.advertisements.ads-list', ['status' => 'paused'])}}">
                                        {{translate('Paused')}}
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">

                                    <form
                                        action="{{route('admin.advertisements.ads-list', ['status' => $queryParams['status']])}}"
                                        class="search-form search-form_style-two"
                                        method="get">
                                        @csrf
                                        <div class="input-group search-form__input_group">
                                            <span class="search-form__icon">
                                                <span class="material-icons">search</span>
                                            </span>
                                            <input type="search" class="theme-input-style search-form__input"
                                                   value="{{$queryParams['search'] }}" name="search"
                                                   placeholder="{{translate('search_here')}}">
                                            <input type="hidden"
                                                   value="{{$queryParams['status'] }}" name="status"
                                                   placeholder="{{translate('search_here')}}">
                                        </div>
                                        <button type="submit"
                                                class="btn btn--primary">{{translate('search')}}</button>
                                    </form>

                                    <div class="d-flex flex-wrap align-items-center gap-3">
                                        @can('advertisement_add')
                                            <div
                                                class="page-title-wrap d-flex justify-content-between flex-wrap align-items-center gap-3">
                                                <a href="{{route('admin.advertisements.ads-create')}}"
                                                   class="btn btn--primary">{{translate('create_ads')}}</a>
                                            </div>
                                        @endcan
                                        @can('advertisement_export')
                                            <div class="dropdown">
                                                <button type="button"
                                                        class="btn btn--secondary text-capitalize dropdown-toggle"
                                                        data-bs-toggle="dropdown">
                                                <span class="material-icons">file_download</span> {{translate('download')}}
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                    <li><a class="dropdown-item"
                                                           href="{{route('admin.advertisements.download', ['status' => $queryParams['status'], 'search' => $queryParams['search']])}}">{{translate('excel')}}</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        @endcan
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table id="example" class="table align-middle">
                                        <thead class="text-nowrap">
                                        <tr>
                                            <th>{{translate('SL')}}</th>
                                            <th>{{translate('Title')}}</th>
                                            <th>{{translate('Advertisement ID')}}</th>
                                            <th>{{translate('Provider Info')}}</th>
                                            <th>{{translate('Ads Type')}}</th>
                                            <th>{{translate('Duration')}}</th>
                                            @if($queryParams['status'] == 'all' || $queryParams['status'] == 'expired')
                                                <th class="text-center">{{translate('Status')}}</th>
                                            @endif
                                            <th class="text-center">{{translate('Priority')}}</th>
                                            <th class="text-center">{{translate('Action')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($advertisements as $key => $advertisement)
                                            <tr>
                                                <td>{{ $advertisements->firstitem()+$key }}</td>
                                                <td>
                                                    <a href="{{route('admin.advertisements.details',[$advertisement->id])}}">{{Str::limit($advertisement->title, 40)}}</a>
                                                </td>
                                                <td>
                                                    <a href="{{route('admin.advertisements.details',[$advertisement->id])}}">{{ $advertisement->readable_id }}</a>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column gap-1">
                                                        <span>{{ $advertisement?->provider?->company_name }}</span>
                                                        <a href="mailto:{{ $advertisement?->provider?->company_email }}"
                                                           class="fs-12">{{ $advertisement?->provider?->company_email }}</a>
                                                    </div>
                                                </td>
                                                <td>{{ ucwords(str_replace('_', ' ', $advertisement->type)) }}</td>
                                                <td>
                                                    <div class="d-flex flex-column gap-1 fs-12">
                                                        <span>{{ $advertisement->start_date->format('Y-m-d') }}</span>
                                                        <span>{{ $advertisement->end_date->format('Y-m-d') }}</span>
                                                    </div>
                                                </td>
                                                @if($queryParams['status'] == 'all' || $queryParams['status'] == 'expired')
                                                    <td>
                                                        <div class="d-flex justify-content-center">
                                                            @if($advertisement->status == 'pending')
                                                                <label
                                                                    class="badge badge-info">{{ $advertisement->status }}</label>
                                                            @elseif(($advertisement->status == 'approved' || $advertisement->status == 'resumed') && ($advertisement->start_date <= \Carbon\Carbon::today() && $advertisement->end_date >= \Carbon\Carbon::today() ) )
                                                                <label
                                                                    class="badge badge-primary">{{translate('Running')}}
                                                                    @elseif($advertisement->status == 'approved')
                                                                        <label
                                                                            class="badge badge-success">{{translate('Approved')}}
                                                                            @elseif($advertisement->status == 'paused')
                                                                                <label
                                                                                    class="badge badge-success">{{translate('Paused')}}</label>
                                                                            @elseif($advertisement->status == 'resumed')
                                                                                <label
                                                                                    class="badge badge-success">{{translate('Resumed')}}</label>
                                                                            @elseif($advertisement->status == 'running')
                                                                                <label
                                                                                    class="badge badge-primary">{{ translate('running') }}</label>
                                                                            @elseif($advertisement->status == 'expired')
                                                                                <label
                                                                                    class="badge badge-secondary">{{ translate('Expired') }}</label>
                                                                            @elseif($advertisement->status == 'denied' || $advertisement->status == 'canceled')
                                                                                <label
                                                                                    class="badge badge-danger">{{ $advertisement->status }}</label>
                                                            @endif
                                                        </div>
                                                        @php
                                                            $end_date = \Carbon\Carbon::parse($advertisement->end_date)->startOfDay();
                                                            $today = \Carbon\Carbon::today();
                                                        @endphp
                                                        @if($end_date < $today)
                                                            <div class="text-center">
                                                                <small class="text-muted text-center">({{translate('Expired')}})</small>
                                                            </div>
                                                        @endif
                                                    </td>
                                                @endif
                                                <td>
                                                    <div class="d-flex justify-content-center set-priority"
                                                         data-id="{{$advertisement->id}}"
                                                         data-action="{{ route('admin.advertisements.set-priority', [$advertisement->id])}}"
                                                         data-bs-toggle="modal" data-bs-target="#setPriorityModal">
                                                        @if($advertisement->priority == null)
                                                            <div class="text-muted d-flex gap-1 align-items-center">
                                                                <span class="lh-1 mt-1">N/A</span>
                                                                <span data-bs-toggle="tooltip"
                                                                      title="Priority isn't set yet!">
                                                                <img
                                                                    src="{{asset('public/assets/admin-module')}}/img/icons/info-hexa.svg"
                                                                    alt="">
                                                            </span>
                                                            </div>
                                                        @else
                                                            <span>{{ $advertisement->priority }}</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center">
                                                        <div class="dropdown dropdown__style--two">
                                                            <button type="button" class="bg-transparent border-0 title-color"
                                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                                <span class="material-symbols-outlined">more_vert</span>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                @can('advertisement_view')
                                                                    <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                       href="{{route('admin.advertisements.details',[$advertisement->id])}}">
                                                                <span
                                                                    class="material-icons">visibility</span>
                                                                        {{ translate('View Ads') }}
                                                                    </a>
                                                                @endcan
                                                                @can('advertisement_update')
                                                                    <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                       href="{{route('admin.advertisements.edit',[$advertisement->id])}}">
                                                                        <span
                                                                            class="material-icons">edit</span>
                                                                        {{ translate('Edit Ads') }}
                                                                    </a>
                                                                @endcan
                                                                @if($advertisement->status == 'pending')
                                                                    @can('advertisement_approve_or_deny')
                                                                        <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                           href="{{route('admin.advertisements.status-update',[$advertisement->id, 'approved'])}}">
                                                                        <span
                                                                            class="material-icons">done</span>
                                                                            {{ translate('Approve Ads') }}
                                                                        </a>

                                                                        <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                           href="#" data-bs-toggle="modal"
                                                                           data-bs-target="#deniedModal-{{$advertisement->id}}">
                                                                        <span
                                                                            class="material-icons">close</span>
                                                                            {{ translate('Deny Ads') }}
                                                                        </a>
                                                                    @endcan
                                                                @endif
                                                                @if((!in_array($advertisement->status, ['pending', 'paused', 'denied', 'canceled']) || !$advertisement->where('end_date', '<', Carbon\Carbon::today())))
                                                                    @can('advertisement_manage_status')
                                                                        <a class="dropdown-item d-flex gap-2 align-items-center set-priority"
                                                                           data-id="{{$advertisement->id}}"
                                                                           data-action="{{ route('admin.advertisements.set-priority', [$advertisement->id])}}"
                                                                           data-bs-toggle="modal"
                                                                           data-bs-target="#setPriorityModal">
                                                                            <span class="material-icons">format_list_bulleted</span>
                                                                            {{ translate('Set Priority') }}
                                                                        </a>
                                                                    @endcan
                                                                @endif
                                                                @if($advertisement->whereIn('status', ['approved', 'resumed'])->where('start_date', '<=', Carbon\Carbon::today())->where('end_date', '>', Carbon\Carbon::today()) && in_array($advertisement->status, ['approved', 'resumed']))
                                                                    @can('advertisement_manage_status')
                                                                        <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                           href="#" data-bs-toggle="modal"
                                                                           data-bs-target="#pausedModal-{{$advertisement->id}}">
                                                                    <span
                                                                        class="material-icons">pause_circle</span>
                                                                            {{ translate('Pause Ads') }}
                                                                        </a>
                                                                    @endcan
                                                                @endif
                                                                @if($advertisement->status == 'paused')
                                                                    @can('advertisement_manage_status')
                                                                        <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                           href="#" data-bs-toggle="modal"
                                                                           data-bs-target="#resumeModal-{{$advertisement->id}}">
                                                                    <span
                                                                        class="material-icons">play_arrow</span>
                                                                            {{ translate('Resume Ads') }}
                                                                        </a>
                                                                    @endcan
                                                                @endif
                                                                @if((($advertisement->where('end_date', '<', Carbon\Carbon::today()) || $advertisement->status == 'denied')) && ($advertisement->status != 'approved' &&  !$advertisement->whereIn('status', ['approved', 'resumed'])->where('start_date', '<=', Carbon\Carbon::today())->where('end_date', '>', Carbon\Carbon::today())))
                                                                    @can('advertisement_update')
                                                                        <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                           href="{{route('admin.advertisements.re-submit',[$advertisement->id])}}">
                                                                    <span
                                                                        class="material-icons">history</span>
                                                                            {{ translate('Re-submit Ads') }}
                                                                        </a>
                                                                    @endcan
                                                                @endif

                                                                    @if(($advertisement->status == 'denied'))
                                                                        @can('advertisement_update')
                                                                            <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                               href="{{route('admin.advertisements.re-submit',[$advertisement->id])}}">
                                                                <span
                                                                    class="material-symbols-outlined">history</span>
                                                                                {{ translate('Re-submit Ads') }}
                                                                            </a>
                                                                        @endcan
                                                                    @endif

                                                                @can('advertisement_delete')
                                                                        @if(($advertisement->status == 'approved' || $advertisement->status == 'resumed') && ($advertisement->start_date <= \Carbon\Carbon::today() && $advertisement->end_date > \Carbon\Carbon::today() ))
                                                                        <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                           data-bs-toggle="modal"
                                                                           data-bs-target="#adDeleteModal-{{$advertisement->id}}">
                                                                    <span class="material-icons">delete</span>
                                                                            {{ translate('Delete Ads') }}
                                                                        </a>
                                                                    @else
                                                                        <a class="dropdown-item d-flex gap-2 align-items-center delete_section"
                                                                           href="#" data-id="{{$advertisement->id}}">
                                                                    <span
                                                                        class="material-icons">delete</span>
                                                                            {{ translate('Delete Ads') }}
                                                                        </a>
                                                                    @endif
                                                                @endcan
                                                                <form
                                                                    action="{{route('admin.advertisements.delete',[$advertisement->id])}}"
                                                                    method="post" id="delete-{{$advertisement->id}}"
                                                                    class="hidden">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                </form>
                                                            </ul>
                                                        </div>

                                                        <div class="modal fade" id="deniedModal-{{$advertisement->id}}"
                                                             tabindex="-1"
                                                             aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-body pt-5 p-md-5">
                                                                        <button type="button" class="btn-close"
                                                                                data-bs-dismiss="modal"
                                                                                aria-label="Close"></button>
                                                                        <div class="d-flex justify-content-center mb-4">
                                                                            <img width="75" height="75"
                                                                                 src="{{asset('public/assets/admin-module/img/delete2.png')}}"
                                                                                 class="rounded-circle" alt="">
                                                                        </div>

                                                                        <h3 class="text-start mb-1 fw-medium text-center">{{translate('Are you sure you want to deny the request?')}}</h3>
                                                                        <p class="text-start fs-12 fw-medium text-muted text-center">{{translate('You will lost the provider ads request')}}</p>
                                                                        <form method="post"
                                                                              action="{{route('admin.advertisements.status-update',[$advertisement->id, 'denied'])}}">
                                                                            @csrf
                                                                            <div class="form-floating">
                                                                            <textarea class="form-control h-69px"
                                                                                      placeholder="{{translate('Denied Note')}}"
                                                                                      name="note" id="add-your-note"
                                                                                      required></textarea>
                                                                                <label for="add-your-note"
                                                                                       class="d-flex align-items-center gap-1">{{translate('Deny Note')}}</label>
                                                                                <div
                                                                                    class="d-flex justify-content-center mt-3 gap-3">
                                                                                    <button type="button"
                                                                                            class="btn btn--secondary min-w-92px px-2"
                                                                                            data-bs-dismiss="modal"
                                                                                            aria-label="Close">{{translate('Not Now')}}</button>
                                                                                    <button type="submit"
                                                                                            class="btn btn--danger min-w-92px">{{translate('Yes')}}</button>
                                                                                </div>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- Ad Delete Modal --}}
                                                        <div class="modal fade"
                                                             id="adDeleteModal-{{$advertisement->id}}"
                                                             tabindex="-1"
                                                             aria-labelledby="adDeleteModalLabel" aria-hidden="true">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-body pt-5 p-md-5">
                                                                        <button type="button" class="btn-close"
                                                                                data-bs-dismiss="modal"
                                                                                aria-label="Close"></button>
                                                                        <div class="d-flex justify-content-center mb-4">
                                                                            <img width="75" height="75"
                                                                                 src="{{asset('public/assets/admin-module/img/ad_delete.svg')}}"
                                                                                 class="rounded-circle" alt="">
                                                                        </div>

                                                                        <h3 class="text-start mb-1 fw-medium text-center">{{translate('You can’t delete the ad')}}</h3>
                                                                        <p class="text-start fs-12 fw-medium text-muted text-center">{{translate('Your ad is running, To delete this ad from the list, please change its status first. Once the status is updated, you can proceed with deletion')}}</p>                                                                        <div
                                                                            class="d-flex justify-content-center mt-3 gap-3">
                                                                            <button type="button"
                                                                                    class="btn btn--danger min-w-92px px-2"
                                                                                    data-bs-dismiss="modal"
                                                                                    aria-label="Close">{{translate('Okay')}}</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>


                                                        <div class="modal fade" id="pausedModal-{{$advertisement->id}}"
                                                             tabindex="-1"
                                                             aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-body pt-5 p-md-5">
                                                                        <button type="button" class="btn-close"
                                                                                data-bs-dismiss="modal"
                                                                                aria-label="Close"></button>
                                                                        <div class="d-flex justify-content-center mb-4">
                                                                            <img width="75" height="75"
                                                                                 src="{{asset('public/assets/admin-module/img/paused.png')}}"
                                                                                 class="rounded-circle" alt="">
                                                                        </div>

                                                                        <h3 class="text-start mb-1 fw-medium text-center">{{translate('Are you sure you want to Pause the request??')}}</h3>
                                                                        <p class="text-start fs-12 fw-medium text-muted text-center">{{translate('This ad will be pause and not show in the app or web')}}</p>
                                                                        <form method="post"
                                                                              action="{{route('admin.advertisements.status-update',[$advertisement->id, 'paused'])}}">
                                                                            @csrf
                                                                            <div class="form-floating">
                                                                            <textarea class="form-control h-69px"
                                                                                      placeholder="{{translate('Paused Note')}}"
                                                                                      name="note" id="add-paused-note"
                                                                                      required></textarea>
                                                                                <label for="add-your-note"
                                                                                       class="d-flex align-items-center gap-1">{{translate('Paused Note')}}</label>
                                                                                <div
                                                                                    class="d-flex justify-content-center mt-3 gap-3">
                                                                                    <button type="button"
                                                                                            class="btn btn--secondary min-w-92px px-2"
                                                                                            data-bs-dismiss="modal"
                                                                                            aria-label="Close">{{translate('Not Now')}}</button>
                                                                                    <button type="submit"
                                                                                            class="btn btn--danger min-w-92px">{{translate('Yes')}}</button>
                                                                                </div>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal fade" id="resumeModal-{{$advertisement->id}}"
                                                             tabindex="-1"
                                                             aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-body pt-5 p-md-5">
                                                                        <button type="button" class="btn-close"
                                                                                data-bs-dismiss="modal"
                                                                                aria-label="Close"></button>
                                                                        <div class="d-flex justify-content-center mb-4">
                                                                            <img width="75" height="75"
                                                                                 src="{{asset('public/assets/admin-module/img/resume.svg')}}"
                                                                                 class="rounded-circle" alt="">
                                                                        </div>

                                                                        <h3 class="text-start mb-1 fw-medium text-center">{{translate('Are you sure you want to Resume the request???')}}</h3>
                                                                        <p class="text-start fs-12 fw-medium text-muted text-center">{{translate('This ad will be run again and will show in the app or web')}}</p>
                                                                        <form method="post"
                                                                              action="{{route('admin.advertisements.status-update',[$advertisement->id, 'resumed'])}}">
                                                                            @csrf
                                                                            <div class="form-floating">
                                                                                <div
                                                                                    class="d-flex justify-content-center mt-3 gap-3">
                                                                                    <button type="button"
                                                                                            class="btn btn--secondary min-w-92px px-2"
                                                                                            data-bs-dismiss="modal"
                                                                                            aria-label="Close">{{translate('Not Now')}}</button>
                                                                                    <button type="submit"
                                                                                            class="btn btn--primary min-w-92px">{{translate('Yes')}}</button>
                                                                                </div>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="text-center">
                                                <td colspan="8">{{translate('No_data_available')}}</td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-end">
                                    {!! $advertisements->links() !!}
                                </div>
                            </div>
                        </div>
                    @elseif($queryParams['status'] == 'all' && $advertisements->count() == 0)
                        <div class="card mt-5">
                            <div class="card-body">
                                <div class="d-flex flex-column gap-2 align-items-center text-center">
                                    <img width="140" class="mb-2"
                                         src="{{asset('public/assets/provider-module')}}/img/media/create-ads.png"
                                         alt="">
                                    <h4>{{translate('Advertisement List')}}</h4>
                                    <p>{{translate("Uh oh! there is not advertisement created by provider!")}}</p>
                                </div>
                                <hr class="my-4">
                                <div class="row justify-content-center rounded mb-5">
                                    <div class="col-xl-6 col-lg-7 col-md-8 col-sm-9">
                                        <div
                                            class="bg-light d-flex flex-column gap-2 align-items-center text-center py-4 px-4 px-xl-5">
                                            <h4 class="mb-2">{{translate('Create Advertisement to Promote Provider’s Services')}}</h4>
                                            <p>{{translate('Here, Provider can showcase their services or profile to a wider audience through targeted ad campaigns. You can create ad on behalf of a provider.')}}</p>
                                            <a class="text-white btn btn--primary"
                                               href="{{route('admin.advertisements.ads-create')}}">{{translate('Create Ads')}}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div
                            class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                            <ul class="nav nav--tabs">
                                <li class="nav-item">
                                    <a class="nav-link {{$queryParams['status'] == 'all' ? 'active' : ''}}"
                                       href="{{route('admin.advertisements.ads-list', ['status' => 'all'])}}">
                                        {{translate('All')}}
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link {{$queryParams['status'] == 'approved' ? 'active' : ''}}"
                                       href="{{route('admin.advertisements.ads-list', ['status' => 'approved'])}}">
                                        {{translate('Approved')}}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{$queryParams['status'] == 'running' ? 'active' : ''}}"
                                       href="{{route('admin.advertisements.ads-list', ['status' => 'running'])}}">
                                        {{translate('Running')}}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{$queryParams['status'] == 'expired' ? 'active' : ''}}"
                                       href="{{route('admin.advertisements.ads-list', ['status' => 'expired'])}}">
                                        {{translate('Expired')}}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{$queryParams['status'] == 'denied' ? 'active' : ''}}"
                                       href="{{route('admin.advertisements.ads-list', ['status' => 'denied'])}}">
                                        {{translate('Denied')}}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{$queryParams['status'] == 'paused' ? 'active' : ''}}"
                                       href="{{route('admin.advertisements.ads-list', ['status' => 'paused'])}}">
                                        {{translate('Paused')}}
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">

                                    <form
                                        action="{{route('admin.advertisements.ads-list', ['status' => $queryParams['status']])}}"
                                        class="search-form search-form_style-two"
                                        method="get">
                                        @csrf
                                        <div class="input-group search-form__input_group">
                                            <span class="search-form__icon">
                                                <span class="material-icons">search</span>
                                            </span>
                                            <input type="search" class="theme-input-style search-form__input"
                                                   value="{{$queryParams['search'] }}" name="search"
                                                   placeholder="{{translate('search_here')}}">
                                            <input type="hidden"
                                                   value="{{$queryParams['status'] }}" name="status"
                                                   placeholder="{{translate('search_here')}}">
                                        </div>
                                        <button type="submit"
                                                class="btn btn--primary">{{translate('search')}}</button>
                                    </form>

                                    <div class="d-flex flex-wrap align-items-center gap-3">
                                        @can('advertisement_add')
                                            <div
                                                class="page-title-wrap d-flex justify-content-between flex-wrap align-items-center gap-3">
                                                <a href="{{route('admin.advertisements.ads-create')}}"
                                                   class="btn btn--primary">{{translate('create_ads')}}</a>
                                            </div>
                                        @endcan
                                        @can('advertisement_export')
                                            <div class="dropdown">
                                                <button type="button"
                                                        class="btn btn--secondary text-capitalize dropdown-toggle"
                                                        data-bs-toggle="dropdown">
                                                <span
                                                    class="material-icons">file_download</span> {{translate('download')}}
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                    <li><a class="dropdown-item"
                                                           href="{{route('admin.advertisements.download', ['status' => $queryParams['status'], 'search' => $queryParams['search']])}}">{{translate('excel')}}</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        @endcan
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table id="example" class="table align-middle">
                                        <thead class="text-nowrap">
                                        <tr>
                                            <th>{{translate('SL')}}</th>
                                            <th>{{translate('Title')}}</th>
                                            <th>{{translate('Advertisement ID')}}</th>
                                            <th>{{translate('Provider Info')}}</th>
                                            <th>{{translate('Ads Type')}}</th>
                                            <th>{{translate('Duration')}}</th>
                                            @if($queryParams['status'] == 'all' || $queryParams['status'] == 'expired')
                                                <th class="text-center">{{translate('Status')}}</th>
                                            @endif
                                            <th class="text-center">{{translate('Priority')}}</th>
                                            <th class="text-center">{{translate('Action')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($advertisements as $key => $advertisement)
                                            <tr>
                                                <td>{{ $advertisements->firstitem()+$key }}</td>
                                                <td>
                                                    <a href="{{route('admin.advertisements.details',[$advertisement->id])}}">{{Str::limit($advertisement->title, 40)}}</a>
                                                </td>
                                                <td>
                                                    <a href="{{route('admin.advertisements.details',[$advertisement->id])}}">{{ $advertisement->readable_id }}</a>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column gap-1">
                                                        <span>{{ $advertisement?->provider?->company_name }}</span>
                                                        <a href="mailto:{{ $advertisement?->provider?->company_email }}"
                                                           class="fs-12">{{ $advertisement?->provider?->company_email }}</a>
                                                    </div>
                                                </td>
                                                <td>{{ ucwords(str_replace('_', ' ', $advertisement->type)) }}</td>
                                                <td>
                                                    <div class="d-flex flex-column gap-1 fs-12">
                                                        <span>{{ $advertisement->start_date->format('Y-m-d') }}</span>
                                                        <span>{{ $advertisement->end_date->format('Y-m-d') }}</span>
                                                    </div>
                                                </td>
                                                @if($queryParams['status'] == 'all' || $queryParams['status'] == 'expired')
                                                    <td>
                                                        <div class="d-flex justify-content-center">
                                                            @if($advertisement->status == 'pending')
                                                                <label class="badge badge-info">{{ $advertisement->status }}</label>
                                                            @elseif(($advertisement->status == 'approved' || $advertisement->status == 'resumed') && ($advertisement->start_date <= \Carbon\Carbon::today() && $advertisement->end_date >= \Carbon\Carbon::today() ) )
                                                                <label class="badge badge-primary">{{translate('Running')}}
                                                            @elseif($advertisement->status == 'approved')
                                                                 <label class="badge badge-success">{{translate('Approved')}}
                                                            @elseif($advertisement->status == 'paused')
                                                                 <label class="badge badge-success">{{translate('Paused')}}</label>
                                                            @elseif($advertisement->status == 'resumed')
                                                                <label class="badge badge-success">{{translate('Resumed')}}</label>
                                                            @elseif($advertisement->status == 'running')
                                                                <label class="badge badge-primary">{{ translate('running') }}</label>
                                                            @elseif($advertisement->status == 'expired')
                                                                <label class="badge badge-secondary">{{ translate('Expired') }}</label>
                                                            @elseif($advertisement->status == 'denied')
                                                                 <label class="badge badge-danger">{{ translate('Denied') }}</label>
                                                            @elseif($advertisement->status == 'canceled')
                                                                  <label class="badge badge-danger">{{ translate('canceled') }}</label>
                                                            @endif
                                                             @php
                                                                 $end_date = \Carbon\Carbon::parse($advertisement->end_date)->startOfDay();
                                                                 $today = \Carbon\Carbon::today();
                                                             @endphp
                                                             @if($end_date < $today)
                                                                 <div class="text-center">
                                                                     <small class="text-muted text-center">({{translate('Expired')}})</small>
                                                                 </div>
                                                            @endif
                                                        </div>
                                                    </td>
                                                @endif
                                                <td>
                                                    <div class="d-flex justify-content-center set-priority"
                                                         data-id="{{$advertisement->id}}"
                                                         data-action="{{ route('admin.advertisements.set-priority', [$advertisement->id])}}"
                                                         data-bs-toggle="modal" data-bs-target="#setPriorityModal">
                                                        @if($advertisement->priority == null)
                                                            <div class="text-muted d-flex gap-1 align-items-center">
                                                                <span class="lh-1 mt-1">N/A</span>
                                                                <span data-bs-toggle="tooltip"
                                                                      title="Priority isn't set yet!">
                                                                <img
                                                                    src="{{asset('public/assets/admin-module')}}/img/icons/info-hexa.svg"
                                                                    alt="">
                                                            </span>
                                                            </div>
                                                        @endif
                                                        <span>{{ $advertisement->priority }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center">
                                                        <div class="dropdown dropdown__style--two">
                                                            <button type="button" class="bg-transparent border-0"
                                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                                <span class="material-symbols-outlined">more_vert</span>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                @can('advertisement_view')
                                                                    <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                       href="{{route('admin.advertisements.details',[$advertisement->id])}}">
                                                                <span
                                                                    class="material-symbols-outlined">visibility</span>
                                                                        {{ translate('View Ads') }}
                                                                    </a>
                                                                @endcan
                                                                @can('advertisement_update')
                                                                    <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                       href="{{route('admin.advertisements.edit',[$advertisement->id])}}">
                                                                        <span
                                                                            class="material-symbols-outlined">edit</span>
                                                                        {{ translate('Edit Ads') }}
                                                                    </a>
                                                                @endcan
                                                                @if($advertisement->status == 'pending')
                                                                    @can('advertisement_approve_or_deny')
                                                                        <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                           href="{{route('admin.advertisements.status-update',[$advertisement->id, 'approved'])}}">
                                                                        <span
                                                                            class="material-symbols-outlined">done</span>
                                                                            {{ translate('Approve Ads') }}
                                                                        </a>

                                                                        <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                           href="#" data-bs-toggle="modal"
                                                                           data-bs-target="#deniedModal-{{$advertisement->id}}">
                                                                        <span
                                                                            class="material-symbols-outlined">close</span>
                                                                            {{ translate('Deny Ads') }}
                                                                        </a>
                                                                    @endcan
                                                                @endif
                                                                @if((!in_array($advertisement->status, ['pending', 'paused', 'denied', 'canceled']) || !$advertisement->where('end_date', '<', Carbon\Carbon::today())))
                                                                    @can('advertisement_manage_status')
                                                                        <a class="dropdown-item d-flex gap-2 align-items-center set-priority"
                                                                           data-id="{{$advertisement->id}}"
                                                                           data-action="{{ route('admin.advertisements.set-priority', [$advertisement->id])}}"
                                                                           data-bs-toggle="modal"
                                                                           data-bs-target="#setPriorityModal">
                                                                            <span class="material-symbols-outlined">format_list_bulleted</span>
                                                                            {{ translate('Set Priority') }}
                                                                        </a>
                                                                    @endcan
                                                                @endif
                                                                @if($advertisement->whereIn('status', ['approved', 'resumed'])->where('start_date', '<=', Carbon\Carbon::today())->where('end_date', '>', Carbon\Carbon::today()) && in_array($advertisement->status, ['approved', 'resumed']))
                                                                    @can('advertisement_manage_status')
                                                                        <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                           href="#" data-bs-toggle="modal"
                                                                           data-bs-target="#pausedModal-{{$advertisement->id}}">
                                                                    <span
                                                                        class="material-symbols-outlined">pause_circle</span>
                                                                            {{ translate('Pause Ads') }}
                                                                        </a>
                                                                    @endcan
                                                                @endif
                                                                @if($advertisement->status == 'paused')
                                                                    @can('advertisement_manage_status')
                                                                        <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                           href="#" data-bs-toggle="modal"
                                                                           data-bs-target="#resumeModal-{{$advertisement->id}}">
                                                                    <span
                                                                        class="material-symbols-outlined">play_arrow</span>
                                                                            {{ translate('Resume Ads') }}
                                                                        </a>
                                                                    @endcan
                                                                @endif
                                                                @if((($advertisement->where('end_date', '<', Carbon\Carbon::today()) || $advertisement->status == 'denied')) && ($advertisement->status != 'approved' &&  !$advertisement->whereIn('status', ['approved', 'resumed'])->where('start_date', '<=', Carbon\Carbon::today())->where('end_date', '>', Carbon\Carbon::today())))
                                                                    @can('advertisement_update')
                                                                        <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                           href="{{route('admin.advertisements.re-submit',[$advertisement->id])}}">
                                                                    <span
                                                                        class="material-symbols-outlined">history</span>
                                                                            {{ translate('Re-submit Ads') }}
                                                                        </a>
                                                                    @endcan
                                                                @endif

                                                                @if(($advertisement->status == 'denied'))
                                                                    @can('advertisement_update')
                                                                        <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                           href="{{route('admin.advertisements.re-submit',[$advertisement->id])}}">
                                                                <span
                                                                    class="material-symbols-outlined">history</span>
                                                                            {{ translate('Re-submit Ads') }}
                                                                        </a>
                                                                    @endcan
                                                                @endif

                                                                @can('advertisement_delete')
                                                                        @if(($advertisement->status == 'approved' || $advertisement->status == 'resumed') && ($advertisement->start_date <= \Carbon\Carbon::today() && $advertisement->end_date > \Carbon\Carbon::today() ))
                                                                        <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                           data-bs-toggle="modal"
                                                                           data-bs-target="#adDeleteModal-{{$advertisement->id}}">
                                                                    <span
                                                                        class="material-symbols-outlined">delete</span>
                                                                            {{ translate('Delete Ads') }}
                                                                        </a>
                                                                    @else
                                                                        <a class="dropdown-item d-flex gap-2 align-items-center delete_section"
                                                                           href="#" data-id="{{$advertisement->id}}">
                                                                    <span
                                                                        class="material-symbols-outlined">delete</span>
                                                                            {{ translate('Delete Ads') }}
                                                                        </a>
                                                                    @endif
                                                                @endcan
                                                                <form
                                                                    action="{{route('admin.advertisements.delete',[$advertisement->id])}}"
                                                                    method="post" id="delete-{{$advertisement->id}}"
                                                                    class="hidden">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                </form>
                                                            </ul>
                                                        </div>

                                                        <div class="modal fade" id="deniedModal-{{$advertisement->id}}"
                                                             tabindex="-1"
                                                             aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-body pt-5 p-md-5">
                                                                        <button type="button" class="btn-close"
                                                                                data-bs-dismiss="modal"
                                                                                aria-label="Close"></button>
                                                                        <div class="d-flex justify-content-center mb-4">
                                                                            <img width="75" height="75"
                                                                                 src="{{asset('public/assets/admin-module/img/delete2.png')}}"
                                                                                 class="rounded-circle" alt="">
                                                                        </div>

                                                                        <h3 class="text-start mb-1 fw-medium text-center">{{translate('Are you sure you want to deny the request?')}}</h3>
                                                                        <p class="text-start fs-12 fw-medium text-muted text-center">{{translate('You will lost the provider ads request')}}</p>
                                                                        <form method="post"
                                                                              action="{{route('admin.advertisements.status-update',[$advertisement->id, 'denied'])}}">
                                                                            @csrf
                                                                            <div class="form-floating">
                                                                            <textarea class="form-control h-69px"
                                                                                      placeholder="{{translate('Denied Note')}}"
                                                                                      name="note" id="add-your-note"
                                                                                      required></textarea>
                                                                                <label for="add-your-note"
                                                                                       class="d-flex align-items-center gap-1">{{translate('Deny Note')}}</label>
                                                                                <div
                                                                                    class="d-flex justify-content-center mt-3 gap-3">
                                                                                    <button type="button"
                                                                                            class="btn btn--secondary min-w-92px px-2"
                                                                                            data-bs-dismiss="modal"
                                                                                            aria-label="Close">{{translate('Not Now')}}</button>
                                                                                    <button type="submit"
                                                                                            class="btn btn--danger min-w-92px">{{translate('Yes')}}</button>
                                                                                </div>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- Ad Delete Modal --}}
                                                        <div class="modal fade"
                                                             id="adDeleteModal-{{$advertisement->id}}"
                                                             tabindex="-1"
                                                             aria-labelledby="adDeleteModalLabel" aria-hidden="true">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-body pt-5 p-md-5">
                                                                        <button type="button" class="btn-close"
                                                                                data-bs-dismiss="modal"
                                                                                aria-label="Close"></button>
                                                                        <div class="d-flex justify-content-center mb-4">
                                                                            <img width="75" height="75"
                                                                                 src="{{asset('public/assets/admin-module/img/ad_delete.svg')}}"
                                                                                 class="rounded-circle" alt="">
                                                                        </div>

                                                                        <h3 class="text-start mb-1 fw-medium text-center">{{translate('You can’t delete the ad')}}</h3>
                                                                        <p class="text-start fs-12 fw-medium text-muted text-center">{{translate('This ad is running, To delete this ad from the list, please change its status first. Once the status is updated, you can proceed with deletion')}}</p>
                                                                        <div
                                                                            class="d-flex justify-content-center mt-3 gap-3">
                                                                            <button type="button"
                                                                                    class="btn btn--danger min-w-92px px-2"
                                                                                    data-bs-dismiss="modal"
                                                                                    aria-label="Close">{{translate('Okay')}}</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>


                                                        <div class="modal fade" id="pausedModal-{{$advertisement->id}}"
                                                             tabindex="-1"
                                                             aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-body pt-5 p-md-5">
                                                                        <button type="button" class="btn-close"
                                                                                data-bs-dismiss="modal"
                                                                                aria-label="Close"></button>
                                                                        <div class="d-flex justify-content-center mb-4">
                                                                            <img width="75" height="75"
                                                                                 src="{{asset('public/assets/admin-module/img/paused.png')}}"
                                                                                 class="rounded-circle" alt="">
                                                                        </div>

                                                                        <h3 class="text-start mb-1 fw-medium text-center">{{translate('Are you sure you want to Pause the request??')}}</h3>
                                                                        <p class="text-start fs-12 fw-medium text-muted text-center">{{translate('This ad will be pause and not show in the app or web')}}</p>
                                                                        <form method="post"
                                                                              action="{{route('admin.advertisements.status-update',[$advertisement->id, 'paused'])}}">
                                                                            @csrf
                                                                            <div class="form-floating">
                                                                            <textarea class="form-control h-69px"
                                                                                      placeholder="{{translate('Paused Note')}}"
                                                                                      name="note" id="add-paused-note"
                                                                                      required></textarea>
                                                                                <label for="add-your-note"
                                                                                       class="d-flex align-items-center gap-1">{{translate('Paused Note')}}</label>
                                                                                <div
                                                                                    class="d-flex justify-content-center mt-3 gap-3">
                                                                                    <button type="button"
                                                                                            class="btn btn--secondary min-w-92px px-2"
                                                                                            data-bs-dismiss="modal"
                                                                                            aria-label="Close">{{translate('Not Now')}}</button>
                                                                                    <button type="submit"
                                                                                            class="btn btn--danger min-w-92px">{{translate('Yes')}}</button>
                                                                                </div>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal fade" id="resumeModal-{{$advertisement->id}}"
                                                             tabindex="-1"
                                                             aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-body pt-5 p-md-5">
                                                                        <button type="button" class="btn-close"
                                                                                data-bs-dismiss="modal"
                                                                                aria-label="Close"></button>
                                                                        <div class="d-flex justify-content-center mb-4">
                                                                            <img width="75" height="75"
                                                                                 src="{{asset('public/assets/admin-module/img/resume.svg')}}"
                                                                                 class="rounded-circle" alt="">
                                                                        </div>

                                                                        <h3 class="text-start mb-1 fw-medium text-center">{{translate('Are you sure you want to Resume the request???')}}</h3>
                                                                        <p class="text-start fs-12 fw-medium text-muted text-center">{{translate('This ad will be run again and will show in the app or web')}}</p>
                                                                        <form method="post"
                                                                              action="{{route('admin.advertisements.status-update',[$advertisement->id, 'resumed'])}}">
                                                                            @csrf
                                                                            <div class="form-floating">
                                                                                <div
                                                                                    class="d-flex justify-content-center mt-3 gap-3">
                                                                                    <button type="button"
                                                                                            class="btn btn--secondary min-w-92px px-2"
                                                                                            data-bs-dismiss="modal"
                                                                                            aria-label="Close">{{translate('Not Now')}}</button>
                                                                                    <button type="submit"
                                                                                            class="btn btn--primary min-w-92px">{{translate('Yes')}}</button>
                                                                                </div>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="text-center">
                                                <td colspan="8">{{translate('No_data_available')}}</td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-end">
                                    {!! $advertisements->links() !!}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="modal fade" id="setPriorityModal" tabindex="-1" aria-labelledby="setPriorityModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-sm-5 pb-sm-5">
                        <h4 class="mb-2">{{ translate('Set Priority') }}</h4>
                        <p>{{ translate('Customize Ad Placement for Enhanced Visibility') }}</p>
                        <form>
                            @csrf
                            <div class="mb-30">
                                <label for="priority" class="form-label">{{ translate('Priority') }}</label>
                                <select class="form-select" name="priority" id="priority" aria-label="priority setup">
                                    @for($i=1; $i<=\Modules\PromotionManagement\Entities\Advertisement::where('priority', '!=', null)->count()+1; $i++)
                                        <option value="{{$i}}">{{$i}}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="d-flex">
                                <button type="submit"
                                        class="btn btn--primary flex-grow-1">{{ translate('Set') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script>
        "use strict";

        $('.delete_section').on('click', function () {
            let itemId = $(this).data('id');
            form_alert('delete-' + itemId, '{{ translate('want_to_delete_this') }}?');
        })

        $('.set-priority').on('click', function () {
            const editModal = $('#setPriorityModal');
            editModal.find('input[name=priority]').val($(this).data('priority'));
            editModal.find('form').attr('action', $(this).data('action'));
        })

        $(document).ready(function () {
            let alert = $('.alert--message-2');

            setTimeout(function () {
                alert.removeClass('show active').addClass('fade');
            }, 5000);

            alert.find('.btn-close').on('click', function () {
                alert.removeClass('show active').addClass('fade');
            });
        });

    </script>
@endpush
