@extends('providermanagement::layouts.master')

@section('title',translate('Ads List'))

@section('content')

    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap d-flex align-items-center justify-content-between gap-2 mb-3">
                        <h2 class="page-title">{{translate('Advertisement_List')}}</h2>
                        <div class="ripple-animation" data-bs-toggle="tooltip" data-bs-placement="top"
                             data-bs-title="{{translate('View advertisement history here. You can see status wise advertisement history and adjust edit, pause, resume, resubmit, or delete as needed.')}}"
                             type="button">
                            <img src="{{asset('/public/assets/admin-module/img/info.svg')}}" class="svg" alt="">
                        </div>
                    </div>

                    @if($advertisements->count() > 0)
                        <div
                            class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                            <ul class="nav nav--tabs">
                                <li class="nav-item">
                                    <a class="nav-link {{$queryParam['status'] == 'all' ? 'active' : ''}}"
                                       href="{{route('provider.advertisements.ads-list', ['status' => 'all'])}}">
                                        {{translate('All')}}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{$queryParam['status'] == 'pending' ? 'active' : ''}}"
                                       href="{{route('provider.advertisements.ads-list', ['status' => 'pending'])}}">
                                        {{translate('Pending')}}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{$queryParam['status'] == 'approved' ? 'active' : ''}}"
                                       href="{{route('provider.advertisements.ads-list', ['status' => 'approved'])}}">
                                        {{translate('Approved')}}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{$queryParam['status'] == 'running' ? 'active' : ''}}"
                                       href="{{route('provider.advertisements.ads-list', ['status' => 'running'])}}">
                                        {{translate('Running')}}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{$queryParam['status'] == 'paused' ? 'active' : ''}}"
                                       href="{{route('provider.advertisements.ads-list', ['status' => 'paused'])}}">
                                        {{translate('Paused')}}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{$queryParam['status'] == 'expired' ? 'active' : ''}}"
                                       href="{{route('provider.advertisements.ads-list', ['status' => 'expired'])}}">
                                        {{translate('Expired')}}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{$queryParam['status'] == 'denied' ? 'active' : ''}}"
                                       href="{{route('provider.advertisements.ads-list', ['status' => 'denied'])}}">
                                        {{translate('Denied')}}
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                    <form action="" class="search-form search-form_style-two" method="POST">
                                        @csrf
                                        <div class="input-group search-form__input_group">
                                            <span class="search-form__icon">
                                                <span class="material-icons">search</span>
                                            </span>
                                            <input type="search" class="theme-input-style search-form__input"
                                                   value="{{$queryParam['search'] }}" name="search"
                                                   placeholder="{{translate('search_by_id_or_title')}}">
                                        </div>
                                        <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                                    </form>

                                    <div class="d-flex flex-wrap align-items-center gap-3">
                                        <div
                                            class="page-title-wrap d-flex justify-content-between flex-wrap align-items-center gap-3">
                                            <a href="{{route('provider.advertisements.ads-create')}}"
                                               class="btn btn--primary">{{translate('create_ads')}}</a>
                                        </div>
                                        <div class="dropdown">
                                            <button type="button"
                                                    class="btn btn--secondary text-capitalize dropdown-toggle"
                                                    data-bs-toggle="dropdown">
                                                <span
                                                    class="material-icons">file_download</span> {{translate('download')}}
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                <li><a class="dropdown-item"
                                                       href="{{route('provider.advertisements.download', ['status' => $queryParam['status'], 'search' => $queryParam['search']])}}">{{translate('excel')}}</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table id="example" class="table align-middle">
                                        <thead class="text-nowrap">
                                        <tr>
                                            <th>{{translate('SL')}}</th>
                                            <th>{{translate('Advertisement ID')}}</th>
                                            <th>{{translate('Advertisement Title')}}</th>
                                            <th>{{translate('Ads Type')}}</th>
                                            <th>{{translate('Duration')}}</th>
                                            @if($queryParam['status'] == 'all' || $queryParam['status'] == 'expired')
                                                <th class="text-center">{{translate('Status')}}</th>
                                            @endif
                                            <th class="text-center">{{translate('Action')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($advertisements as $key => $advertisement)
                                            <tr>
                                                <td>{{ $advertisements->firstitem()+$key }}</td>
                                                <td>
                                                    <a href="{{route('provider.advertisements.details',[$advertisement->id])}}">{{ $advertisement->readable_id }}</a>
                                                </td>
                                                <td>
                                                    <a class="text-capitalize" href="{{route('provider.advertisements.details',[$advertisement->id])}}">{{Str::limit($advertisement->title, 40)}}</a>
                                                </td>
                                                <td>{{ ucwords(str_replace('_', ' ', $advertisement->type)) }}</td>
                                                <td>
                                                    <div class="d-flex flex-column gap-1 fs-12">
                                                        <span>{{ $advertisement->start_date->format('Y-m-d') }}</span>
                                                        <span>{{ $advertisement->end_date->format('Y-m-d') }}</span>
                                                    </div>
                                                </td>
                                                @if($queryParam['status'] == 'all' || $queryParam['status'] == 'expired')
                                                    <td>
                                                        <div class="d-flex justify-content-center">
                                                            @if($advertisement->status == 'pending')
                                                                <label
                                                                    class="badge badge-info">{{ $advertisement->status }}</label>
                                                            @elseif(($advertisement->status == 'approved' || $advertisement->status == 'resumed') && ($advertisement->start_date <= \Carbon\Carbon::today() && $advertisement->end_date > \Carbon\Carbon::today() ) )
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
                                                                            @elseif($advertisement->status == 'denied')
                                                                                <label
                                                                                    class="badge badge-danger">{{ translate('Denied') }}</label>
                                                                            @elseif($advertisement->status == 'canceled')
                                                                                <label
                                                                                    class="badge badge-danger">{{ translate('canceled') }}</label>
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
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center">
                                                        <div class="dropdown dropdown__style--two">
                                                            <button type="button" class="bg-transparent border-0"
                                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                                <span class="material-symbols-outlined title-color">more_vert</span>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                @if(in_array($advertisement->status, ['pending', 'approved', 'paused', 'expired', 'denied', 'resumed', 'canceled']))
                                                                    <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                       href="{{route('provider.advertisements.details',[$advertisement->id])}}">
                                                                    <span
                                                                        class="material-symbols-outlined">visibility</span>
                                                                        {{translate('View Ads')}}
                                                                    </a>
                                                                @endif
                                                                @if(in_array($advertisement->status, ['pending', 'approved', 'paused', 'resumed', 'denied']))
                                                                    <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                       href="{{route('provider.advertisements.edit',[$advertisement->id])}}">
                                                                        <span
                                                                            class="material-symbols-outlined">edit</span>
                                                                        {{translate('Edit Ads')}}
                                                                    </a>
                                                                @endif

                                                                @if($advertisement->status == 'pending')
                                                                    <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                       href="#" data-bs-toggle="modal"
                                                                       data-bs-target="#canceledModal-{{$advertisement->id}}">
                                                                        <span
                                                                            class="material-symbols-outlined">close</span>
                                                                        {{translate('Cancel Ads')}}
                                                                    </a>
                                                                @endif
                                                                @if($advertisement->whereIn('status', ['approved', 'resumed'])->where('start_date', '<=', Carbon\Carbon::today())->where('end_date', '>', Carbon\Carbon::today()) && in_array($advertisement->status, ['approved', 'resumed']))
                                                                    <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                       href="#" data-bs-toggle="modal"
                                                                       data-bs-target="#pausedModal-{{$advertisement->id}}">
                                                                    <span
                                                                        class="material-symbols-outlined">pause_circle</span>
                                                                        {{translate('Pause Ads')}}
                                                                    </a>
                                                                @endif
                                                                @if($advertisement->status == 'paused')
                                                                    <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                       href="#" data-bs-toggle="modal"
                                                                       data-bs-target="#resumeModal-{{$advertisement->id}}">
                                                                    <span
                                                                        class="material-symbols-outlined">play_arrow</span>
                                                                        {{translate('Resume Ads')}}
                                                                    </a>
                                                                @endif
                                                                @if($advertisement->status == 'expired' || $advertisement->status == 'denied')
                                                                    <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                       href="{{route('provider.advertisements.re-submit',[$advertisement->id])}}">
                                                                        <span
                                                                            class="material-symbols-outlined">history</span>
                                                                        {{ translate('Re-submit Ads') }}
                                                                    </a>
                                                                @endif
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
                                                                <form
                                                                    action="{{route('provider.advertisements.delete',[$advertisement->id])}}"
                                                                    method="post" id="delete-{{$advertisement->id}}"
                                                                    class="hidden">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                </form>
                                                            </ul>
                                                        </div>

                                                        <div class="modal fade" id="deniedModal" tabindex="-1"
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
                                                                              action="{{route('provider.advertisements.status-update',[$advertisement->id, 'denied'])}}">
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
                                                                              action="{{route('provider.advertisements.status-update',[$advertisement->id, 'paused'])}}">
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

                                                        <div class="modal fade"
                                                             id="canceledModal-{{$advertisement->id}}"
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

                                                                        <h3 class="text-start mb-1 fw-medium text-center">{{translate('Are you sure you want to cancel the request?')}}</h3>
                                                                        <p class="text-start fs-12 fw-medium text-muted text-center">{{translate('You will lost the provider ads request')}}</p>
                                                                        <form method="post"
                                                                              action="{{route('provider.advertisements.status-update',[$advertisement->id, 'canceled'])}}">
                                                                            @csrf
                                                                            <div class="form-floating">
                                                                            <textarea class="form-control h-69px"
                                                                                      placeholder="{{translate('Paused Note')}}"
                                                                                      name="note" id="add-paused-note"
                                                                                      required></textarea>
                                                                                <label for="add-your-note"
                                                                                       class="d-flex align-items-center gap-1">{{translate('Cancellation Note')}}</label>
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

                                                                        <h3 class="text-start mb-1 fw-medium text-center">{{translate('Are you sure you want to Resume the request?')}}</h3>
                                                                        <p class="text-start fs-12 fw-medium text-muted text-center">{{translate('This ad will be run again and will show in the app or web')}}</p>
                                                                        <form method="post"
                                                                              action="{{route('provider.advertisements.status-update',[$advertisement->id, 'resumed'])}}">
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
                    @elseif($queryParam['status'] == 'all' && $advertisements->count() == 0)
                        <div class="card mt-5">
                            <div class="card-body">
                                <div class="d-flex flex-column gap-2 align-items-center text-center">
                                    <img width="140" class="mb-2"
                                         src="{{asset('public/assets/provider-module')}}/img/media/create-ads.png"
                                         alt="">
                                    <h4>{{translate('Advertisement List')}}</h4>
                                    <p>{{translate("Uh oh! You didn’t created any advertisement yet!")}}</p>
                                </div>
                                <hr class="my-4">
                                <div class="row justify-content-center rounded mb-5">
                                    <div class="col-xl-6 col-lg-7 col-md-8 col-sm-9">
                                        <div
                                            class="bg-light d-flex flex-column gap-2 align-items-center text-center py-4 px-4 px-xl-5">
                                            <h4 class="mb-2">{{translate('Create Advertisement to Promote Your Services')}}</h4>
                                            <p>{{translate('Here, you can showcase your services or profile to a
                                                wider audience through targeted ad campaigns. Get
                                                started by creating your first ad campaign.')}}</p>

                                            <button class="btn btn--primary">
                                                <a class="text-white"
                                                   href="{{route('provider.advertisements.ads-create')}}">{{translate('Create Ads')}}</a>
                                            </button>
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
                                    <a class="nav-link {{request()->getQueryString() == 'status=all' ? 'active' : ''}}"
                                       href="{{route('provider.advertisements.ads-list', ['status' => 'all'])}}">
                                        {{translate('All')}}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{request()->getQueryString() == 'status=pending' ? 'active' : ''}}"
                                       href="{{route('provider.advertisements.ads-list', ['status' => 'pending'])}}">
                                        {{translate('Pending')}}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{request()->getQueryString() == 'status=approved' ? 'active' : ''}}"
                                       href="{{route('provider.advertisements.ads-list', ['status' => 'approved'])}}">
                                        {{translate('Approved')}}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{request()->getQueryString() == 'status=running' ? 'active' : ''}}"
                                       href="{{route('provider.advertisements.ads-list', ['status' => 'running'])}}">
                                        {{translate('Running')}}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{$queryParam['status'] == 'paused' ? 'active' : ''}}"
                                       href="{{route('provider.advertisements.ads-list', ['status' => 'paused'])}}">
                                        {{translate('Paused')}}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{request()->getQueryString() == 'status=expired' ? 'active' : ''}}"
                                       href="{{route('provider.advertisements.ads-list', ['status' => 'expired'])}}">
                                        {{translate('Expired')}}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{request()->getQueryString() == 'status=denied' ? 'active' : ''}}"
                                       href="{{route('provider.advertisements.ads-list', ['status' => 'denied'])}}">
                                        {{translate('Denied')}}
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                    <form action="" class="search-form search-form_style-two" method="POST">
                                        @csrf
                                        <div class="input-group search-form__input_group">
                                        <span class="search-form__icon">
                                            <span class="material-icons">search</span>
                                        </span>
                                            <input type="search" class="theme-input-style search-form__input"
                                                   value="{{$queryParam['search'] }}" name="search"
                                                   placeholder="{{translate('search_here')}}">
                                        </div>
                                        <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                                    </form>

                                    <div class="d-flex flex-wrap align-items-center gap-3">
                                        <div
                                            class="page-title-wrap d-flex justify-content-between flex-wrap align-items-center gap-3">
                                            <a href="{{route('provider.advertisements.ads-create')}}"
                                               class="btn btn--primary">{{translate('create_ads')}}</a>
                                        </div>
                                        <div class="dropdown">
                                            <button type="button"
                                                    class="btn btn--secondary text-capitalize dropdown-toggle"
                                                    data-bs-toggle="dropdown">
                                                <span
                                                    class="material-icons">file_download</span> {{translate('download')}}
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                <li><a class="dropdown-item"
                                                       href="{{route('provider.advertisements.download', ['status' => $queryParam['status'], 'search' => $queryParam['search']])}}">{{translate('excel')}}</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table id="example" class="table align-middle">
                                        <thead class="text-nowrap">
                                        <tr>
                                            <th>{{translate('SL')}}</th>
                                            <th>{{translate('Advertisement ID')}}</th>
                                            <th>{{translate('Advertisement Title')}}</th>
                                            <th>{{translate('Ads Type')}}</th>
                                            <th>{{translate('Duration')}}</th>
                                            @if($queryParam['status'] == 'all' || $queryParam['status'] == 'expired')
                                                <th class="text-center">{{translate('Status')}}</th>
                                            @endif
                                            <th class="text-center">{{translate('Action')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($advertisements as $key => $advertisement)
                                            <tr>
                                                <td>{{ $advertisements->firstitem()+$key }}</td>
                                                <td>
                                                    <a href="{{route('provider.advertisements.details',[$advertisement->id])}}">{{ $advertisement->readable_id }}</a>
                                                </td>
                                                <td>{{$advertisement->title }}</td>
                                                <td>{{ ucwords(str_replace('_', ' ', $advertisement->type)) }}</td>
                                                <td>
                                                    <div class="d-flex flex-column gap-1 fs-12">
                                                        <span>{{ $advertisement->start_date->format('Y-m-d') }}</span>
                                                        <span>{{ $advertisement->end_date->format('Y-m-d') }}</span>
                                                    </div>
                                                </td>
                                                @if($queryParam['status'] == 'all' || $queryParam['status'] == 'expired')
                                                    <td>
                                                        <div class="d-flex justify-content-center">
                                                            @if($advertisement->status == 'pending')
                                                                <label
                                                                    class="badge badge-info">{{ $advertisement->status }}</label>
                                                            @elseif(($advertisement->status == 'approved' || $advertisement->status == 'resumed') && ($advertisement->start_date <= \Carbon\Carbon::today() && $advertisement->end_date > \Carbon\Carbon::today() ) )
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
                                                                            @elseif($advertisement->status == 'denied')
                                                                                <label
                                                                                    class="badge badge-danger">{{ translate('Denied') }}</label>
                                                                            @elseif($advertisement->status == 'canceled')
                                                                                <label
                                                                                    class="badge badge-danger">{{ translate('canceled') }}</label>
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
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center">
                                                        <div class="dropdown dropdown__style--two">
                                                            <button type="button" class="bg-transparent border-0"
                                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                                <span class="material-symbols-outlined title-color">more_vert</span>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                @if(in_array($advertisement->status, ['pending', 'approved', 'paused', 'expired', 'denied', 'resumed', 'canceled']))
                                                                    <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                       href="{{route('provider.advertisements.details',[$advertisement->id])}}">
                                                                    <span
                                                                        class="material-symbols-outlined">visibility</span>
                                                                        {{translate('View Ads')}}
                                                                    </a>
                                                                @endif
                                                                @if(in_array($advertisement->status, ['pending', 'approved', 'paused', 'resumed']))
                                                                    <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                       href="{{route('provider.advertisements.edit',[$advertisement->id])}}">
                                                                        <span
                                                                            class="material-symbols-outlined">edit</span>
                                                                        {{translate('Edit Ads')}}
                                                                    </a>
                                                                @endif

                                                                @if($advertisement->status == 'pending')
                                                                    <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                       href="#" data-bs-toggle="modal"
                                                                       data-bs-target="#canceledModal-{{$advertisement->id}}">
                                                                        <span
                                                                            class="material-symbols-outlined">close</span>
                                                                        {{translate('Cancel Ads')}}
                                                                    </a>
                                                                @endif
                                                                @if($advertisement->whereIn('status', ['approved', 'resumed'])->where('start_date', '<=', Carbon\Carbon::today())->where('end_date', '>', Carbon\Carbon::today()) && in_array($advertisement->status, ['approved', 'resumed']))
                                                                    <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                       href="#" data-bs-toggle="modal"
                                                                       data-bs-target="#pausedModal-{{$advertisement->id}}">
                                                                    <span
                                                                        class="material-symbols-outlined">pause_circle</span>
                                                                        {{translate('Pause Ads')}}
                                                                    </a>
                                                                @endif
                                                                @if($advertisement->status == 'paused')
                                                                    <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                       href="#" data-bs-toggle="modal"
                                                                       data-bs-target="#resumeModal-{{$advertisement->id}}">
                                                                    <span
                                                                        class="material-symbols-outlined">play_arrow</span>
                                                                        {{translate('Resume Ads')}}
                                                                    </a>
                                                                @endif
                                                                @if($advertisement->status == 'expired' || $advertisement->status == 'denied')
                                                                    <a class="dropdown-item d-flex gap-2 align-items-center"
                                                                       href="#">
                                                                    <span
                                                                        class="material-symbols-outlined">history</span>
                                                                        {{translate('Re-submit Ads')}}
                                                                    </a>
                                                                @endif

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
                                                                <form
                                                                    action="{{route('provider.advertisements.delete',[$advertisement->id])}}"
                                                                    method="post" id="delete-{{$advertisement->id}}"
                                                                    class="hidden">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                </form>
                                                            </ul>
                                                        </div>

                                                        <div class="modal fade" id="deniedModal" tabindex="-1"
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
                                                                              action="{{route('provider.advertisements.status-update',[$advertisement->id, 'denied'])}}">
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
                                                                              action="{{route('provider.advertisements.status-update',[$advertisement->id, 'paused'])}}">
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

                                                        <div class="modal fade"
                                                             id="canceledModal-{{$advertisement->id}}"
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

                                                                        <h3 class="text-start mb-1 fw-medium text-center">{{translate('Are you sure you want to cancel the request?')}}</h3>
                                                                        <p class="text-start fs-12 fw-medium text-muted text-center">{{translate('You will lost the provider ads request')}}</p>
                                                                        <form method="post"
                                                                              action="{{route('provider.advertisements.status-update',[$advertisement->id, 'canceled'])}}">
                                                                            @csrf
                                                                            <div class="form-floating">
                                                                            <textarea class="form-control h-69px"
                                                                                      placeholder="{{translate('Paused Note')}}"
                                                                                      name="note" id="add-paused-note"
                                                                                      required></textarea>
                                                                                <label for="add-your-note"
                                                                                       class="d-flex align-items-center gap-1">{{translate('Cancellation Note')}}</label>
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

                                                                        <h3 class="text-start mb-1 fw-medium text-center">{{translate('Are you sure you want to Resume the request?')}}</h3>
                                                                        <p class="text-start fs-12 fw-medium text-muted text-center">{{translate('This ad will be run again and will show in the app or web')}}</p>
                                                                        <form method="post"
                                                                              action="{{route('provider.advertisements.status-update',[$advertisement->id, 'resumed'])}}">
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
    </div>

    {{-- Ad Delete Modal --}}
    <div class="modal fade" id="adDeleteModal" tabindex="-1"
         aria-labelledby="adDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body pt-5 p-md-5">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="d-flex justify-content-center mb-4">
                        <img width="75" height="75" src="{{asset('public/assets/admin-module/img/ad_delete.svg')}}"
                             class="rounded-circle" alt="">
                    </div>

                    <h3 class="text-start mb-1 fw-medium text-center">{{translate('You can’t delete the ad')}}</h3>
                    <p class="text-start fs-12 fw-medium text-muted text-center">{{translate('This ad will be run again and will show in the app or web')}}</p>
                    <div class="d-flex justify-content-center mt-3 gap-3">
                        <button type="button" class="btn btn--danger min-w-92px px-2" data-bs-dismiss="modal"
                                aria-label="Close">{{translate('Okay')}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Confirm Ad Delete Modal --}}
    <div class="modal fade" id="confirmAdDeleteModal" tabindex="-1"
         aria-labelledby="confirmAdDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body pt-5 p-md-5">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="d-flex justify-content-center mb-4">
                        <img width="75" height="75" src="{{asset('public/assets/admin-module/img/delete.png')}}"
                             class="rounded-circle" alt="">
                    </div>

                    <h3 class="text-start mb-1 fw-medium text-center">{{translate('Confirm Ad Deletion')}}</h3>
                    <p class="text-start fs-12 fw-medium text-muted text-center">{{translate('Deleting this ad will remove it permanently. Are you sure you want to proceed?')}}</p>
                    <div class="d-flex justify-content-center mt-3 gap-3">
                        <button type="button" class="btn btn--secondary min-w-92px px-2" data-bs-dismiss="modal"
                                aria-label="Close">{{translate('Not Now')}}</button>
                        <button type="submit" class="btn btn--danger min-w-92px">{{translate('Yes')}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ad Create Success Modal -->
    <div class="modal fade" id="adCreateSuccessModal" tabindex="-1" aria-labelledby="adCreateSuccessModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-sm-5">
                    <div class="d-flex flex-column gap-2 align-items-center text-center">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                        <img width="250"
                             src="{{asset('public/assets/provider-module/img/media/ad-create-success.png')}}"
                             alt="{{translate('image')}}">
                        <h4 class="mb-2">{{translate('Ad Created Successfully')}}</h4>
                        <p>{{translate("Congratulations on creating your ad! It's now awaiting approval. To finalize the process and make payment arrangements, please contact our")}}
                            <a href="{{route('provider.chat.index', ['user_type' => 'super_admin'])}}"
                               class="c1 text-decoration-underline fw-bold">{{translate('admin directly')}}</a>. {{translate('We look forward to helping you boost your visibility and reach more customers.')}}
                        </p>

                        <button type="button" class="btn btn--primary text-capitalize"
                                data-bs-dismiss="modal">{{translate('Okay')}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script>
        $('.delete_section').on('click', function () {
            let itemId = $(this).data('id');
            form_alert('delete-' + itemId, '{{ translate('Deleting this ad will remove it permanently') }}?');
        })
    </script>

    @if(session('newItemAdded'))
        <script>
            $(document).ready(function () {
                $("#adCreateSuccessModal").modal('show');
            });
        </script>
    @endif
@endpush
