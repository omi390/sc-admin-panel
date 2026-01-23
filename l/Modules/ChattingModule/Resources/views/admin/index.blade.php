@extends('adminmodule::layouts.master')

@section('title',translate('chat_list'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/select2/select2.min.css')}}"/>
    <link rel="stylesheet" href="{{asset('public/assets/css/lightbox.css')}}">
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title d-flex gap-3 align-items-center">
                    {{translate('Messages')}}
                    <span class="badge bg--secondary fs-6">{{$chatList->count()}}</span>
                </h2>
            </div>

            <div class="row gx-1">
                <div class="col-xl-3 col-lg-4">
                    <div class="card card-body px-0 h-100">
                        <div class="media align-items-center px-3 gap-3 mb-2">
                            <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                                <ul class="nav nav--tabs">
                                    <li class="nav-item">
                                        <a class="nav-link {{$type=='customer'?'active':''}}"
                                           href="{{url()->current()}}?user_type=customer">
                                            {{translate('customer')}}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{$type=='provider_serviceman'?'active':''}}"
                                           href="{{url()->current()}}?user_type=provider_serviceman">
                                            {{translate('Service Man')}}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{$type=='provider_admin'?'active':''}}"
                                           href="{{url()->current()}}?user_type=provider_admin">
                                            {{translate('Provider')}}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="inbox_people">
                            <div class="d-flex gap-3 align-items-center mx-3 mb-3">
                                <div class="input-group search-form__input_group">
                                        <span class="search-form__icon">
                                            <span class="material-icons">search</span>
                                        </span>
                                    <input type="search" class="h-40 flex-grow-1 search-form__input" id="chat-search"
                                           placeholder="Search Here">
                                </div>

                                <div class="ripple-animation" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{{ translate('Search by name or phone number to start the conversation') }}" type="button">
                                    <img src="{{asset('/public/assets/admin-module/img/info.svg')}}" class="svg" alt="">
                                </div>
                            </div>

                            <div class="inbox_chat d-flex flex-column mt-1">
                                @foreach($chatList as $chat)
                                    @php($fromUser=$chat->channelUsers->where('user_id','!=',auth()->id())->first())
                                    <div class="chat_list chat-list-class {{$chat->is_read==0?'active':''}}"
                                         id="chat-{{$chat->id}}"
                                         data-route="{{route('admin.chat.ajax-conversation',['channel_id'=>$chat->id,'offset'=>1])}}"
                                         data-chat="{{$chat->id}}">
                                        <div class="chat_people media gap-10" id="chat_people">
                                            <div class="position-relative">
                                                <img
                                                    @if(isset($fromUser->user) && $fromUser->user->user_type == 'customer')
                                                        src="{{$fromUser->user->profile_image_full_path}}"
                                                    @elseif(isset($fromUser->user) && $fromUser->user->user_type == 'provider-admin')
                                                        src="{{$fromUser->user->provider->logo_full_path}}"
                                                    @elseif(isset($fromUser->user) && $fromUser->user->user_type == 'provider-serviceman')
                                                        src="{{$fromUser->user->profile_image_full_path}}"
                                                    @else
                                                        src="{{onErrorImage(
                                                                'null',
                                                                asset('storage/app/public/serviceman/profile').'/',
                                                                asset('public/assets/admin-module/img/media/user.png') ,
                                                                'serviceman/profile/')}}"
                                                    @endif
                                                    class="avatar rounded-circle" alt="{{ translate('image') }}">
                                                <span class="avatar-status bg-success"></span>
                                            </div>
                                            <div class="chat_ib media-body">
                                                <h5 class="">{{isset($fromUser->user) ? ($fromUser->user->provider ? $fromUser->user->provider->company_name : $fromUser->user->first_name . ' ' . $fromUser->user->last_name)  : translate('no_user_found')}}</h5>
                                                <span
                                                    class="fz-12">{{isset($fromUser->user) ? ($fromUser->user->provider ? $fromUser->user->provider->company_phone : $fromUser->user->phone) : ''}}</span>
                                            </div>
                                        </div>
                                        @if($chat->is_read==0)
                                            <div class="bg-info text-white radius-50 px-1 fz-12"
                                                 id="badge-{{$chat->id}}">
                                                <span class="material-symbols-outlined">swipe_up</span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-9 col-lg-8 mt-4 mt-lg-0">
                    <div class="card-header radius-10 mb-1 d-flex justify-content-end">
                        <button class="btn btn--primary" type="button" data-bs-toggle="modal"
                                data-bs-target="#modal-conversation-start">
                            <span class="material-icons">add</span>
                            {{translate('start_conversation')}}
                        </button>
                    </div>
                    <div class="card card-body card-chat justify-content-between" id="set-conversation">
                        <h4 class="d-flex align-items-center justify-content-center my-auto gap-2">
                            <span class="material-icons">chat</span>
                            {{translate('start_conversation')}}
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-conversation-start" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <label for="with-user" class="d-flex gap-2 fw-semibold">
                        <span class="material-icons">chat</span>
                        {{translate('with_user')}}
                    </label>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{route('admin.chat.create-channel')}}" method="post">
                    @csrf
                    <div class="modal-body p-30">
                        <div class="form-group mb-30">
                            <select class="form-control" name="user_type" id="user_type">
                                <option value="" selected disabled>{{translate('Select_User_Type')}}</option>
                                <option value="customer">{{translate('customer')}}</option>
                                <option value="provider-admin">{{translate('provider')}}</option>
                                <option value="provider-serviceman">{{translate('serviceman')}}</option>
                            </select>
                        </div>

                        <div class="form-group mb-30" id="customer">
                            <select class="form-control chat-js-select" name="customer_id">
                                <option value="" selected disabled>{{translate('Select_Customer')}}</option>
                                @foreach($customers as $item)
                                    <option value="{{$item->id}}">
                                        {{$item->first_name}} {{$item->last_name}} ({{$item->phone}})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-30 d--none" id="provider">
                            <select class="form-control chat-js-select" name="provider_id">
                                <option value="" selected disabled>{{translate('Select_Provider')}}</option>
                                @foreach($providers as $item)
                                    @if($item->provider)
                                        <option value="{{$item->id}}">
                                            {{$item->provider->company_name??''}} ({{$item->provider->company_phone}})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-30 d--none" id="serviceman">
                            <select class="form-control chat-js-select" name="serviceman_id">
                                <option value="" selected disabled>{{translate('Select_Serviceman')}}</option>
                                @foreach($servicemen as $item)
                                    <option value="{{$item->id}}">
                                        {{$item->first_name}} {{$item->last_name}} ({{$item->phone}})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--secondary" data-bs-dismiss="modal"
                                aria-label="Close">{{translate('close')}}</button>
                        <button type="submit" class="btn btn--primary">{{translate('start')}}</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

