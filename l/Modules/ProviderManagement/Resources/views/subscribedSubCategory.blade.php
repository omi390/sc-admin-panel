@extends('providermanagement::layouts.master')

@section('title',translate('My_Subscriptions'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('My_Subscriptions')}}</h2>
                    </div>

                    <div
                        class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                        <ul class="nav nav--tabs">
                            <li class="nav-item">
                                <a class="nav-link {{$status == 'all' ? 'active' : ''}}"
                                   href="{{url()->current()}}?status=all">{{translate('All')}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$status == 'subscribed' ? 'active' : ''}}"
                                   href="{{url()->current()}}?status=subscribed">{{translate('Subscribed_Sub_categories')}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$status == 'unsubscribed' ? 'active' : ''}}"
                                   href="{{url()->current()}}?status=unsubscribed">{{translate('Unsubscribed_Sub_categories')}}</a>
                            </li>
                        </ul>

                        <div class="d-flex gap-2 fw-medium">
                            <span class="opacity-75">{{translate('Total_Sub_Categories')}}:</span>
                            <span class="title-color">{{$subscribedSubCategories->total()}}</span>
                        </div>
                    </div>

                    <div class="tab-content">
                        <div class="">
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="example" class="table align-middle">
                                            <thead>
                                            <tr>
                                                <th>{{translate('SL')}}</th>
                                                <th>{{translate('Sub_Category_Name')}}</th>
                                                <th>{{translate('Category')}}</th>
                                                <th>{{translate('Services')}}</th>
                                                <th class="text-center">{{translate('Action')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($subscribedSubCategories as $key=>$sub_category)
                                                <tr>
                                                    <td>{{$subscribedSubCategories->firstitem()+$key}}</td>
                                                    <td>{{ Str::limit($sub_category->sub_category['name']??translate('Unavailable'), 30) }}</td>
                                                    <td>{{ Str::limit($sub_category->category['name']??translate('Unavailable'), 30) }}</td>
                                                    <td>
                                                        <div
                                                            class="service-details-info-wrap d-inline-block position-relative cursor-pointer">
                                                            <div>{{ $sub_category->sub_category->services_count ?? 0 }}</div>

                                                            @if($sub_category->services)
                                                            <div
                                                                class="service-details-info bg-dark p-2 rounded shadow">
                                                                @foreach($sub_category->services as $service)
                                                                    <div class="media gap-2 align-items-center">
                                                                        <img width="40" class="rounded" src="{{$service->thumbnail_full_path}}" alt="{{translate('image')}}">

                                                                        <div class="media-body text-white">
                                                                            <h6 class="text-white">{{\Illuminate\Support\Str::limit($service->name,15)}}</h6>
                                                                            <div class="fs-10">{{translate('Up to: ')}}
                                                                                    ${{ $service->variations->first()->price }}
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <form action="javascript:void(0)" method="post" class="hide-div"
                                                              id="form-{{$sub_category->id}}">
                                                            @csrf
                                                            @method('put')
                                                            <input name="sub_category_id"
                                                                   value="{{$sub_category->sub_category_id}}">
                                                        </form>
                                                        @if($sub_category->is_subscribed == 1)
                                                            <button type="button" class="btn btn--danger subscribe-btn"
                                                                    id="button-{{$sub_category->id}}"
                                                                    data-subcategory="{{$sub_category->id}}">
                                                                {{translate('unsubscribe')}}
                                                            </button>
                                                        @else
                                                            <button type="button" class="btn btn--primary subscribe-btn"
                                                                    id="button-{{$sub_category->id}}"
                                                                    data-subcategory="{{$sub_category->id}}">
                                                                {{translate('subscribe')}}
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        {!! $subscribedSubCategories->links() !!}
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

    <script>
        "use strict";

        $('.subscribe-btn').on('click', function () {
            let id = $(this).data('subcategory');
            update_subscription(id)
        });

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
                    send_request(formData, id);
                }
            })
        }

        function update_view(id) {
            const subscribe_button = $('#button-' + id);
            if (subscribe_button.hasClass('btn--danger')) {
                subscribe_button.removeClass('btn--danger').addClass('btn--primary').text('{{translate('subscribe')}}');
            } else {
                subscribe_button.removeClass('btn--primary').addClass('btn--danger').text('{{translate('unsubscribe')}}');
            }
            subscribe_button.blur();
        }


        function send_request(formData, id) {
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
                        update_view(id)

                    } else if(response.response_code === 'default_204'){
                        toastr.warning('{{translate('this_category_is_not_available_in_your_zone')}}')

                    } else {
                        toastr.error('{{translate('your_subscription_package_category_limit_has_ended')}}');
                    }
                    location.reload();
                },
                error: function (response) {
                    toastr.error('{{translate('your_subscription_package_category_limit_has_ended')}}')
                },
                complete: function () {
                    $('.preloader').hide()
                }
            });
            return is_success;
        }
    </script>

@endpush
