@extends('providermanagement::layouts.master')

@section('title',translate('available_services'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('All_Service')}}</h2>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            @if(count($categories) > 0)
                                <div class="service-list-wrap">
                                    <ul class="services-tab-menu">
                                        <li class="{{$activeCategory=='all'?'active':''}}">
                                            <a href="{{url()->current()}}?active_category=all">{{translate('all')}}</a>
                                        </li>

                                        @foreach($categories as $category)
                                            <li id="list-{{$category->id}}"
                                                class="{{$activeCategory==$category->id?'active':''}}">
                                                <a href="{{url()->current()}}?active_category={{$category->id}}">
                                                    {{$category->name}}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>

                                    @if(count($subCategories) > 0)
                                        <div class="service-list">
                                            @foreach($subCategories as $sub)
                                                <div class="service-list-item">
                                                    <div class="service-img">
                                                        <a>
                                                            <img src="{{$sub->image_full_path}}" alt="{{translate('image')}}">
                                                        </a>
                                                    </div>
                                                    <div class="service-content">
                                                        <a href="" class="service-title cursor-auto" data-bs-toggle="modal"
                                                           data-bs-target="#showServiceModal">
                                                            {{$sub->name}}
                                                        </a>
                                                        <div class="service-actions">
                                                            @if($sub->services_count > 0)
                                                                <button type="button" class="btn btn-link text-capitalize"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#modal-{{$sub->id}}">
                                                                    <strong>{{$sub->services_count}}</strong>{{translate('services')}}
                                                                </button>
                                                            @endif
                                                            <form action="javascript:void(0)" method="post"
                                                                  class="hide-div"
                                                                  id="form-{{$sub->id}}">
                                                                @csrf
                                                                @method('put')
                                                                <input name="sub_category_id" value="{{$sub->id}}">
                                                            </form>

                                                            @if(in_array($sub->id,$subscribedIds))
                                                                <button type="button" class="btn btn--danger update-service-subscription"
                                                                        id="button-{{$sub->id}}"
                                                                        data-id="{{$sub->id}}">
                                                                    {{translate('unsubscribe')}}
                                                                </button>
                                                            @else
                                                                <button type="button" class="btn btn--primary update-service-subscription"
                                                                        id="button-{{$sub->id}}"
                                                                        data-id="{{$sub->id}}">
                                                                    {{translate('subscribe')}}
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="d-flex justify-content-center align-items-center h-100">
                                            <span class="text-muted">{{translate('No Sub Category Available')}}</span>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <span>{{translate('No_available_services')}}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @foreach($subCategories as $sub)
        <div class="modal fade" id="modal-{{$sub->id}}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body p-30">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                        <div class="text-center">
                            <div class="img-wrap-circle mx-auto mb-20">
                                <img src="{{$sub->image_full_path}}"
                                     alt="{{translate('image')}}">
                            </div>
                            <h4 class="mb-20">
                                <strong>( {{$sub->services_count}} )</strong>
                                {{translate('services')}}
                            </h4>
                        </div>

                        <ul class="list-unstyled d-flex flex-wrap gap-3 justify-content-center">
                            @foreach($sub->services as $service)
                                <li class="d-flex flex-column gap-2 justify-content-center service_cursor_pointer provider-service-detail"
                                    data-route="{{route('provider.service.detail',[$service->id])}}">
                                    <img alt="{{translate('image')}}"
                                         src="{{$service->thumbnail_full_path}}"
                                         class="mx-auto img-square-90" width="90">
                                    <div class="fw-medium">
                                        {{\Illuminate\Support\Str::limit($service->name,15)}}
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

@endsection

@push('script')
    <script>
        "use strict";

        $(".provider-service-detail").on('click', function (){
            let route = $(this).data('route')
            location.href = route;
        })

        $(".update-service-subscription").on('click', function (){
            let id = $(this).data('id')
            update_subscription(id)
        })

        function update_subscription(id) {

            var form = $('#form-' + id)[0];
            var formData = new FormData(form);

            Swal.fire({
                title: "{{translate('are_you_sure')}}?",
                text: "{{translate('want_to_update_subscription')}}",
                type: 'warning',
                showCloseButton: true,
                showCancelButton: true,
                cancelButtonColor: 'var(--c2)',
                confirmButtonColor: 'var(--c1)',
                cancelButtonText: '{{translate('cancel')}}',
                confirmButtonText: '{{translate('yes')}}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    send_request(formData)

                    const subscribe_button = document.querySelector('#button-' + id);
                    if (subscribe_button.classList.contains('btn--danger')) {
                        subscribe_button.classList.remove('btn--danger');
                        subscribe_button.classList.add('btn--primary');
                        $('#button-' + id).text('{{translate('subscribe')}}')
                    } else {
                        subscribe_button.classList.remove('btn--primary');
                        subscribe_button.classList.add('btn--danger');
                        $('#button-' + id).text('{{translate('unsubscribe')}}')
                    }
                }
            })
        }

        function send_request(formData) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('provider.service.update-subscription')}}",
                data: formData,
                processData: false,
                contentType: false,
                type: 'post',
                beforeSend: function () {
                    $('.preloader').show()
                },
                success: function (response) {
                    if (response.response_code === 'default_200') {
                        toastr.success('successfully data fetched');

                    } else if(response.response_code === 'default_204'){
                        toastr.warning('{{translate('this_category_is_not_available_in_your_zone')}}')

                    } else {
                        toastr.error('{{translate('your_subscription_package_category_limit_has_ended')}}');
                    }
                    location.reload();
                },
                error: function (response) {
                    toastr.error('{{translate('your_subscription_package_category_limit_has_ended')}}');
                    location.reload();
                },
                complete: function () {
                    $('.preloader').hide()
                }
            });
        }


    </script>
@endpush
