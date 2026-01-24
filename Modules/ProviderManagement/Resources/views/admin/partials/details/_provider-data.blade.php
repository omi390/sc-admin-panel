<div
    class="border-bottom d-flex align-items-center justify-content-between gap-2 py-3 px-4 mb-2">
    <h4 class="d-flex align-items-center gap-2">
        <span class="material-icons title-color">person</span>
        {{translate('Provider_Information')}}
    </h4>
    {{--                                        @if($booking->provider)--}}
    {{--                                            <span class="square-btn" data-bs-toggle="modal"--}}
    {{--                                                  data-bs-target="#providerModal">--}}
    {{--                                            <i class="material-icons fs-14" data-toggle="tooltip" data-placement="top"--}}
    {{--                                               title="{{translate('Update service address')}}">edit</i>--}}
    {{--                                        </span>--}}
    {{--                                        @endif--}}
    <div class="btn-group">
        <div class="cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="material-symbols-outlined">more_vert</span>
        </div>
        <ul class="dropdown-menu dropdown-menu__custom border-none dropdown-menu-end">
            <li>
                <div
                    class="d-flex align-items-center gap-2 cursor-pointer customer-chat">
                    <span class="material-symbols-outlined">chat</span>
                    {{translate('chat_with_Customer')}}
                    <form action="{{route('admin.chat.create-channel')}}"
                          method="post" id="chatForm-{{$booking?->customer?->id}}">
                        @csrf
                        <input type="hidden" name="customer_id"
                               value="{{$booking?->customer?->id}}">
                        <input type="hidden" name="type" value="booking">
                        <input type="hidden" name="user_type" value="customer">
                    </form>
                </div>
            </li>
            <li>
                <div class="d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined">manage_history</span>
                    {{translate('change_Provider')}}
                </div>
            </li>
            <li>
                <div class="d-flex align-items-center gap-2">
                    <span class="material-icons">person</span>
                    {{translate('View_Details')}}
                </div>
            </li>
        </ul>
    </div>
</div>
@if(isset($booking->provider))
    <div class="py-3 px-4">
        <div class="media gap-2 flex-wrap">
            <img width="58" height="58" class="rounded-circle border border-white"
                 src="{{asset('public/assets/admin-module/img/user.png')}}" alt="">
            <div class="meida-body">
                <h5 class="c1 mb-3">{{Str::limit($booking->provider->company_name??'', 30)}}</h5>
                <ul class="list-info">
                    <li>
                        <span class="material-icons">phone_iphone</span>
                        <a href="tel:88013756987564">{{$booking->provider->contact_person_phone??''}}</a>
                    </li>
                    <li>
                        <span class="material-icons">map</span>
                        <p>{{Str::limit($booking->provider->company_address??'', 100)}}</p>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@else
    <div class="d-flex flex-column gap-2 mt-30 align-items-center">
        <span class="material-icons text-muted fs-2">account_circle</span>
        <p class="text-muted text-center fw-medium">{{translate('No Serviceman Information')}}</p>
    </div>
@endif
