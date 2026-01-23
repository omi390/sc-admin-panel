@extends('adminmodule::layouts.master')

@section('title',translate('Request_Details'))

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-wrap mb-3 d-flex justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <h2 class="page-title">{{translate('request_Details')}}</h2>

                        <span class="material-icons ripple-animation" data-bs-toggle="tooltip" data-bs-placement="top"
                              data-bs-title="{{translate('This booking request includes custom instruction by the customer. Please read all the detailed requirements before accepting the request')}}"
                              type="button">info</span>
                    </div>

                    @can('booking_delete')
                    <a type="button" class="action-btn btn--danger rounded-circle" style="--size: 30px"
                       data-bs-toggle="modal"
                       data-bs-target="#exampleModal--{{$post['id']}}">
                        <span class="material-symbols-outlined">delete</span>
                    </a>
                        @endcan
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="card bg-primary-light shadow-none">
                                    <div class="card-body pb-5">
                                        <div class="media flex-wrap gap-3">
                                            <img width="140" class="radius-10"
                                                 src="{{$post?->customer?->profile_image_full_path}}"
                                                 alt="{{ translate('profile_image') }}">
                                            <div class="media-body">
                                                <div class="d-flex align-items-center gap-2 mb-3">
                                                    <span class="material-icons text-primary">person</span>
                                                    <h4>{{translate('Customer Information')}}</h4>
                                                </div>
                                                <h5 class="text-primary mb-2">{{$post?->customer?->first_name.' '.$post?->customer?->last_name}}</h5>

                                                <div class="fs-12 text-muted">0.8km away from you</div>

                                                <p class="text-muted fs-12">
                                                    @if($distance)
                                                        {{$distance}} {{translate('away from you')}}
                                                    @endif
                                                </p>
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <span class="material-icons">phone_iphone</span>
                                                    <a href="tel:{{$post?->customer?->phone}}">{{$post?->customer?->phone}}</a>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="material-icons">map</span>
                                                    <p>{{Str::limit($post?->service_address?->address??translate('not_available'), 100)}}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card bg-primary-light shadow-none">
                                    <div class="card-body pb-5">
                                        <div class="d-flex align-items-center gap-2 mb-3">
                                            <img width="18"
                                                 src="{{asset('public/assets/provider-module')}}/img/media/more-info.png"
                                                 alt="">
                                            <h4>{{translate('Service Information')}}</h4>
                                        </div>
                                        <div class="media gap-2 mb-4">
                                            <img width="30"
                                                 src="{{$post?->sub_category?->image_full_path}}"
                                                 alt="{{ translate('sub_category') }}">
                                            <div class="media-body">
                                                <h5>{{$post?->service?->name}}</h5>
                                                <div class="text-muted fs-12">{{$post?->sub_category?->name}}</div>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-column gap-2">
                                            <div class="fw-medium">{{translate('Booking Request Time')}} : <span
                                                    class="fw-bold">{{$post->created_at->format('d/m/Y h:ia')}}</span>
                                            </div>
                                            <div class="fw-medium">{{translate('Service Time')}} : <span
                                                    class="fw-bold">{{date('d/m/Y h:ia',strtotime($post->booking_schedule))}}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card">
                                    <div
                                        class="card-header d-flex align-items-center gap-2 bg-primary-light shadow-none">
                                        <img width="18"
                                             src="{{asset('public/assets/provider-module')}}/img/icons/instruction.png"
                                             alt="">
                                        <h5 class="text-uppercase">{{translate('Additional Instruction')}}</h5>
                                    </div>
                                    <div class="card-body pb-4">
                                        <ul class="d-flex flex-column gap-3 px-3 instruction-details">
                                            @forelse($post?->addition_instructions as $item)
                                                <li>{{$item->details}}</li>
                                            @empty
                                                <span>{{translate('No_Addition_Instructions')}}</span>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div
                                        class="card-header d-flex align-items-center gap-2 bg-primary-light shadow-none">
                                        <img width="18"
                                             src="{{asset('public/assets/provider-module')}}/img/icons/edit-info.png"
                                             alt="">
                                        <h5 class="text-uppercase">{{translate('Service Description')}}</h5>
                                    </div>
                                    <div class="card-body pb-4">
                                        <p>{{$post->service_description}}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div
                                        class="card-header d-flex align-items-center gap-2 bg-primary-light shadow-none">
                                        <img width="18"
                                             src="{{asset('public/assets/provider-module')}}/img/icons/provider.png"
                                             alt="">
                                        <h5 class="text-uppercase">{{translate('other_provider_offering')}}</h5>
                                    </div>
                                    <div class="card-body pb-4">
                                        @forelse($post->bids as $item)
                                            <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
                                                <div class="media gap-3">
                                                    <div class="avatar avatar-lg">
                                                        <img class="rounded"
                                                             src="{{onErrorImage(
                                                                    $item?->provider?->logo,
                                                                    asset('storage/app/public/provider/logo').'/' . $item?->provider?->logo,
                                                                    asset('public/assets/placeholder.png') ,
                                                                    'provider/logo/')}}"
                                                             alt="{{ translate('provider-logo') }}">
                                                    </div>
                                                    <div class="media-body">
                                                        <h5>{{$item?->provider?->company_name}}</h5>
                                                        <div
                                                            class="fs-12 d-flex flex-wrap align-items-center gap-2 mt-1">
                                                        <span class="common-list_rating d-flex gap-1">
                                                            <span class="material-icons text-primary fs-12">star</span>
                                                            {{$item?->provider?->avg_rating??0}}
                                                        </span>
                                                            <span>{{$item?->provider?->rating_count??0}} {{translate('Reviews')}}</span>
                                                        </div>
                                                        <div
                                                            class="d-flex gap-2 flex-wrap align-items-center fs-12 mt-1">
                                                                    <span
                                                                        class="text-danger">{{translate('price offered')}}</span>
                                                            <h4 class="text-primary">{{with_currency_symbol($item->offered_price??0)}}</h4>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div>
                                                    <button class="dropdown-item d-flex gap-2" data-bs-toggle="modal"
                                                            data-bs-target="#providerInformationModal--{{$item?->provider?->id}}">
                                                        {{translate('View Details')}}
                                                        <span class="material-symbols-outlined">chevron_right</span>
                                                    </button>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="d-flex justify-content-between gap-3 mb-4">
                                                <span>{{translate('No provider offering for the post')}}</span>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header pb-0 border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-5">
                    <div class="d-flex flex-column align-items-center gap-2 text-center">
                        <img src="{{asset('public/assets/provider-module')}}/img/icons/alert.png" alt="">
                        <h3>{{translate('Alert')}}!</h3>
                        <p class="fw-medium">
                            {{translate('This request is with customized instructions. Please read the customer description and instructions thoroughly and place your pricing according to this')}}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModal--{{$post['id']}}"
         tabindex="-1"
         aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body pt-5 p-md-5">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                    <div class="d-flex justify-content-center mb-4">
                        <img width="75" height="75" src="{{asset('public/assets/admin-module/img/media/delete.png')}}"
                             class="rounded-circle" alt="">
                    </div>

                    <h3 class="text-center mb-1 fw-medium">{{translate('Are you sure you want to delete the post?')}}</h3>
                    <p class="text-center fs-12 fw-medium text-muted">{{translate('You will lost the customer booking request?')}}</p>
                    <form method="post"
                          action="{{route('admin.booking.post.delete', [$post->id])}}">
                        @csrf
                        <div class="form-floating">
                            <textarea class="form-control resize-none" placeholder="{{translate('Cancellation Note')}}"
                                      name="post_delete_note" required id="add-your-notes"></textarea>

                            <label for="add-your-notes" class="d-flex align-items-center gap-1">
                                {{translate('Cancellation Note')}}
                            </label>
                        </div>
                        <div class="d-flex justify-content-center gap-3 mt-3">
                            <button type="button" class="btn btn--secondary"
                                    data-bs-dismiss="modal">{{translate('cancel')}}</button>
                            <button type="submit" class="btn btn-danger">{{translate('Delete')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @foreach($post->bids as $item)
        <div class="modal fade" id="providerInformationModal--{{$item?->provider?->id}}" tabindex="-1"
             aria-labelledby="alertModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header pb-0 border-0">
                        <h3>{{translate('Provider Information')}}</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-sm-5">
                        <div class="d-flex justify-content-between gap-3 mb-4">
                            <div class="media gap-3">
                                <div class="avatar avatar-lg">
                                    <img class="rounded"
                                         src="{{onErrorImage(
                                              $item?->provider?->logo,
                                              asset('storage/app/public/provider/logo').'/' . $item?->provider?->logo,
                                              asset('public/assets/placeholder.png') ,
                                              'provider/logo/')}}"
                                         alt="{{ translate('provider-logo') }}">
                                </div>
                                <div class="media-body">
                                    <div class="d-flex justify-content-between">
                                        <h5>{{$item?->provider?->company_name}}</h5>
                                        <div>{{$item->created_at->format('Y-m-d h:ia')}}</div>
                                    </div>
                                    <div class="fs-12 d-flex flex-wrap align-items-center gap-2 mt-1">
                                            <span class="common-list_rating d-flex gap-1">
                                                <span class="material-icons text-primary fs-12">star</span>
                                                {{$item?->provider?->avg_rating??0}}
                                            </span>
                                        <span>{{$item?->provider?->rating_count??0}} {{translate('Reviews')}}</span>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap align-items-center fs-12 mt-1">
                                        <span class="text-danger">{{translate('price offered')}}</span>
                                        <h4 class="text-primary">{{with_currency_symbol($item->offered_price??0)}}</h4>
                                    </div>
                                    @if($item->provider_note)
                                        <div>
                                            <span>{{translate('Description')}}:</span>
                                            <p>{{$item->provider_note}}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