@endsection

@push('script')
    <script src="{{asset('public/assets/js/lightbox.min.js')}}"></script>
    <script>

        "use Strict";

        $('.chat-list-class').on('click', function () {
            let chatId = $(this).data('chat');
            let route = $(this).data('route');
            fetch_conversation(route, chatId)
        })

        function fetch_conversation(route, chat_id) {
            $.get({
                url: route,
                dataType: 'json',
                data: {},
                beforeSend: function () {
                },
                success: function (response) {
                    $('#set-conversation').empty().html(response.template);
                    document.getElementById('chat-' + chat_id).classList.remove("active");
                    document.getElementById('badge-' + chat_id).classList.add("hide-div");
                },
                error: function (jqXHR, exception) {
                    if (jqXHR.responseJSON && jqXHR.responseJSON.errors && jqXHR.responseJSON.errors.length > 0) {
                        var errorMessages = jqXHR.responseJSON.errors.map(function (error) {
                            return error.message;
                        });

                        errorMessages.forEach(function (errorMessage) {
                            toastr.error(errorMessage);
                        });
                    } else {
                        toastr.error("An error occurred.");
                    }
                },
                complete: function () {
                },
            });
        }

        $(document).ready(function () {
            $('.chat-js-select').select2({
                dropdownParent : $('#modal-conversation-start')
            });
        });

    </script>

    <script src="{{asset('public/assets/chatting-module/js/custom.js')}}"></script>

@endpush
