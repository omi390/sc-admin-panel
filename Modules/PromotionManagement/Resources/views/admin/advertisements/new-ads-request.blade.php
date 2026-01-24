@extends('adminmodule::layouts.master')

@section('title',translate('New Ads Request'))

@section('content')

    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap d-flex align-items-center justify-content-between gap-2 mb-3">
                        <h2 class="page-title">{{translate('Advertisement Requests')}}<span style="background-color: #0461A514" class="py-1 px-2 m-2 radius-50 title-color">{{$advertisementsNewCount + $advertisementsUpdateCount+$advertisementsExpiredCount}}</span></h2>
                        <div class="ripple-animation" data-bs-toggle="tooltip" data-bs-placement="top"
                             data-bs-title="info" type="button">
                            <img src="{{asset('/public/assets/admin-module/img/info.svg')}}" class="svg" alt="">
                        </div>
                    </div>

                    <div
                        class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                        <ul class="nav nav--tabs">
                            <li class="nav-item">
                                <a class="nav-link {{$queryParams['status'] == 'new' ? 'active' : ''}}"
                                   href="{{url()->current()}}?status=new">{{translate('New')}}
                                    <sup class="c2-bg py-1 px-2 radius-50 text-white">{{$advertisementsNewCount}}</sup></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$queryParams['status'] == 'update_request' ? 'active' : ''}}"
                                   href="{{url()->current()}}?status=update_request">{{translate('Update Request')}}
                                    <sup
                                        class="c2-bg py-1 px-2 radius-50 text-white">{{$advertisementsUpdateCount}}</sup></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$queryParams['status'] == 'expired_request' ? 'active' : ''}}"
                                   href="{{url()->current()}}?status=expired_request">{{translate('Expired')}}
                                    <sup
                                        class="c2-bg py-1 px-2 radius-50 text-white">{{$advertisementsExpiredCount}}</sup></a>
                            </li>
                        </ul>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">

                                <form
                                    action="{{route('admin.advertisements.new-ads-request', ['status' => $queryParams['status']])}}"
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
                                                       href="{{route('admin.advertisements.download', ['search' => $queryParams['search'], 'status' => $queryParams['status']])}}">{{translate('excel')}}</a>
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
                                        <th class="text-center">{{translate('Status')}}</th>
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
                                            <td>
                                                <div class="d-flex justify-content-center">
                                                    <label class="badge badge-info bg-transparent p-0">
                                                        <label
                                                            class="badge badge-info">{{ $advertisement->status }}</label>
                                                    </label>
                                                </div>
                                                @php
                                                    $end_date = \Carbon\Carbon::parse($advertisement->end_date)->startOfDay();
                                                    $today = \Carbon\Carbon::today();
                                                @endphp
                                                @if($end_date < $today)
                                                    <div class="text-center">
                                                        <small class="text-muted text-center">({{translate('Expired')}}
                                                            )</small>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center">
                                                    <div class="dropdown dropdown__style--two">
                                                        <button type="button" class="bg-transparent border-0"
                                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                            <span class="material-symbols-outlined title-color">more_vert</span>
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
                                                                    <span class="material-symbols-outlined">edit</span>
                                                                    {{ translate('Edit Ads') }}
                                                                </a>
                                                            @endcan
                                                            @if($advertisement->status == 'pending')
                                                                @can('advertisement_manage_status')
                                                                    @if($advertisement->end_date < \Carbon\Carbon::today())
                                                                        <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                           href="#" data-bs-toggle="modal"
                                                                           data-bs-target="#approveModals-{{$advertisement->id}}">
                                                                        <span
                                                                            class="material-symbols-outlined">close</span>
                                                                            {{ translate('Approve Ads') }}
                                                                        </a>
                                                                    @else
                                                                        <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                           href="{{route('admin.advertisements.status-update',[$advertisement->id, 'approved'])}}">
                                                                        <span
                                                                            class="material-symbols-outlined">done</span>
                                                                            {{ translate('Approve Ads') }}
                                                                        </a>
                                                                    @endif
                                                                @endcan
                                                            @endif
                                                            @if($advertisement->status == 'pending')
                                                                @can('advertisement_manage_status')
                                                                    <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                       href="#" data-bs-toggle="modal"
                                                                       data-bs-target="#deniedModal-{{$advertisement->id}}">
                                                                        <span
                                                                            class="material-symbols-outlined">close</span>
                                                                        {{ translate('Deny Ads') }}
                                                                    </a>
                                                                @endcan
                                                            @endif

                                                            @if($advertisement->status == 'running')
                                                                @can('advertisement_manage_status')
                                                                    <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                       href="#" data-bs-toggle="modal"
                                                                       data-bs-target="#pausedModal-{{$advertisement->id}}">
                                                                        <span class="material-symbols-outlined">pause_circle</span>{{ translate('Pause Ads') }}
                                                                    </a>
                                                                @endcan
                                                            @endif
                                                            @if($advertisement->status == 'paused')
                                                                @can('advertisement_manage_status')
                                                                    <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                       href="#" data-bs-toggle="modal"
                                                                       data-bs-target="#resumeModal-{{$advertisement->id}}">
                                                                        <span class="material-symbols-outlined">play_arrow</span>{{ translate('Resume Ads') }}
                                                                    </a>
                                                                @endcan
                                                            @endif
                                                            @if($advertisement->status == 'expired')
                                                                @can('advertisement_update')
                                                                    <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                       href="#">
                                                                        <span
                                                                            class="material-symbols-outlined">history</span>{{ translate('Re-submit Ads') }}
                                                                    </a>
                                                                @endcan
                                                            @endif
                                                            @can('advertisement_delete')
                                                                <a class="dropdown-item d-flex gap-2 align-items-center delete_section"
                                                                   href="#" data-id="{{$advertisement->id}}">
                                                                    <span
                                                                        class="material-symbols-outlined">delete</span>{{ translate('Delete Ads') }}
                                                                </a>
                                                                <form
                                                                    action="{{route('admin.advertisements.delete',[$advertisement->id])}}"
                                                                    method="post" id="delete-{{$advertisement->id}}"
                                                                    class="hidden">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                </form>
                                                            @endcan
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
                                                                          action="{{route('admin.advertisements.status-update',[$advertisement->id, 'approved'])}}">
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

                                                    <div class="modal fade" id="approveModals-{{$advertisement->id}}"
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
                                                                             src="{{asset('public/assets/admin-module/img/media/accept.png')}}"
                                                                             class="rounded-circle" alt="">
                                                                    </div>

                                                                    <h3 class="text-start mb-1 fw-medium text-center">{{translate('Are you sure you want to Approve the request?')}}</h3>
                                                                    <p class="text-start fs-12 fw-medium text-muted text-center">{{translate('The ad date has already expired. If you approve it will be expired.')}}</p>
                                                                    <form method="post"
                                                                          action="{{route('admin.advertisements.status-update',[$advertisement->id, 'approved'])}}">
                                                                        @csrf
                                                                        <div class="form-floating">
                                                                            <div
                                                                                class="d-flex justify-content-center mt-3 gap-3">
                                                                                <a href="{{route('admin.advertisements.edit',[$advertisement->id])}}"
                                                                                   class="btn btn--secondary min-w-92px px-2">{{translate('Edit Ads')}}</a>
                                                                                <a href="{{route('admin.advertisements.status-update',[$advertisement->id, 'approved'])}}"
                                                                                        class="btn btn--primary min-w-92px">{{translate('Approve')}}</a>
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
    </script>
@endpush
