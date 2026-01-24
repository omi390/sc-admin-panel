<?php
$booking = \Modules\BookingModule\Entities\Booking::get();
$max_booking_amount = (business_config('max_booking_amount', 'booking_setup'))->live_values ?? 0;
$pending_booking_count = \Modules\BookingModule\Entities\Booking::where('booking_status', 'pending')
    ->when($max_booking_amount > 0, function ($query) use ($max_booking_amount) {
        $query->where(function ($query) use ($max_booking_amount) {
            $query->where('payment_method', 'cash_after_service')
                ->where(function ($query) use ($max_booking_amount) {
                    $query->where('is_verified', 1)
                        ->orWhere('total_booking_amount', '<=', $max_booking_amount);
                })
                ->orWhere('payment_method', '<>', 'cash_after_service');
        });
    })
    ->count();

$offline_booking_count = \Modules\BookingModule\Entities\Booking::whereIn('booking_status', ['pending', 'accepted'])
    ->where('payment_method', 'offline_payment')->where('is_paid', 0)->count();

$accepted_booking_count = \Modules\BookingModule\Entities\Booking::where('booking_status', 'accepted')
    ->when($max_booking_amount > 0, function ($query) use ($max_booking_amount) {
        $query->where(function ($query) use ($max_booking_amount) {
            $query->where('payment_method', 'cash_after_service')
                ->where(function ($query) use ($max_booking_amount) {
                    $query->where('is_verified', 1)
                        ->orWhere('total_booking_amount', '<=', $max_booking_amount);
                })
                ->orWhere('payment_method', '<>', 'cash_after_service');
        });
    })
    ->count();

$arrived_booking_count = \Modules\BookingModule\Entities\Booking::where('booking_status', 'arrived')->count();
$all_booking_count = \Modules\BookingModule\Entities\Booking::count();

$pending_providers = \Modules\ProviderManagement\Entities\Provider::ofApproval(2)->count();
$denied_providers = \Modules\ProviderManagement\Entities\Provider::ofApproval(0)->count();
$logo = getBusinessSettingsImageFullPath(key: 'business_logo', settingType: 'business_information', path: 'business/', defaultPath: 'public/assets/placeholder.png');
?>

