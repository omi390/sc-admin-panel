<?php
$booking = \Modules\BookingModule\Entities\Booking::where('provider_id', auth()->user()->provider->id)->get();
$maxBookingAmount = (business_config('max_booking_amount', 'booking_setup'))->live_values;
$subscribed_sub_category_ids = \Modules\ProviderManagement\Entities\SubscribedService::where(['provider_id' => auth()->user()->provider->id])->ofSubscription(1)->pluck('sub_category_id')->toArray();

$pending_booking_count = \Modules\BookingModule\Entities\Booking::providerPendingBookings(auth()->user()->provider, $maxBookingAmount)->count();
$accepted_booking_count = \Modules\BookingModule\Entities\Booking::providerAcceptedBookings(auth()->user()->provider->id, $maxBookingAmount)->count();

$logo = getBusinessSettingsImageFullPath(key: 'business_logo', settingType: 'business_information', path: 'business/',  defaultPath : 'public/assets/admin-module/img/placeholder.png');
?>

@php($provider = auth()->user()->provider)
<aside class="aside">
    <div class="aside-header">
        <a href="{{route('admin.dashboard')}}" class="logo d-flex gap-2">
            <img src="{{ $logo }}"
                 style="max-height: 50px" alt="{{translate('image')}}"
                 class="main-logo">
        </a>

        <button class="toggle-menu-button aside-toggle border-0 bg-transparent p-0 dark-color">
            <span class="material-icons">menu</span>
        </button>
    </div>

    <div class="aside-body" data-trigger="scrollbar">
        <div class="user-profile media gap-3 align-items-center my-3">
            <div class="avatar">
                <img class="avatar-img rounded-circle" src="{{$provider->logo_full_path}}"
                     alt="{{translate('provider logo')}}">
            </div>
            <div class="media-body ">
                <h5 class="card-title">{{ Str::limit($provider->company_email, 30) }}</h5>
                <span class="card-text">{{ Str::limit($provider->company_name, 30) }}</span>
            </div>
        </div>

        <ul class="nav">
            <li class="nav-category">{{translate('main')}}</li>
            <li>
                <a href="{{route('provider.dashboard')}}"
                   class="{{request()->is('provider/dashboard')?'active-menu':''}}">
                    <span class="material-icons" title="{{translate('dashboard')}}">dashboard</span>
                    <span class="link-title">{{translate('dashboard')}}</span>
                </a>
            </li>

            <!--<li class="nav-category" title="{{translate('booking_management')}}">-->
            <!--    {{translate('booking_management')}}-->
            <!--</li>-->
            <!--<li class="has-sub-item {{request()->is('provider/booking/*')?'sub-menu-opened':''}}">-->
            <!--    <a href="#" class="{{request()->is('provider/booking/*')?'active-menu':''}}">-->
            <!--        <span class="material-icons" title="{{translate('bookings')}}">shopping_cart</span>-->
            <!--        <span class="link-title">{{translate('bookings')}}</span>-->
            <!--    </a>-->

            <!--    <ul class="nav sub-menu">-->
            <!--        @php($bidding_status = (int)((business_config('bidding_status', 'bidding_system'))->live_values ?? 0))-->
            <!--        @if($bidding_status)-->
            <!--                <?php-->
            <!--                $ignored_posts = \Modules\BidModule\Entities\IgnoredPost::where('provider_id', auth()->user()->provider->id)->pluck('post_id')->toArray();-->
            <!--                $bidding_post_validity = (int)(business_config('bidding_post_validity', 'bidding_system'))->live_values;-->
            <!--                $posts = \Modules\BidModule\Entities\Post::where('is_booked', 0)-->
            <!--                    ->whereNotIn('id', $ignored_posts)-->
            <!--                    ->whereIn('sub_category_id', $subscribed_sub_category_ids)-->
            <!--                    ->where('zone_id', auth()->user()->provider->zone_id)-->
            <!--                    ->whereBetween('created_at', [Carbon\Carbon::now()->subDays($bidding_post_validity), Carbon\Carbon::now()])-->
            <!--                    ->when(!request()->user()?->provider?->service_availability || auth()->user()->provider->is_suspended && business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values, function ($query) {-->
            <!--                        $query->whereHas('bids', function ($query) {-->
            <!--                            $query->where('status', 'pending')->where('provider_id', auth()->user()->provider->id);-->
            <!--                        });-->
            <!--                    })-->
            <!--                    ->get();-->

            <!--                foreach ($posts as $key => $post) {-->
            <!--                    if ($post->bids) {-->
            <!--                        foreach ($post->bids as $bid) {-->
            <!--                            if ($bid->status == 'denied') unset($posts[$key]);-->
            <!--                        }-->
            <!--                    }-->
            <!--                }-->

            <!--                $posts = $posts->count();-->
            <!--                ?>-->
            <!--            <li>-->
            <!--                <a href="{{route('provider.booking.post.list', ['type'=>'all'])}}"-->
            <!--                   class="{{request()->is('provider/booking/post') || request()->is('provider/booking/post/details*') ? 'active-menu' : ''}}">-->
            <!--                    <span class="link-title">{{translate('Customized_Requests')}}-->
            <!--                        <span class="count">{{$posts??0}}</span>-->
            <!--                    </span>-->
            <!--                </a>-->
            <!--            </li>-->
            <!--        @endif-->
            <!--        <li>-->
            <!--            <a href="{{route('provider.booking.list', ['booking_status'=>'pending'])}}"-->
            <!--               class="{{request()->is('provider/booking/list') && request()->query('booking_status')=='pending'?'active-menu':''}}">-->
            <!--                <span class="link-title">{{translate('Booking_Requests')}}-->
            <!--                    <span class="count">{{\Illuminate\Support\Facades\Request::user()?->provider?->is_suspended == 0 || !business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values ? $pending_booking_count : 0}}</span>-->
            <!--                </span>-->
            <!--            </a>-->
            <!--        </li>-->
            <!--        <li>-->
            <!--            <a href="{{route('provider.booking.list', ['booking_status'=>'accepted'])}}"-->
            <!--               class="{{request()->is('provider/booking/list') && request()->query('booking_status')=='accepted'?'active-menu':''}}">-->
            <!--                <span class="link-title">{{translate('Accepted')}}-->
            <!--                    <span class="count">{{$accepted_booking_count}}</span>-->
            <!--                </span>-->
            <!--            </a>-->
            <!--        </li>-->
            <!--        <li>-->
            <!--            <a href="{{route('provider.booking.list', ['booking_status'=>'ongoing'])}}"-->
            <!--               class="{{request()->is('provider/booking/list') && request()->query('booking_status')=='ongoing'?'active-menu':''}}">-->
            <!--                <span class="link-title">{{translate('Ongoing')}}-->
            <!--                    <span class="count">{{$booking->where('booking_status', 'ongoing')->count()}}</span>-->
            <!--                </span>-->
            <!--            </a>-->
            <!--        </li>-->
            <!--        <li>-->
            <!--            <a href="{{route('provider.booking.list', ['booking_status'=>'completed'])}}"-->
            <!--               class="{{request()->is('provider/booking/list') && request()->query('booking_status')=='completed'?'active-menu':''}}">-->
            <!--                <span class="link-title">{{translate('Completed')}}-->
            <!--                    <span class="count">{{$booking->where('booking_status', 'completed')->count()}}</span>-->
            <!--                </span>-->
            <!--            </a>-->
            <!--        </li>-->
            <!--        <li>-->
            <!--            <a href="{{route('provider.booking.list', ['booking_status'=>'canceled'])}}"-->
            <!--               class="{{request()->is('provider/booking/list') && request()->query('booking_status')=='canceled'?'active-menu':''}}">-->
            <!--                <span class="link-title">{{translate('Canceled')}}-->
            <!--                    <span class="count">{{$booking->where('booking_status', 'canceled')->count()}}</span>-->
            <!--                </span>-->
            <!--            </a>-->
            <!--        </li>-->
            <!--    </ul>-->
            <!--</li>-->

            <!--<li class="nav-category">{{translate('Help & support')}}</li>-->
            <!--<li>-->
            <!--    <a href="{{route('provider.chat.index', ['user_type' => 'super_admin'])}}"-->
            <!--       class="{{request()->is('provider/chat/index*') ?'active-menu':''}}">-->
            <!--        <span class="material-icons" title="{{translate('chatting')}}">message</span>-->
            <!--        <span class="link-title">{{translate('Chatting')}}</span>-->
            <!--    </a>-->
            <!--</li>-->

            <!--<li class="nav-category"-->
            <!--    title="{{translate('Service_Management')}}">{{translate('Service_Management')}}</li>-->
            <!--<li>-->
            <!--    <a href="{{route('provider.service.available')}}"-->
            <!--       class="{{request()->is('provider/service/available*') || request()->is('provider/service/detail*')?'active-menu':''}}">-->
            <!--        <span class="material-icons" title="{{translate('available_services')}}">home_repair_service</span>-->
            <!--        <span class="link-title">{{translate('available_services')}}</span>-->
            <!--    </a>-->
            <!--</li>-->
            <!--<li>-->
            <!--    <a href="{{route('provider.sub_category.subscribed', ['status'=>'all'])}}"-->
            <!--       class="{{request()->is('provider/sub-category/subscribed*')?'active-menu':''}}">-->
            <!--        <span class="material-icons" title="{{translate('my_Subscriptions')}}">subscriptions</span>-->
            <!--        <span class="link-title">{{translate('my_subscriptions')}}</span>-->
            <!--    </a>-->
            <!--</li>-->

            <!--<li>-->
            <!--    <a href="{{route('provider.service.request-list')}}"-->
            <!--       class="{{request()->is('provider/service/request-list*') || request()->is('provider/service/make-request*')?'active-menu':''}}">-->
            <!--        <span class="material-icons" title="{{translate('Request for Service')}}">list</span>-->
            <!--        <span class="link-title">{{translate('Service Requests')}}</span>-->
            <!--    </a>-->
            <!--</li>-->

            <!--<li class="nav-category">{{translate('Promotion Management')}}</li>-->

            <!--<li class="has-sub-item {{request()->is('provider/advertisements/*')?'sub-menu-opened':''}}">-->
            <!--    <a href="#" class="{{request()->is('provider/advertisements/*')?'active-menu':''}}">-->
            <!--        <span class="material-icons" title="{{translate('advertisements')}}">campaign</span>-->
            <!--        <span class="link-title">{{translate('advertisements')}}</span>-->
            <!--    </a>-->
            <!--    <ul class="nav sub-menu">-->
            <!--        <li>-->
            <!--            <a href="{{route('provider.advertisements.ads-list', ['status' => 'all'])}}"-->
            <!--               class="{{request()->is('provider/advertisements/ads-list*')?'active-menu':''}}">-->
            <!--                {{translate('Ads List')}}-->
            <!--            </a>-->
            <!--        </li>-->
            <!--        <li>-->
            <!--            <a href="{{route('provider.advertisements.ads-create')}}"-->
            <!--               class="{{request()->is('provider/advertisements/ads-create')?'active-menu':''}}">-->
            <!--                {{translate('Create New Advertisement')}}-->
            <!--            </a>-->
            <!--        </li>-->
            <!--    </ul>-->
            <!--</li>-->

            <!--<li class="nav-category"-->
            <!--    title="{{translate('User_Management')}}">{{translate('User Management')}}</li>-->

            <!--<li class="has-sub-item {{request()->is('provider/serviceman/*')?'sub-menu-opened':''}}">-->
            <!--    <a href="#" class="{{request()->is('provider/serviceman/*')?'active-menu':''}}">-->
            <!--        <span class="material-icons" title="{{translate('Service_Man')}}">man</span>-->
            <!--        <span class="link-title">{{translate('Service_Man')}}</span>-->
            <!--    </a>-->
            <!--    <ul class="nav sub-menu">-->
            <!--        <li>-->
            <!--            <a href="{{route('provider.serviceman.list', ['status'=>'all'])}}"-->
            <!--               class="{{request()->is('provider/serviceman/list')?'active-menu':''}}">-->
            <!--                {{translate('Serviceman_List')}}-->
            <!--            </a>-->
            <!--        </li>-->
            <!--        <li>-->
            <!--            <a href="{{route('provider.serviceman.create')}}"-->
            <!--               class="{{request()->is('provider/serviceman/create')?'active-menu':''}}">-->
            <!--                {{translate('add_new_serviceman')}}-->
            <!--            </a>-->
            <!--        </li>-->
            <!--    </ul>-->
            <!--</li>-->

            <!--<li class="nav-category" title="{{translate('account')}}">{{translate('account_management')}}</li>-->
            <!--<li>-->
            <!--    <a href="{{route('provider.account_info', ['page_type'=>'overview'])}}"-->
            <!--       class="{{request()->is('provider/account-info*') || request()->is('provider/withdraw') ?'active-menu':''}}">-->
            <!--        <span class="material-icons" title="{{translate('Account_Information')}}">account_circle</span>-->
            <!--        <span class="link-title">{{translate('Account_Information')}}</span>-->
            <!--    </a>-->
            <!--</li>-->
            <!--<li>-->
            <!--    <a href="{{route('provider.bank_info')}}"-->
            <!--       class="{{request()->is('provider/bank-info*')?'active-menu':''}}">-->
            <!--        <span class="material-icons" title="{{translate('bank_information')}}">account_balance</span>-->
            <!--        <span class="link-title">{{translate('bank_information')}}</span>-->
            <!--    </a>-->
            <!--</li>-->

            <!--<li class="nav-category" title="{{translate('Reports & Analytics')}}">-->
            <!--    {{translate('Reports & Analytics')}}-->
            <!--</li>-->
            <!--<li class="has-sub-item {{request()->is('provider/report/*')?'sub-menu-opened':''}}">-->
            <!--    <a href="#" class="{{request()->is('provider/report/*')?'active-menu':''}}">-->
            <!--        <span class="material-icons" title="Customers">event_note</span>-->
            <!--        <span class="link-title">{{translate('Reports')}}</span>-->
            <!--    </a>-->
            <!--    <ul class="nav sub-menu">-->
            <!--        <li>-->
            <!--            <a href="{{route('provider.report.transaction', ['transaction_type'=>'all'])}}"-->
            <!--               class="{{request()->is('provider/report/transaction')?'active-menu':''}}">-->
            <!--                {{translate('Transaction Report')}}-->
            <!--            </a>-->
            <!--        </li>-->
            <!--        <li>-->
            <!--            <a href="{{route('provider.report.business.overview')}}"-->
            <!--               class="{{request()->is('provider/report/business*')?'active-menu':''}}">-->
            <!--                {{translate('Business Report')}}-->
            <!--            </a>-->
            <!--        </li>-->
            <!--        <li>-->
            <!--            <a href="{{route('provider.report.booking')}}"-->
            <!--               class="{{request()->is('provider/report/booking')?'active-menu':''}}">-->
            <!--                {{translate('Booking Report')}}-->
            <!--            </a>-->
            <!--        </li>-->
            <!--    </ul>-->
            <!--</li>-->

            <!--<li class="nav-category" title="{{translate('system_management')}}">{{translate('system_management')}}</li>-->
            <!--<li>-->
            <!--    <a href="{{route('provider.business-settings.get-business-information')}}"-->
            <!--       class="{{request()->is('provider/business-settings/get-business-information')?'active-menu':''}}">-->
            <!--        <span class="material-icons" title="Business Settings">business_center</span>-->
            <!--        <span class="link-title">{{translate('business_settings')}}</span>-->
            <!--    </a>-->
            <!--</li>-->
            <!--<li>-->
            <!--    <a href="{{route('provider.subscription-package.details')}}"-->
            <!--       class="{{request()->is('provider/subscription-package/*')?'active-menu':''}}">-->
            <!--        <span class="material-symbols-outlined">tune</span>-->
            <!--        <span class="link-title">{{translate('business_plan')}}</span>-->
            <!--    </a>-->
            <!--</li>-->
            <!--<li>-->
            <!--    <a href="{{route('provider.configuration.get-notification-setting', ['notification_type' => 'provider'])}}"-->
            <!--       class="{{request()->is('provider/configuration/get-notification-setting')?'active-menu':''}}">-->
            <!--        <span class="material-icons" title="Subscription Management">campaign</span>-->
            <!--        <span class="link-title">{{translate('Notification Channel')}}</span>-->
            <!--    </a>-->
            <!--</li>-->

            <!--<li>-->
            <!--    <div class="card" data-bg-img="{{asset('assets/provider-module')}}/img/media/create-ads-bg-vertical.png">-->
            <!--        <div class="card-body">-->
            <!--            <div class="d-flex flex-column gap-2 align-items-center text-center">-->
            <!--                <img width="140" class="mb-3" src="{{asset('assets/provider-module')}}/img/media/create-ads.png" alt="">-->
            <!--                <h4 class="mb-2">{{translate('Want To Get Highlighted')}}?</h4>-->
            <!--                <p>{{translate('Create ads to get highlighted on the app and web browser')}}</p>-->

            <!--               <a class="text-white text-white btn btn--primary" href="{{route('provider.advertisements.ads-create')}}">{{translate('Create Ads')}}</a>-->
            <!--            </div>-->
            <!--        </div>-->
            <!--    </div>-->
            <!--</li>-->
        </ul>
    </div>
</aside>