<aside class="aside">
    <div class="aside-header">
        <a href="{{route('admin.dashboard')}}" class="logo d-flex gap-2">
            <img class="main-logo onerror-image" src="{{ $logo }}" alt="{{ translate('image') }}">
        </a>

        <button class="toggle-menu-button aside-toggle border-0 bg-transparent p-0 dark-color">
            <span class="material-icons">menu</span>
        </button>
    </div>


    <div class="aside-body" data-trigger="scrollbar">
        <div class="user-profile media gap-3 align-items-center my-3">
            <div class="avatar">
                <img class="avatar-img rounded-circle" src="{{auth()->user()->profile_image_full_path }}" alt="{{ translate('profile_image') }}">
            </div>
            <div class="media-body ">
                <h5 class="card-title">{{\Illuminate\Support\Str::limit(auth()->user()->email,15)}}</h5>
                <span class="card-text">{{auth()->user()->user_type}}</span>
            </div>
        </div>

        <ul class="nav">
            <li class="nav-category">{{translate('main')}}</li>

            <li>
                <a href="{{route('admin.dashboard')}}" class="{{request()->is('admin/dashboard')?'active-menu':''}}">
                    <span class="material-icons" title="{{translate('dashboard')}}">dashboard</span>
                    <span class="link-title">{{translate('dashboard')}}</span>
                </a>
            </li>
            <li>
                <a href="{{route('admin.tendors')}}" class="{{request()->is('admin/tendors')?'active-menu':''}}">
                    <span class="material-icons" title="{{translate('dashboard')}}">dashboard</span>
                    <span class="link-title">Tendors</span>
                </a>
            </li>
            @can(['booking_view'])
                <li class="nav-category" title="{{translate('booking_management')}}">
                    {{translate('booking_management')}}
                </li>
                <li class="has-sub-item {{request()->is('admin/booking/*')?'sub-menu-opened':''}}">
                    <a href="#" class="{{request()->is('admin/booking/*')?'active-menu':''}}">
                        <span class="material-icons" title="Bookings">calendar_month</span>
                        <span class="link-title">{{translate('bookings')}}</span>
                    </a>
                    
                    <ul class="nav sub-menu">
                        <li>
                            <a href="{{route('admin.booking.post.list', ['type'=>'all'])}}"
                               class="{{request()->is('admin/booking/post') || request()->is('admin/booking/post/details*') ? 'active-menu' : ''}}">
                                <span class="link-title">{{translate('Customized_Requests')}}
                                    <span
                                        class="count">{{\Modules\BidModule\Entities\Post::where('is_booked', 0)->count()??0}}</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="{{route('admin.booking.list', ['booking_status'=>'all'])}}"
                               class="{{request()->is('admin/booking/list') && request()->query('booking_status')=='all'?'active-menu':''}}">
                                <span class="link-title">{{translate('All_Bookings')}} <span class="count">{{$all_booking_count}}</span></span>
                            </a>
                        </li>
                        <li>
                            <a href="{{route('admin.booking.list.verification', ['booking_status'=>'pending', 'type' => 'pending'])}}"
                               class="{{request()->is('admin/booking/list/verification') && request()->query('booking_status')=='pending' ?'active-menu':''}}"><span
                                    class="link-title">{{translate('verify_requests')}} <span
                                        class="count">{{\Modules\BookingModule\Entities\Booking::where('is_verified', '0')->where('payment_method', 'cash_after_service')->Where('total_booking_amount', '>', $max_booking_amount)->whereIn('booking_status', ['pending', 'accepted'])->count()}}</span></span></a>
                        </li>
                        <li><a href="{{route('admin.booking.list', ['booking_status'=>'pending'])}}"
                               class="{{request()->is('admin/booking/list') && request()->query('booking_status')=='pending'?'active-menu':''}}"><span
                                    class="link-title">{{translate('Booking_Requests')}} <span
                                        class="count">{{$pending_booking_count}}</span></span></a>
                        </li>

                        <li><a href="{{route('admin.booking.offline.payment')}}"
                               class="{{request()->is('admin/booking/list/offline-payment') && request()->query('booking_status')=='pending'?'active-menu':''}}"><span
                                    class="link-title">{{translate('Offline Payment')}} <span
                                        class="count">{{$offline_booking_count}}</span></span></a>
                        </li>

                        <li><a href="{{route('admin.booking.list', ['booking_status'=>'accepted'])}}"
                               class="{{request()->is('admin/booking/list') && request()->query('booking_status')=='accepted'?'active-menu':''}}"><span
                                    class="link-title">{{translate('Accepted')}} <span
                                        class="count">{{$accepted_booking_count}}</span></span></a>
                        </li>
                        <li><a href="{{route('admin.booking.list', ['booking_status'=>'arrived'])}}"
                               class="{{request()->is('admin/booking/list') && request()->query('booking_status')=='arrived'?'active-menu':''}}"><span
                                    class="link-title">{{translate('Arrived')}} <span class="count">{{$arrived_booking_count}}</span></span></a>
                        </li>
                        <li><a href="{{route('admin.booking.list', ['booking_status'=>'ongoing'])}}"
                               class="{{request()->is('admin/booking/list') && request()->query('booking_status')=='ongoing'?'active-menu':''}}"><span
                                    class="link-title">{{translate('Ongoing')}} <span
                                        class="count">{{$booking->where('booking_status', 'ongoing')->count()}}</span></span></a>
                        </li>
                        <li><a href="{{route('admin.booking.list', ['booking_status'=>'completed'])}}"
                               class="{{request()->is('admin/booking/list') && request()->query('booking_status')=='completed'?'active-menu':''}}"><span
                                    class="link-title">{{translate('Completed')}} <span
                                        class="count">{{$booking->where('booking_status', 'completed')->count()}}</span></span></a>
                        </li>
                        <li><a href="{{route('admin.booking.list', ['booking_status'=>'canceled'])}}"
                               class="{{request()->is('admin/booking/list') && request()->query('booking_status')=='canceled'?'active-menu':''}}"><span
                                    class="link-title">{{translate('Canceled')}} <span
                                        class="count">{{$booking->where('booking_status', 'canceled')->count()}}</span></span></a>
                        </li>
                    </ul>
                </li>
            @endcan

            @canany(['discount_view', 'discount_add', 'coupon_view', 'coupon_add', 'bonus_view', 'bonus_add', 'campaign_view', 'campaign_add','advertisement_view', 'advertisement_add', 'banner_add', 'push_notification_view','push_notification_add' ])
                <li class="nav-category" title="{{translate('promotion_management')}}">
                    {{translate('promotion_management')}}
                </li>
            @endcanany
            @canany(['discount_view', 'discount_add'])
                <li class="has-sub-item {{request()->is('admin/discount/*')?'sub-menu-opened':''}}">
                    <a href="#" class="{{request()->is('admin/discount/*')?'active-menu':''}}">
                        <span class="material-icons" title="{{translate('discounts')}}">redeem</span>
                        <span class="link-title">{{translate('discounts')}}</span>
                    </a>
                    <ul class="nav sub-menu">
                        <li>
                            <a href="{{route('admin.discount.list')}}"
                               class="{{request()->is('admin/discount/list')?'active-menu':''}}">
                                {{translate('discount_list')}}
                            </a>
                        </li>
                        @can('discount_add')
                            <li>
                                <a href="{{route('admin.discount.create')}}"
                                   class="{{request()->is('admin/discount/create')?'active-menu':''}}">
                                    {{translate('add_new_discount')}}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany
            @canany(['coupon_view', 'coupon_add'])
                <li class="has-sub-item {{request()->is('admin/coupon/*')?'sub-menu-opened':''}}">
                    <a href="#" class="{{request()->is('admin/coupon/*')?'active-menu':''}}">
                        <span class="material-icons" title="{{translate('coupons')}}">sell</span>
                        <span class="link-title">{{translate('coupons')}}</span>
                    </a>
                    <ul class="nav sub-menu">
                        @can('coupon_view')
                            <li>
                                <a href="{{route('admin.coupon.list')}}"
                                   class="{{request()->is('admin/coupon/list')?'active-menu':''}}">
                                    {{translate('coupon_list')}}
                                </a>
                            </li>
                        @endcan
                        @can('coupon_add')
                            <li>
                                <a href="{{route('admin.coupon.create')}}"
                                   class="{{request()->is('admin/coupon/create')?'active-menu':''}}">
                                    {{translate('add_new_coupon')}}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany
            @canany(['bonus_view', 'bonus_add'])
                <li class="has-sub-item {{request()->is('admin/bonus/*')?'sub-menu-opened':''}}">
                    <a href="#" class="{{request()->is('admin/bonus/*')?'active-menu':''}}">
                    <span class="material-icons matarial-symbols-outlined"
                          title="{{translate('bonus')}}">price_change</span>
                        <span class="link-title">{{translate('Wallet Bonus')}}</span>
                    </a>
                    <ul class="nav sub-menu">
                        @can('bonus_view')
                            <li>
                                <a href="{{route('admin.bonus.list')}}"
                                   class="{{request()->is('admin/bonus/list')?'active-menu':''}}">
                                    {{translate('bonus_list')}}
                                </a>
                            </li>
                        @endcan
                        @can('bonus_add')
                            <li>
                                <a href="{{route('admin.bonus.create')}}"
                                   class="{{request()->is('admin/bonus/create')?'active-menu':''}}">
                                    {{translate('add_new_bonus')}}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany
            @canany(['campaign_view', 'campaign_add'])
                <li class="has-sub-item {{request()->is('admin/campaign/*')?'sub-menu-opened':''}}">
                    <a href="#" class="{{request()->is('admin/campaign/*')?'active-menu':''}}">
                        <span class="material-icons" title="{{translate('campaigns')}}">campaign</span>
                        <span class="link-title">{{translate('campaigns')}}</span>
                    </a>
                    <ul class="nav sub-menu">
                        @can('campaign_view')
                            <li>
                                <a href="{{route('admin.campaign.list')}}"
                                   class="{{request()->is('admin/campaign/list')?'active-menu':''}}">
                                    {{translate('campaign_list')}}
                                </a>
                            </li>
                        @endcan
                        @can('campaign_add')
                            <li>
                                <a href="{{route('admin.campaign.create')}}"
                                   class="{{request()->is('admin/campaign/create')?'active-menu':''}}">
                                    {{translate('add_new_campaign')}}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany
            @canany(['advertisement_view', 'advertisement_add'])
                <li class="has-sub-item {{request()->is('admin/advertisements/*')?'sub-menu-opened':''}}">
                    <a href="#" class="{{request()->is('admin/advertisements/*')?'active-menu':''}}">
                        <span class="material-icons" title="{{translate('advertisements')}}">campaign</span>
                        <span class="link-title">{{translate('advertisements')}}</span>
                    </a>
                    <ul class="nav sub-menu">
                        @can('advertisement_view')
                            <li>
                                <a href="{{route('admin.advertisements.ads-list', ['status' => 'all'])}}"
                                   class="{{request()->is('admin/advertisements/ads-list')?'active-menu':''}}">
                                    {{translate('Ads List')}}
                                </a>
                            </li>
                        @endcan
                        @can('advertisement_add')
                            <li>
                                <a href="{{route('admin.advertisements.new-ads-request', ['status' => 'new'])}}"
                                   class="{{request()->is('admin/advertisements/new-ads-request')?'active-menu':''}}">
                                    {{translate('New Ads Request')}}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany
            @can('banner_add')
                <li>
                    <a href="{{route('admin.banner.create')}}"
                       class="{{request()->is('admin/banner/*')?'active-menu':''}}">
                        <span class="material-icons" title="{{translate('promotional_banners')}}">flag</span>
                        <span class="link-title">{{translate('promotional_banners')}}</span>
                    </a>
                </li>
            @endcan
            @can('push_notification_add')
                <li>
                    <a href="{{route('admin.push-notification.create')}}"
                       class="{{request()->is('admin/push-notification/*')?'active-menu':''}}">
                        <span class="material-icons" title="{{translate('push_notification')}}">notifications</span>
                        <span class="link-title">{{translate('Send Notifications')}}</span>
                    </a>
                </li>
            @endcan
            @canany(['provider_view', 'provider_add', 'provider_approve_or_deny','withdraw_view', 'withdraw_add'])
                <li class="nav-category"
                    title="{{translate('provider_management')}}">
                    {{translate('provider_management')}}
                </li>
            @endcan
            @canany(['provider_view', 'provider_approve_or_deny'])
                <li>
                    <a href="{{route('admin.provider.onboarding_request', ['status'=>'onboarding'])}}"
                       class="{{request()->is('admin/provider/onboarding*')?'active-menu':''}}">
                        <span class="material-icons" title="{{translate('Onboarding_Request')}}">description</span>
                        <span class="link-title">{{translate('Onboarding_Request')}} <span
                                class="count">{{$pending_providers + $denied_providers}}</span></span>
                    </a>
                </li>
            @endcanany
            @canany(['provider_view', 'provider_add'])
                <li class="has-sub-item  {{(request()->is('admin/provider/list') || request()->is('admin/provider/create') || request()->is('admin/provider/details*') || request()->is('admin/provider/edit*') || request()->is('admin/provider/collect-cash*'))?'sub-menu-opened':''}}">
                    <a href="#"
                       class="{{(request()->is('admin/provider/list') || request()->is('admin/provider/create') || request()->is('admin/provider/details*') || request()->is('admin/provider/edit*') || request()->is('admin/provider/collect-cash*'))?'active-menu':''}}">
                        <span class="material-icons" title="Providers">engineering</span>
                        <span class="link-title">{{translate('providers')}}</span>
                    </a>
                    <ul class="nav sub-menu">
                        @can('provider_view')
                            <li>
                                <a href="{{route('admin.provider.list', ['status'=>'all'])}}"
                                   class="{{(request()->is('admin/provider/list'))?'active-menu':''}}">{{translate('Provider_List')}}</a>
                            </li>
                        @endcan
                        @can('provider_add')
                            <li><a href="{{route('admin.provider.create')}}"
                                   class="{{(request()->is('admin/provider/create'))?'active-menu':''}}">{{translate('Add_New_Provider')}}</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcan
            @canany(['withdraw_view', 'withdraw_add'])
                <li class="has-sub-item  {{request()->is('admin/withdraw/method*')||request()->is('admin/withdraw/method/create')||request()->is('admin/withdraw/method/edit*') || request()->is('admin/withdraw/request*') ?'sub-menu-opened':''}}">
                    <a href="#"
                       class="{{request()->is('admin/withdraw/method*')||request()->is('admin/withdraw/method/create')||request()->is('admin/withdraw/method/edit*') || request()->is('admin/withdraw/request*') ?'active-menu':''}}">
                        <span class="material-icons" title="{{translate('withdraw_methods')}}">payments</span>
                        <span class="link-title">{{translate('Withdraws')}}</span>
                    </a>
                    <ul class="nav sub-menu">
                        @can('withdraw_view')
                            <li>
                                <a href="{{route('admin.withdraw.request.list', ['status'=>'all'])}}"
                                   class="{{request()->is('admin/withdraw/request*')?'active-menu':''}}">
                                    {{translate('Withdraw Requests')}}
                                </a>
                            </li>
                        @endcan
                        @can('withdraw_add')
                            <li>
                                <a href="{{route('admin.withdraw.method.list')}}"
                                   class="{{request()->is('admin/withdraw/method*')||request()->is('admin/withdraw/method/create')||request()->is('admin/withdraw/method/edit*')?'active-menu':''}}">
                                    {{translate('Withdraw method setup')}}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @canany(['service_view','service_add','zone_add','category_add'])
                <li class="nav-category" title="{{translate('service_management')}}">
                    {{translate('service_management')}}
                </li>
            @endcanany
            @can('zone_add')
                <li>
                    <a href="{{route('admin.zone.create')}}"
                       class="{{request()->is('admin/zone/*')?'active-menu':''}}">
                        <span class="material-icons" title="{{translate('service_zones')}}">map</span>
                        <span class="link-title">{{translate('Service Zones Setup')}}</span>
                    </a>
                </li>
            @endcan
            @can('category_add')
                <li class="has-sub-item {{(request()->is('admin/category/*') || request()->is('admin/sub-category/*'))?'sub-menu-opened':''}}">
                    <a href="#"
                       class="{{(request()->is('admin/category/*') || request()->is('admin/sub-category/*'))?'active-menu':''}}">
                        <span class="material-icons" title="Service Categories">category</span>
                        <span class="link-title">{{translate('Categories')}}</span>
                    </a>
                    <ul class="nav sub-menu">
                        <li>
                            <a href="{{route('admin.category.create')}}"
                               class="{{request()->is('admin/category/*')?'active-menu':''}}">
                                {{translate('Category Setup')}}
                            </a>
                        </li>
                        <li>
                            <a href="{{route('admin.sub-category.create')}}"
                               class="{{request()->is('admin/sub-category/*')?'active-menu':''}}">
                                {{translate('Sub Category Setup')}}
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan
            @canany(['service_view','service_add'])
                <li class="has-sub-item {{request()->is('admin/service/*')?'sub-menu-opened':''}}">
                    <a href="#" class="{{request()->is('admin/service/*')?'active-menu':''}}">
                        <span class="material-icons" title="Services">design_services</span>
                        <span class="link-title">{{translate('services')}}</span>
                    </a>
                    <ul class="nav flex-column sub-menu">
                        @can('service_view')
                            <li>
                                <a href="{{route('admin.service.index')}}"
                                   class="{{request()->is('admin/service/list')?'active-menu':''}}">
                                    {{translate('service_list')}}
                                </a>
                            </li>
                        @endcan
                        @can('service_add')
                            <li>
                                <a href="{{route('admin.service.create')}}"
                                   class="{{request()->is('admin/service/create')?'active-menu':''}}">
                                    {{translate('add_new_service')}}
                                </a>
                            </li>
                        @endcan
                        @can('service_view')
                            <li>
                                <a href="{{route('admin.service.request.list')}}"
                                   class="{{request()->is('admin/service/request/list*')?'active-menu':''}}">
                                    <span class="link-title">{{translate('New Service Requests')}}</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @canany(['wallet_add','wallet_view','customer_view','customer_add','point_view'])
                <li class="nav-category" title="{{translate('customer_management')}}">
                    {{translate('customer_management')}}
                </li>

                @canany(['customer_view','customer_add'])
                    <li class="has-sub-item {{request()->is('admin/customer/list')||request()->is('admin/customer/create') ?'sub-menu-opened':''}}">
                        <a href="#"
                           class="{{request()->is('admin/customer/list') || request()->is('admin/customer/detail*') || request()->is('admin/customer/edit/*') ||request()->is('admin/customer/create')?'active-menu':''}}">
                            <span class="material-icons" title="Customers">person_outline</span>
                            <span class="link-title">{{translate('customers')}}</span>
                        </a>
                        <ul class="nav sub-menu">
                            @can('customer_view')
                                <li>
                                    <a href="{{route('admin.customer.index')}}"
                                       class="{{request()->is('admin/customer/list')?'active-menu':''}}">
                                        {{translate('customer_list')}}
                                    </a>
                                </li>
                            @endcan
                            @can('customer_add')
                                <li>
                                    <a href="{{route('admin.customer.create')}}"
                                       class="{{request()->is('admin/customer/create')?'active-menu':''}}">
                                        {{translate('add_new_customer')}}
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany

                @canany(['wallet_add','wallet_view'])
                    <li class="has-sub-item {{request()->is('admin/customer/wallet*')?'sub-menu-opened':''}}">
                        <a href="#" class="{{request()->is('admin/customer/wallet*')?'active-menu':''}}">
                            <span class="material-icons" title="Customers">wallet</span>
                            <span class="link-title">{{translate('customer_wallet')}}</span>
                        </a>
                        <ul class="nav sub-menu">
                            @can('wallet_add')
                                <li>
                                    <a href="{{route('admin.customer.wallet.add-fund')}}"
                                       class="{{request()->is('admin/customer/wallet/add-fund')?'active-menu':''}}">
                                        {{translate('Add Fund to Wallet')}}
                                    </a>
                                </li>
                            @endcan
                            @can('wallet_view')
                                <li>
                                    <a href="{{route('admin.customer.wallet.report')}}"
                                       class="{{request()->is('admin/customer/wallet/report')?'active-menu':''}}">
                                        {{translate('Wallet Transactions')}}
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany

                @can('point_view')
                    <li class="has-sub-item {{request()->is('admin/customer/loyalty-point*')?'sub-menu-opened':''}}">
                        <a href="#" class="{{request()->is('admin/customer/loyalty-point*')?'active-menu':''}}">
                            <span class="material-icons" title="Customers">paid</span>
                            <span class="link-title">{{translate('loyalty_point')}}</span>
                        </a>
                        <ul class="nav sub-menu">
                            <li>
                                <a href="{{route('admin.customer.loyalty-point.report')}}"
                                   class="{{request()->is('admin/customer/loyalty-point/report')?'active-menu':''}}">
                                    {{translate('Loyalty Points Transactions')}}
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcan
            @endcanany

            @canany(['role_view', 'role_add', 'employee_add', 'employee_view'])
                <li class="nav-category"
                    title="{{translate('employee_management')}}">{{translate('employee_management')}}</li>
            @endcanany
            @canany(['role_view', 'role_add'])
                <li>
                    <a href="{{route('admin.role.index')}}" class="{{request()->is('admin/role/*')?'active-menu':''}}">
                        <span class="material-icons" title="Employee">settings</span>
                        <span class="link-title">{{translate('Employee Role Setup')}}</span>
                    </a>
                </li>
            @endcan
            @can('employee_view')
                <li>
                    <a href="{{route('admin.employee.index')}}"
                       class="{{request()->is('admin/employee/list') ||  request()->is('admin/employee/edit/*') ? 'active-menu':''}}">
                        <span class="material-icons" title="{{translate('employee_list')}}">list</span>
                        <span class="link-title">{{translate('employee_list')}}</span>
                    </a>
                </li>
            @endcan
            @can('employee_add')
                <li>
                    <a href="{{route('admin.employee.create')}}"
                       class="{{request()->is('admin/employee/create')?'active-menu':''}}">
                        <span class="material-icons" title="{{translate('add_new_employee')}}">add</span>
                        <span class="link-title">{{translate('add_new_employee')}}</span>
                    </a>
                </li>
            @endcan

            @can('transaction_view')
                <li class="nav-category" title="{{translate('transaction_management')}}">
                    {{translate('transaction_management')}}
                </li>
                <li>
                    <a href="{{route('admin.transaction.list', ['trx_type'=>'all'])}}"
                       class="{{request()->is('admin/transaction/list')?'active-menu':''}}">
                        <span class="material-icons" title="Customers">article</span>
                        <span class="link-title">{{translate('All Transactions')}}</span>
                    </a>
                </li>
            @endcan

            @canany(['report_view','analytics_view'])
                <li class="nav-category" title="{{translate('report_management')}}">
                    {{translate('Reports & Analytics')}}
                </li>
            @endcanany
            @can('report_view')
                <li class="has-sub-item {{request()->is('admin/report/*')?'sub-menu-opened':''}}">
                    <a href="#" class="{{request()->is('admin/report/*')?'active-menu':''}}">
                        <span class="material-icons" title="Customers">event_note</span>
                        <span class="link-title">{{translate('Reports')}}</span>
                    </a>
                    <ul class="nav sub-menu">
                        <li>
                            <a href="{{route('admin.report.transaction', ['transaction_type'=>'all'])}}"
                               class="{{request()->is('admin/report/transaction')?'active-menu':''}}">
                                {{translate('Transaction Reports')}}
                            </a>
                        </li>
                        <li>
                            <a href="{{route('admin.report.business.overview')}}"
                               class="{{request()->is('admin/report/business*')?'active-menu':''}}">
                                {{translate('Business Reports')}}
                            </a>
                        </li>
                        <li>
                            <a href="{{route('admin.report.booking')}}"
                               class="{{request()->is('admin/report/booking')?'active-menu':''}}">
                                {{translate('Booking Reports')}}
                            </a>
                        </li>
                        <li>
                            <a href="{{route('admin.report.provider')}}"
                               class="{{request()->is('admin/report/provider')?'active-menu':''}}">
                                {{translate('Provider Reports')}}
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan
            @can('analytics_view')
                <li class="has-sub-item {{request()->is('admin/analytics/*')?'sub-menu-opened':''}}">
                    <a href="#" class="{{request()->is('admin/analytics/*')?'active-menu':''}}">
                        <span class="material-icons" title="Customers">analytics</span>
                        <span class="link-title">{{translate('Analytics')}}</span>
                    </a>
                    <ul class="nav sub-menu">
                        <li>
                            <a href="{{route('admin.analytics.search.keyword')}}"
                               class="{{request()->is('admin/analytics/search/keyword')?'active-menu':''}}">
                                {{translate('Keyword_Search')}}
                            </a>
                        </li>
                        <li>
                            <a href="{{route('admin.analytics.search.customer')}}"
                               class="{{request()->is('admin/analytics/search/customer')?'active-menu':''}}">
                                {{translate('Customer_Search')}}
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan

            @canany(['addon_view', 'addon_add'])
                <li class="nav-category" title="{{translate('system_addon')}}">
                    {{translate('system_addon')}}
                </li>
                <li>
                    <a class="{{Request::is('admin/addon')?'active-menu':''}}"
                       href="{{route('admin.addon.index')}}" title="{{translate('system_addons')}}">
                        <span class="material-icons" title="add_circle_outline">add_circle_outline</span>
                        <span class="link-title">{{translate('system_addons')}}</span>
                        </span>
                    </a>
                </li>

                @if(count(config('addon_admin_routes'))>0)
                    <li class="has-sub-item {{request()->is('admin/payment/configuration/*') || request()->is('admin/sms/configuration/*')?'sub-menu-opened':''}}">
                        <a href="#"
                           class="{{request()->is('admin/payment/configuration/*') || request()->is('admin/sms/configuration/*')?'active-menu':''}}">
                            <span class="material-symbols-outlined">list</span>
                            <span class="link-title">{{translate('addon_menus')}}</span>
                        </a>
                        <ul class="nav flex-column sub-menu">
                            @foreach(config('addon_admin_routes') as $routes)
                                @foreach($routes as $route)
                                    <li>
                                        <a class="{{ Request::is($route['path']) ?'active-menu':'' }}"
                                           href="{{ $route['url'] }}" title="{{ translate($route['name']) }}">
                                            {{ translate($route['name']) }}
                                        </a>
                                    </li>
                                @endforeach
                            @endforeach
                        </ul>
                    </li>
                @endif
            @endcanany

            @canany(['business_view', 'landing_view','configuration_view','page_view', 'gallery_view', 'backup_view', 'subscription_settings_view', 'subscriber_view', 'subscription_package_view'])
                <li class="nav-category"
                    title="{{translate('system_management')}}">{{translate('system_management')}}</li>
            @endcan
            <li class="has-sub-item {{request()->is('admin/business-settings/*') || request()->is('admin/language/translate/*')?'sub-menu-opened':''}}">
                <a href="#"
                   class="{{request()->is('admin/business-settings/*') || request()->is('admin/language/translate/*')?'active-menu':''}}">
                    <span class="material-icons" title="Business Settings">business_center</span>
                    <span class="link-title">{{translate('Settings Management')}}</span>
                </a>
                <ul class="nav sub-menu">
                    @can('business_view')
                        <li>
                            <a href="{{route('admin.business-settings.get-business-information')}}"
                               class="{{request()->is('admin/business-settings/get-business-information')?'active-menu':''}}">
                                {{translate('business_settings')}}
                            </a>
                        </li>
                        <li>
                            <a href="{{route('admin.business-settings.login.setup')}}"
                               class="{{request()->is('admin/business-settings/login/setup') ?'active-menu':''}}">
                                {{translate('Login Setup')}}
                            </a>
                        </li>
                        <li>
                            <a href="{{route('admin.business-settings.notification-channel', ['notification_type' => 'user'])}}"
                               class="{{request()->is('admin/business-settings/notification-channel') ?'active-menu':''}}">
                                {{translate('Notification Channel')}}
                            </a>
                        </li>
                        <li>
                            <a href="{{route('admin.business-settings.seo.setting', ['page_type' => 'error_logs'])}}"
                               class="{{request()->is('admin/business-settings/seo-setting') ?'active-menu':''}}">
                                {{translate('404 Logs')}}
                            </a>
                        </li>
                        <li>
                            <a href="{{route('admin.business-settings.cron-job.list')}}"
                               class="{{request()->is('admin/business-settings/cron-job') ?'active-menu':''}}">
                                {{translate('Cron Job')}}
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
            <li class="has-sub-item {{request()->is('admin/subscription/*') || request()->is('admin/language/translate/*')?'sub-menu-opened':''}}">
                <a href="#"
                   class="{{request()->is('admin/subscription/*') || request()->is('admin/language/translate/*')?'active-menu':''}}">
                    <span class="material-icons" title="{{translate('Subscription Management')}}">campaign</span>
                    <span class="link-title">{{translate('Subscription Management')}}</span>
                </a>
                <ul class="nav sub-menu">
                    @can('subscription_package_view')
                        <li>
                            <a href="{{route('admin.subscription.package.list')}}"
                               class="{{request()->is('admin/subscription/package/*')?'active-menu':''}}">
                                {{translate('Subscription Package')}}
                            </a>
                        </li>
                    @endcan
                    @can('subscriber_view')
                    <li>
                        <a href="{{route('admin.subscription.subscriber.list')}}"
                           class="{{request()->is('admin/subscription/subscriber/*') ?'active-menu':''}}">
                            {{translate('Subscriber List')}}
                        </a>
                    </li>
                    @endcan
                    @can('subscription_settings_view')
                    <li>
                        <a href="{{route('admin.subscription.settings')}}"
                           class="{{request()->is('admin/subscription/settings') ?'active-menu':''}}">
                            {{translate('Settings')}}
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @can('landing_view')
                <li>
                    <a href="{{route('admin.business-settings.get-landing-information', ['web_page' => 'text_setup'])}}"
                       class="{{request()->is('admin/business-settings/get-landing-information')?'active-menu':''}}">
                        <span class="material-icons" title="Business Settings">rocket_launch</span>
                        <span class="link-title">{{translate('landing_page_settings')}}</span>
                    </a>
                </li>
            @endcan
            @can('configuration_view')
                <li class="has-sub-item {{request()->is('admin/configuration/*') || request()->is('admin/language/translate/*')?'sub-menu-opened':''}}">
                    <a href="#"
                       class="{{request()->is('admin/configuration/*') || request()->is('admin/language/translate/*')?'active-menu':''}}">
                        <span class="material-icons" title="Configurations">settings</span>
                        <span class="link-title">{{translate('configurations')}}</span>
                    </a>
                    <ul class="nav sub-menu">
                        <li>
                            <a href="{{route('admin.configuration.get-notification-setting', ['type' => 'customers'])}}"
                               class="{{request()->is('admin/configuration/get-notification-setting')?'active-menu':''}}">
                                {{translate('Push Notifications')}}
                            </a>
                        </li>
                        <li>
                            <a href="{{route('admin.configuration.get-third-party-config', ['web_page' => 'google_map'])}}"
                               class="{{request()->is('admin/configuration/get-third-party-config') || request()->is('admin/configuration/offline-payment/*') ?'active-menu':''}}">
                                {{translate('3rd_party')}}
                            </a>
                        </li>
                        <li>
                            <a href="{{route('admin.configuration.language_setup')}}"
                               class="{{request()->is('admin/configuration/language-setup') || request()->is('admin/language/translate/*') ?'active-menu':''}}">
                                {{translate('Language Setup')}}
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan
            @can('page_view')
                <li>
                    <a href="{{route('admin.business-settings.get-pages-setup')}}"
                       class="{{request()->is('admin/business-settings/get-pages-setup')?'active-menu':''}}">
                        <span class="material-icons" title="Page Settings">article</span>
                        <span class="link-title">{{translate('page_settings')}}</span>
                    </a>
                </li>
            @endcan
            @can('gallery_view')
                <li>
                    <a href="{{route('admin.business-settings.get-gallery-setup')}}"
                       class="{{request()->is('admin/business-settings/get-gallery-setup*')?'active-menu':''}}">
                        <span class="material-icons" title="Page Settings">collections_bookmark</span>
                        <span class="link-title">{{translate('Gallery')}}</span>
                    </a>
                </li>
            @endcan
            @can('backup_view')
                <li>
                    <a href="{{route('admin.business-settings.get-database-backup')}}"
                       class="{{request()->is('admin/business-settings/get-database-backup')?'active-menu':''}}">
                        <span class="material-icons" title="Page Settings">backup</span>
                        <span class="link-title">{{translate('Backup_Database')}}</span>
                    </a>
                </li>
            @endcan
        </ul>
    </div>
</aside>
