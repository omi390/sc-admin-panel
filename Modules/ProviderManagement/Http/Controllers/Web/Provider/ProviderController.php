<?php

namespace Modules\ProviderManagement\Http\Controllers\Web\Provider;

use App\CentralLogics\ProductLogic;
use App\Models\Item;
use App\Scopes\StoreScope;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\BidModule\Entities\IgnoredPost;
use Modules\BidModule\Entities\Post;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Entities\BookingDetailsAmount;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;
use Modules\BusinessSettingsModule\Entities\PackageSubscriber;
use Modules\CategoryManagement\Entities\Category;
use Modules\ChattingModule\Entities\ChannelList;
use Modules\PaymentModule\Entities\Bonus;
use Modules\PromotionManagement\Entities\Advertisement;
use Modules\PromotionManagement\Entities\Banner;
use Modules\PromotionManagement\Entities\Campaign;
use Modules\PromotionManagement\Entities\Coupon;
use Modules\PromotionManagement\Entities\Discount;
use Modules\PromotionManagement\Entities\PushNotification;
use Modules\ProviderManagement\Entities\BankDetail;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ProviderManagement\Entities\SubscribedService;
use Modules\ReviewModule\Entities\Review;
use Modules\ServiceManagement\Entities\Service;
use Modules\TransactionModule\Entities\Account;
use Modules\TransactionModule\Entities\Transaction;
use Modules\TransactionModule\Entities\WithdrawalMethod;
use Modules\UserManagement\Entities\Serviceman;
use Modules\UserManagement\Entities\User;
use Modules\ZoneManagement\Entities\Zone;
use Rap2hpoutre\FastExcel\FastExcel;
use Carbon\Carbon;

class ProviderController extends Controller
{
    private $provider, $account, $user, $push_notification, $serviceman;
    private $subscribedService;
    private Booking $booking;
    private Zone $zone;
    private Review $review;
    private Transaction $transaction;
    private ChannelList $channelList;
    protected WithdrawalMethod $withdrawal_method;

    private SubscribedService $subscribed_service;
    private BankDetail $bank_detail;
    protected BusinessSettings $business_settings;
    protected BookingDetailsAmount $booking_details_amount;
    protected SubscribedService $subscribedSubCategories;
    protected IgnoredPost $ignoredPost;
    protected Post $post;

    protected $messaging;

    protected $google_map;

    public function __construct(ChannelList $channelList, Transaction $transaction, SubscribedService $subscribedService, BankDetail $bankDetail, Provider $provider, Account $account, WithdrawalMethod $withdrawal_method, User $user, PushNotification $pushNotification, Serviceman $serviceman, Booking $booking, Zone $zone, Review $review, Service $service, SubscribedService $subscribed_service, BankDetail $bank_detail, BusinessSettings $business_settings, BookingDetailsAmount $booking_details_amount, IgnoredPost $ignoredPost, Post $post)
    {
        $this->bank_detail = $bankDetail;
        $this->provider = $provider;
        $this->user = $user;
        $this->account = $account;
        $this->withdrawal_method = $withdrawal_method;
        $this->push_notification = $pushNotification;
        $this->serviceman = $serviceman;
        $this->subscribedService = $subscribedService;
        $this->google_map = business_config('google_map', 'third_party');
        $this->booking = $booking;
        $this->zone = $zone;
        $this->review = $review;
        $this->transaction = $transaction;
        $this->channelList = $channelList;
        $this->subscribed_sub_categories = $subscribedService;
        $this->ignoredPost = $ignoredPost;
        $this->post = $post;

        $this->subscribed_service = $subscribed_service;
        $this->bank_detail = $bank_detail;
        $this->business_settings = $business_settings;
        $this->booking_details_amount = $booking_details_amount;

        $this->messaging = app('firebase.messaging');
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function getUpdatedData(Request $request): JsonResponse
    {
        $subscribed = $this->subscribed_sub_categories->where(['provider_id' => $request->user()->provider->id])
            ->where(['is_subscribed' => 1])
            ->pluck('sub_category_id')->toArray();

        $booking = $this->booking
            ->whereIn('sub_category_id', $subscribed)
            ->where('zone_id', $request->user()->provider->zone_id)
            ->where('is_checked', 0)->count();
        $notificationCount = $this->push_notification->whereJsonContains('zone_ids', $request->user()->provider->zone_id)->whereJsonContains('to_users', 'provider-admin')->count();
        $notifications = $this->push_notification->whereJsonContains('zone_ids', $request->user()->provider->zone_id)->whereJsonContains('to_users', 'provider-admin')->latest()->take(50)->get();
        $message = $this->channelList->wherehas('channelUsers', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id)->where('is_read', 0);
        })->count();


        //bidding_service_request
        $ignoredPosts = IgnoredPost::where('provider_id', auth()->user()->provider->id)->pluck('post_id')->toArray();
        $biddingPostValidity = (int)(business_config('bidding_post_validity', 'bidding_system'))->live_values;

        $unchecked_posts = Post::whereNotIn('id', $ignoredPosts)
            ->whereIn('sub_category_id', $subscribed)
            ->where('zone_id', $request->user()->provider->zone_id)
            ->where('is_checked', 0)
            ->whereBetween('created_at', [Carbon::now()->subDays($biddingPostValidity), Carbon::now()])
            ->latest()
            ->get();

        $post = $unchecked_posts->first();

        //find distance
        $coordinates = auth()->user()->provider->coordinates ?? null;
        $distance = null;
        if (!is_null($coordinates) && isset($post) && $post->service_address) {
            $distance = get_distance(
                [$coordinates['latitude'] ?? null, $coordinates['longitude'] ?? null],
                [$post->service_address?->lat, $post->service_address?->lon]
            );
            $distance = ($distance) ? number_format($distance, 2) . ' km' : null;
        }

        return response()->json([
            'status' => 1,
            'data' => [
                'booking' => $booking,
                'notification_count' => $notificationCount,
                'notification_template' => view('providermanagement::provider.partials._notifications', compact('notifications'))->render(),
                'message' => $message,
                'unchecked_posts' => $unchecked_posts->count(),
                'post_content' => $post ? view('providermanagement::provider.partials._bidding_service_request', compact('post', 'distance'))->render() : null
            ]
        ]);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @param Transaction $transaction
     * @param SubscribedService $subscribedService
     * @param Serviceman $serviceman
     * @return Renderable
     */
    public function dashboard(Request $request, Transaction $transaction, SubscribedService $subscribedService, Serviceman $serviceman): Renderable
    {
        $notification = $this->push_notification->whereJsonContains('zone_ids', $request->user()->provider->zone_id)->get()->count();
        session()->put('notification_count', $notification);

        $data = [];

        $maxBookingAmount = (business_config('max_booking_amount', 'booking_setup'))->live_values;

        //top_cards
        $account = $this->account->where('user_id', $request->user()->id)->first();
        $data[] = ['top_cards' => [
            'total_earning' => $account['received_balance'] + $account['total_withdrawn'],
            'total_subscribed_services' => $this->subscribedService->where('provider_id', $request->user()->provider->id)
                ->with(['sub_category'])
                ->whereHas('category', function ($query) {
                    $query->where('is_active', 1);
                })->whereHas('sub_category', function ($query) {
                    $query->where('is_active', 1);
                })
                ->ofStatus(1)
                ->count(),
            'total_service_man' => $this->serviceman->where(['provider_id' => $request->user()->provider->id])->count(),
            'total_booking_served' => $request->user()->provider->bookings('completed')->count()
        ]];

        //provider total earning
        $totalEarning = $this->booking_details_amount
            ->whereHas('booking', function ($query) use ($request) {
                $query->where('provider_id', $request->user()->provider->id)
                    ->ofBookingStatus('completed');
            })
            ->get()->sum('provider_earning');

        $data[] = ['provider_total_earning' => $totalEarning];


        //booking_stats
        $bookingOverview = DB::table('bookings')->where('provider_id', $request->user()->provider->id)
            ->select('booking_status', DB::raw('count(*) as total'))
            ->groupBy('booking_status')
            ->get();
        $totalBookings = $this->booking->where('provider_id', $request->user()->provider->id)->count();
        $data[] = ['booking_stats' => $bookingOverview, 'total_bookings' => $totalBookings];


        //recent_bookings
        $subCategoryIds = $this->subscribed_service->where('provider_id', $request->user()->provider->id)->ofSubscription(1)->pluck('sub_category_id')->toArray();
        $recent_bookings = $this->booking->with(['detail.service' => function ($query) {
            $query->select('id', 'name', 'thumbnail');
        }])
            ->whereIn('sub_category_id', $subCategoryIds)
            ->when($maxBookingAmount > 0, function ($query) use ($maxBookingAmount, $request) {
                if (!$request->user()?->provider?->is_suspended || !business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values) {
                    $query->where(function ($query) use ($maxBookingAmount) {
                        $query->where('payment_method', 'cash_after_service')
                            ->where(function ($query) use ($maxBookingAmount) {
                                $query->where('is_verified', 1)
                                    ->orWhere('total_booking_amount', '<=', $maxBookingAmount);
                            })
                            ->orWhere('payment_method', '<>', 'cash_after_service');
                    });
                } else {
                    $query->whereNull('id');
                }
            })
            ->where('booking_status', 'pending')
            ->where('zone_id', $request->user()->provider->zone_id)
            ->latest()
            ->take(5)
            ->get();
        $data[] = ['recent_bookings' => $recent_bookings];

        //my_subscriptions
        $subscriptions = $subscribedService
            ->with(['sub_category'])
            ->withCount(['services', 'completed_booking'])
            ->where(['provider_id' => $request->user()->provider->id])
            ->whereHas('category', function ($query) {
                $query->where('is_active', 1);
            })->whereHas('sub_category', function ($query) {
                $query->where('is_active', 1);
            })
            ->ofStatus(1)
            ->take(5)->get();

        $data[] = ['subscriptions' => $subscriptions];


        //serviceman_list
        $servicemanList = $this->serviceman->whereHas('user', function ($q) {
            $q->ofStatus(1);
        })->with(['user'])
            ->where(['provider_id' => $request->user()->provider->id])
            ->latest()
            ->take(5)->get();

        $data[] = ['serviceman_list' => $servicemanList];

        //recent transactions
        $recentTransactions = $this->transaction->where(['to_user_id' => $request->user()->id])->where('credit', '>', 0)
            ->with(['booking'])
            ->latest()
            ->take(5)
            ->get();
        $data[] = [
            'recent_transactions' => $recentTransactions,
            'this_month_trx_count' => $transaction->where(['to_user_id' => $request->user()->id])->where('credit', '>', 0)->whereMonth('created_at', date('m'))->count()
        ];

        //customize booking

        $subCategories = $this->subscribedService
            ->where(['provider_id' => $request->user()->provider->id])
            ->where(['is_subscribed' => 1])->pluck('sub_category_id')->toArray();

        $ignoredPosts = $this->ignoredPost->where('provider_id', $request->user()->provider->id)->pluck('post_id')->toArray();
        $biddingPostValidity = (int)(business_config('bidding_post_validity', 'bidding_system'))->live_values;
        $posts = $this->post
            ->with(['addition_instructions', 'service', 'category', 'sub_category', 'booking', 'customer'])
            ->where('is_booked', 0)
            ->whereNotIn('id', $ignoredPosts)
            ->whereIn('sub_category_id', $subCategories)
            ->where('zone_id', $request->user()->provider->zone_id)
            ->whereBetween('created_at', [Carbon::now()->subDays($biddingPostValidity), Carbon::now()])
            ->when(true, function ($query) use ($request) {
                if($request->user()?->provider?->service_availability && (!$request->user()?->provider?->is_suspended || !business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values)){
                    $query->whereDoesntHave('bids', function ($query) use ($request) {
                        $query->where('provider_id', $request->user()->provider->id);
                    });
                }else{
                    $query->whereNull('id');
                }
            })
            ->latest()
            ->take(5)->get();

        $data[] = ['customized_bookings' => $posts];


        // Data for chart
        $year = session()->has('dashboard_earning_graph_year') ? session('dashboard_earning_graph_year') : date('Y');
        $amounts = $this->booking_details_amount
            ->whereHas('booking', function ($query) use ($request, $year) {
                $query->where('provider_id', $request->user()->provider->id)
                    ->whereYear('created_at', '=', $year)
                    ->ofBookingStatus('completed');
            })
            ->select(
                DB::raw('sum(provider_earning) as provider_earning'),

                DB::raw('MONTH(created_at) month')
            )
            ->groupby('month')->get()->toArray();

        $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        foreach ($months as $month) {
            $found = 0;
            foreach ($amounts as $key => $item) {
                if ($item['month'] == $month) {
                    $chart_data['total_earning'][] = with_decimal_point($item['provider_earning']);
                    $found = 1;
                }
            }
            if (!$found) {
                $chart_data['total_earning'][] = with_decimal_point(0);
            }
        }
        //chart data end

        $postCount = $this->post
            ->where('is_booked', 0)
            ->whereNotIn('id', $ignoredPosts)
            ->whereIn('sub_category_id', $subCategories)
            ->where('zone_id', $request->user()->provider->zone_id)
            ->whereBetween('created_at', [Carbon::now()->subDays($biddingPostValidity), Carbon::now()])
            ->when(true, function ($query) use ($request) {
                if($request->user()?->provider?->service_availability && (!$request->user()?->provider?->is_suspended || !business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values)){
                    $query->whereDoesntHave('bids', function ($query) use ($request) {
                        $query->where('provider_id', $request->user()->provider->id);
                    });
                }else{
                    $query->whereNull('id');
                }
            })
            ->latest()->count();

        $pendingBookingCount = $this->booking->where('booking_status', 'pending')
            ->whereIn('sub_category_id', $subCategories)
            ->when($maxBookingAmount > 0, function ($query) use ($maxBookingAmount) {
                $query->where(function ($query) use ($maxBookingAmount) {
                    $query->where('payment_method', 'cash_after_service')
                        ->where(function ($query) use ($maxBookingAmount) {
                            $query->where('is_verified', 1)
                                ->orWhere('total_booking_amount', '<=', $maxBookingAmount);
                        })
                        ->orWhere('payment_method', '<>', 'cash_after_service');
                });
            })
            ->where('zone_id', $request->user()->provider->zone_id)
            ->count();

        $booking_counts = [
            'normal_booking_count' => $pendingBookingCount,
            'post_count' => $postCount,
        ];

        return view('providermanagement::dashboard', compact('data', 'chart_data', 'booking_counts'));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function updateDashboardEarningGraph(Request $request): JsonResponse
    {
        $year = $request['year'];
        $amounts = $this->booking_details_amount
            ->whereHas('booking', function ($query) use ($request, $year) {
                $query->where('provider_id', $request->user()->provider->id)
                    ->whereYear('created_at', '=', $year)
                    ->ofBookingStatus('completed');
            })
            ->select(
                DB::raw('sum(provider_earning) as provider_earning'),

                DB::raw('MONTH(created_at) month')
            )
            ->groupby('month')->get()->toArray();

        $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        foreach ($months as $month) {
            $found = 0;
            foreach ($amounts as $key => $item) {
                if ($item['month'] == $month) {
                    $chart_data['total_earning'][] = with_decimal_point($item['provider_earning']);
                    $found = 1;
                }
            }
            if (!$found) {
                $chart_data['total_earning'][] = with_decimal_point(0);
            }
        }
        //chart data end

        session()->put('dashboard_earning_graph_year', $request['year']);

        return response()->json($chart_data);
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function subscribedSubCategories(Request $request): Renderable
    {
        $keys = explode(' ', $request['search']);
        $status = $request['status'];
        $search = $request['search'];
        $queryParam = ['status' => $request['status'], 'search' => $request['search']];

        $subscribedSubCategories = $this->subscribedService->where('provider_id', $request->user()->provider->id)
            ->with(['category', 'sub_category' => function ($query) {
                return $query->withCount(['services' => function ($query) {
                    $query->ofStatus(1);
                }])->with(['services']);
            }])->whereHas('category', function ($query) {
                $query->where('is_active', 1);
            })->whereHas('sub_category', function ($query) {
                $query->where('is_active', 1);
            })
            ->when($request->has('status') && $request['status'] != 'all', function ($query) use ($request) {
                $query->where('is_subscribed', ($request['status'] == 'subscribed' ? 1 : 0));
            })
            ->where(function ($query) use ($keys) {
                foreach ($keys as $key) {
                    $query->orWhereHas('sub_category', function ($query) use ($key) {
                        $query->where('name', 'LIKE', '%' . $key . '%');
                    });
                }
            })
            ->paginate(pagination_limit())->appends($queryParam);

        return view('providermanagement::subscribedSubCategory', compact('subscribedSubCategories', 'status', 'search'));
    }


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function statusUpdate(Request $request, $id): JsonResponse
    {
        $service = $this->subscribedService->where('id', $id)->first();
        $this->subscribedService->where('id', $id)->update(['is_subscribed' => !$service->is_subscribed]);

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @return View|Factory|RedirectResponse|Application
     */
    public function accountInfo(Request $request): View|Factory|RedirectResponse|Application
    {
        $pageType = $request['page_type'] ?? 'overview';

        if ($pageType == 'overview') {
            $pageType = $request['page_type'];

            //payment gateways
            $isPublished = 0;
            try {
                $fullData = include('Modules/Gateways/Addon/info.php');
                $isPublished = $fullData['is_published'] == 1 ? 1 : 0;
            } catch (\Exception $exception) {
            }

            $paymentGateways = collect($this->getPaymentMethods())
                ->filter(function ($query) use ($isPublished) {
                    if (!$isPublished) {
                        return in_array($query['gateway'], array_column(PAYMENT_METHODS, 'key'));
                    } else return $query;
                })->map(function ($query) {
                    $query['label'] = ucwords(str_replace('_', ' ', $query['gateway']));
                    return $query;
                })->values();
            $provider = $this->provider->with('owner.account')->withCount(['bookings'])->where('user_id', $request->user()->id)->first();
            $bookingOverview = DB::table('bookings')->where('provider_id', $request->user()->provider->id)
                ->select('booking_status', DB::raw('count(*) as total'))
                ->groupBy('booking_status')
                ->get();

            $status = ['accepted', 'ongoing', 'completed', 'canceled'];
            $total = [];
            foreach ($status as $item) {
                if ($bookingOverview->where('booking_status', $item)->first() !== null) {
                    $total[] = $bookingOverview->where('booking_status', $item)->first()->total;
                } else {
                    $total[] = 0;
                }
            }

            //total earning
            $account = $this->account->where('user_id', $request->user()->id)->first();
            $totalEarning = $account['received_balance'] + $account['total_withdrawn'];

            //adjust &
            $withdrawRequestAmount = [
                'minimum' => (float)(business_config('minimum_withdraw_amount', 'business_information'))->live_values ?? null,
                'maximum' => (float)(business_config('maximum_withdraw_amount', 'business_information'))->live_values ?? null,
            ];

            //random value generate
            $min = $withdrawRequestAmount['minimum']; // Set the minimum value
            $max = $withdrawRequestAmount['maximum']; // Set the maximum value

            // Generate random numbers
            $mid = round(($min + $max) / 2 / 10) * 10; // Middle of min and max
            $mid1 = round(($min + $mid) / 2 / 10) * 10; // Middle of min and mid
            $mid2 = round(($mid + $max) / 2 / 10) * 10; // Middle of mid and max
            $num4 = ceil($max / 10) * 10; // Maximum value

            if ($min == 0 && $max == 0) {
                $num5 = 0;
            } else {
                do {
                    $num5 = floor(rand($min, $max) / 10) * 10; // Random value between min and max
                } while (in_array($num5, array($mid, $mid1, $mid2, $num4)));
            }

            // Store generated numbers in an array
            $withdrawRequestAmount['random'] = array($mid, $mid1, $num5, $mid2, $num4);
            //end

            $account = $this->account->where('user_id', $request->user()->id)->first();
            $receivable = $account->account_receivable;
            $payable = $account->account_payable;

            if ($receivable > $payable) {
                $collectable_cash = $receivable - $payable ?? 0;
            } elseif ($payable > $receivable) {
                $collectable_cash = $payable - $receivable ?? 0;
            } else {
                $collectable_cash = 0;
            }

            $withdrawalMethods = $this->withdrawal_method->ofStatus(1)->get();

            return view('providermanagement::provider.account.overview', compact('pageType', 'provider', 'total', 'totalEarning', 'paymentGateways', 'collectable_cash', 'withdrawalMethods', 'withdrawRequestAmount'));

        } elseif ($pageType == 'commission-info') {

            $provider = $this->provider->where('user_id', $request->user()->id)->first();
            $commission = $provider->commission_status == 1 ? $provider->commission_percentage : (business_config('default_commission', 'business_information'))->live_values;
            return view('providermanagement::provider.account.commission', compact('pageType', 'provider', 'commission'));

        } elseif ($pageType == 'review') {
            $providerId = $request->user()->provider->id;
            $packageSubscriber = PackageSubscriber::where('provider_id', $providerId)->with('feature')->first();
            $search = $request->has('search') ? $request['search'] : '';
            $queryParam = ['search' => $search, 'page_type' => $request['page_type']];

            if ($packageSubscriber ){
                if (!in_array('review', $packageSubscriber?->feature?->pluck('feature')->toArray())) {
                    Toastr::error(translate('your_package_does_not_include_this_section'));
                    return redirect()->route('provider.dashboard');
                }
            }

            $provider = $this->provider->with('reviews')->where('user_id', $request->user()->id)->first();
            $reviews = $this->booking->with(['reviews.service'])
                ->when($request->has('search'), function ($query) use ($request) {
                    $keys = explode(' ', $request['search']);
                    $query->whereHas('reviews', function ($query) use ($keys) {
                        foreach ($keys as $key) {
                            $query->where('review_comment', 'LIKE', '%' . $key . '%')
                                ->orWhere('readable_id', 'LIKE', '%' . $key . '%');
                        }
                    });
                })
                ->whereHas('reviews', function ($query) use ($providerId) {
                    $query->where('provider_id', $providerId)->where('is_active', 1);
                })
                ->latest()
                ->paginate(pagination_limit())
                ->appends($queryParam);

            return view('providermanagement::provider.account.review', compact('pageType', 'reviews', 'search', 'provider'));

        } elseif ($pageType == 'promotional_cost') {
            $promotionalCostPercentage = $this->business_settings->where('settings_type', 'promotional_setup')->get();
            return view('providermanagement::provider.account.promotional-cost', compact('pageType', 'promotionalCostPercentage'));
        }

        Toastr::error(translate('no_data_found'));
        return back();
    }

    public function adjust(Request $request): RedirectResponse
    {
        $provider = Provider::where('user_id', $request->user()->id)->first();
        $account = $this->account->where('user_id', $request->user()->id)->first();
        $receivable = $account->account_receivable;
        $payable = $account->account_payable;

        if ($receivable == $payable) {

            withdrawRequestAcceptForAdjustTransaction($request->user()->id, $receivable);
            collectCashTransaction($provider->id, $payable);

            Toastr::success(translate('account_amount_adjusted_successfully'));
            return redirect()->route('provider.account_info', ['page_type' => 'overview']);
        }

        Toastr::error(translate(DEFAULT_204['message']));
        return back();
    }


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function bankInfo(Request $request): Renderable
    {
        $provider = $this->provider->with('bank_detail')->where('user_id', $request->user()->id)->first();
        return view('providermanagement::bank-info', compact('provider'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateBankInfo(Request $request)
    {
        Validator::make($request->all(), [
            'bank_name' => 'required',
            'branch_name' => 'required',
            'acc_no' => 'required',
            'acc_holder_name' => 'required',
            'routing_number' => 'required',
        ]);

        $this->bank_detail::updateOrCreate(
            ['provider_id' => $request->user()->provider->id],
            [
                'bank_name' => $request->bank_name,
                'branch_name' => $request->branch_name,
                'acc_no' => $request->acc_no,
                'acc_holder_name' => $request->acc_holder_name,
                'routing_number' => $request->routing_number,
            ]
        );

        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function availableServices(Request $request): Renderable
    {
        return view('providermanagement::available-services');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function profileInfo(Request $request): Renderable
    {
        $provider = $this->provider->with(['owner.addresses', 'zone'])->where('user_id', $request->user()->id)->first();
        $zones = $this->zone->ofStatus(1)->select('id', 'name')->get();
        $maxBookingAmount = business_config('max_booking_amount', 'booking_setup')->live_values;

        $ongoingBookings = $this->booking
            ->where('booking_status', 'ongoing')
            ->where('provider_id', $request->user()->provider->id)
            ->count();

        $acceptedBookings = $this->booking
            ->where('provider_id', $request->user()->provider->id)
            ->providerAcceptedBookings($request->user()->provider->id, $maxBookingAmount)
            ->count();

        $account = $this->account->where('user_id', $request->user()->id)->first();

        return view('providermanagement::profile-update', compact('provider', 'zones', 'acceptedBookings', 'ongoingBookings', 'account'));
    }

    /**
     * Modify provider information
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        Validator::make($request->all(), [
            'contact_person_name' => 'required',
            'contact_person_phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
            'contact_person_email' => 'required',
            'zone_id' => 'required',

            'password' => isset($request->password) ? 'string|min:8' : '',
            'confirm_password' => isset($request->password) ? 'required|same:password' : '',

            'company_name' => 'required',
            'company_email' => 'required|email',
            'company_phone' => 'required',
            'company_address' => 'required',
            'logo' => 'image|mimes:jpeg,jpg,png,gif|max:10000',

            'latitude' => 'required',
            'longitude' => 'required',
        ])->validate();

        $provider = $this->provider::where('user_id', $request->user()->id)->first();
        $provider->company_name = $request->company_name;
        $provider->company_email = $request->company_email;
        $provider->company_phone = $request->company_phone;
        if ($request->has('logo')) {
            $provider->logo = file_uploader('provider/logo/', 'png', $request->file('logo'), $provider->logo);
        }
        $provider->company_address = $request->company_address;

        $provider->contact_person_name = $request->contact_person_name;
        $provider->contact_person_phone = $request->contact_person_phone;
        $provider->contact_person_email = $request->contact_person_email;
        $provider->zone_id = $request['zone_id'];
        $provider->coordinates = [
            'latitude' => $request['latitude'],
            'longitude' => $request['longitude'],
        ];

        $owner = $this->user->where('id', $request->user()->id)->first();
        $owner->first_name = $request->account_first_name;
        $owner->last_name = $request->account_last_name;

        if (isset($request->password)) {
            $owner->password = bcrypt($request->password);
        }

        DB::transaction(function () use ($provider, $owner) {
            $owner->save();
            $provider->save();
        });

        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();
    }

    public function download(Request $request)
    {
        $keys = explode(' ', $request['search']);
        $items = $this->subscribedService->where('provider_id', $request->user()->provider->id)
            ->with(['sub_category' => function ($query) {
                return $query->withCount('services')->with(['services']);
            }])
            ->when($request->has('status') && $request['status'] != 'all', function ($query) use ($request) {
                return $query->where('is_subscribed', (($request['status'] == 'subscribed') ? 1 : 0));
            })
            ->where(function ($query) use ($keys) {
                foreach ($keys as $key) {
                    $query->orWhereHas('sub_category', function ($query) use ($key) {
                        $query->where('name', 'LIKE', '%' . $key . '%');
                    });
                }
            })->get();
        return (new FastExcel($items))->download(time() . '-file.xlsx');
    }

    public function reviewsDownload(Request $request)
    {
        $items = $this->review->with(['booking'])
            ->when($request->has('search'), function ($query) use ($request) {
                $query->whereHas('booking', function ($query) use ($request) {
                    $keys = explode(' ', $request['search']);
                    foreach ($keys as $key) {
                        $query->orWhere('readable_id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->where('provider_id', auth()->user()->provider->id)
            ->latest()
            ->get();
        return (new FastExcel($items))->download(time() . '-file.xlsx');
    }

    private function getPaymentMethods(): array
    {
        // Check if the addon_settings table exists
        if (!Schema::hasTable('addon_settings')) {
            return [];
        }

        $methods = DB::table('addon_settings')->where('settings_type', 'payment_config')->get();
        $env = env('APP_ENV') == 'live' ? 'live' : 'test';
        $credentials = $env . '_values';

        $data = [];
        foreach ($methods as $method) {
            $gateway_image = getPaymentGatewayImageFullPath(key: $method->key_name, settingsType: $method->settings_type, defaultPath: 'public/assets/admin-module/img/placeholder.png');
            $credentialsData = json_decode($method->$credentials);
            $additional_data = json_decode($method->additional_data);
            if ($credentialsData->status == 1) {
                $data[] = [
                    'gateway' => $method->key_name,
                    'gateway_image' => $gateway_image
                ];
            }
        }
        return $data;
    }

    public function deleteProvider(Request $request): RedirectResponse
    {
        $provider = $this->provider::where('user_id', $request->user()->id)->first();
        if ($provider) {
            $provider->delete();
            $provider->owner->delete();
            Toastr::success(translate(DEFAULT_DELETE_200['message']));
            Auth::logout();
        }
        Toastr::success(translate(DEFAULT_204['message']));
        return back();
    }

    private function routeFullUrl($uri)
    {
        $fullURL = url($uri);
        if ($uri == 'provider/booking/post'){
            $fullURL = url($uri). '?type=all';
        }if ($uri == 'provider/chat/index'){
            $fullURL = url($uri). '?user_type=super_admin';
        }if ($uri == 'provider/withdraw'){
            $fullURL = url($uri). '?page_type=withdraw_transaction';
        }if ($uri == 'provider/serviceman/list'){
            $fullURL = url($uri). '?status=all';
        }

        return $fullURL;
    }

    public function searchRouting(Request $request): array
    {
        $searchKeyword = $request->input('search');
        $providerId = Auth::user()->provider->id;

        //1st layer
        $formattedRoutes = [];
        $jsonFilePath = public_path('provider_formatted_routes.json');
        if (file_exists($jsonFilePath)) {
            $fileContents = file_get_contents($jsonFilePath);
            $routes = json_decode($fileContents, true);

            foreach ($routes as $route) {
                $uri = $route['URI'];

                if (Str::contains(strtolower($route['keywords']), strtolower($searchKeyword))) {
                    $hasParameters = preg_match('/\{(.*?)\}/', $uri);
                    $fullURL = $this->routeFullUrl($uri);

                    if (!$hasParameters) {
                        $routeName = $route['routeName'];
                        $formattedRoutes[] = [
                            'routeName' => ucwords($routeName),
                            'URI' => $uri,
                            'fullRoute' => $fullURL,
                        ];
                    }
                }
            }
        }

        //2nd layer
        $routes = Route::getRoutes();
        $providerRoutes = collect($routes->getRoutesByMethod()['GET'])->filter(function ($route) {
            return str_starts_with($route->uri(), 'provider');
        });

        $validRoutes = [];
        $isUuid = preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $searchKeyword);
        if ($isUuid === 1) {

            //booking
            $booking = Booking::where(['id' => $searchKeyword, 'provider_id' => $providerId])->first();
            if ($booking){
                $bookingRoutes = $providerRoutes->filter(function ($route) {
                    return str_contains($route->uri(), 'booking') && str_contains($route->uri(), 'details') && !str_contains($route->uri(), 'post');
                });
                if (isset($bookingRoutes)) {
                    foreach ($bookingRoutes as $route) {
                        $validRoutes[] = $this->filterRoute(model: $booking, route: $route, type: 'booking', name: $booking->readable_id, prefix: 'Booking');
                    }
                }
            }

            //multiple bookings with customer id
            $bookings = Booking::with(['customer', 'provider'])
                ->where(['provider_id' => $providerId])
                ->whereHas('customer', function ($query) use ($searchKeyword){
                    $query->where('id', $searchKeyword);
                })
                ->get();

            if ($bookings){
                $bookingsRoutes = $providerRoutes->filter(function ($route) {
                    return str_contains($route->uri(), 'booking') && str_contains($route->uri(), 'details') && !str_contains($route->uri(), 'post');
                });
                if (isset($bookingsRoutes)) {
                    foreach ($bookings as $booking)
                    {
                        foreach ($bookingsRoutes as $route) {
                            $validRoutes[] = $this->filterRoute(model: $booking, route: $route, type: 'booking', name: $booking->readable_id, prefix: 'Booking');
                        }
                    }
                }
            }

            //serviceman
            $serviceman = Serviceman::where(['id' => $searchKeyword, 'provider_id' => $providerId])->first();
            if ($serviceman){
                $serviceRoutes = $providerRoutes->filter(function ($route) {
                    return str_contains($route->uri(), 'service') && (str_contains($route->uri(), 'show') || str_contains($route->uri(), 'edit'));
                });

                if (isset($serviceRoutes)) {
                    foreach ($serviceRoutes as $route) {
                        $validRoutes[] = $this->filterRoute(model: $serviceman, route: $route, name:$serviceman->user?->first_name. ' '. $serviceman->user?->last_name, prefix: 'Serviceman');
                    }
                }
            }

            //ads
            $ads = Advertisement::where(['id' => $searchKeyword, 'provider_id' => $providerId])->first();
            if ($ads){
                $adsRoutes = $providerRoutes->filter(function ($route) {
                    return str_contains($route->uri(), 'advertisements') && (str_contains($route->uri(), 'edit') || str_contains($route->uri(), 'detail'));
                });
                if (isset($adsRoutes)) {
                    foreach ($adsRoutes as $route) {
                        $validRoutes[] = $this->filterRoute(model: $ads, route: $route, name:$ads->readable_id, prefix: 'Advertisement');
                    }
                }
            }
        }
        else {

            //booking
            $booking = Booking::where(['readable_id' => $searchKeyword, 'provider_id' => $providerId])->first();
            if ($booking){
                $bookingRoutes = $providerRoutes->filter(function ($route) {
                    return str_contains($route->uri(), 'booking') && str_contains($route->uri(), 'details') && !str_contains($route->uri(), 'post');
                });
                if (isset($bookingRoutes)) {
                    foreach ($bookingRoutes as $route) {
                        $validRoutes[] = $this->filterRoute(model: $booking, route: $route, type: 'booking', name: $booking->readable_id, prefix: 'Booking');
                    }
                }
            }

            $bookings = Booking::with('customer')
                ->where(['provider_id' => $providerId])
                ->whereHas('customer', function ($query) use ($searchKeyword){
                    $query->where('first_name', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhere('last_name', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhere('email', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhere('phone', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $searchKeyword . '%'])
                        ->orWhereRaw("CONCAT(first_name, last_name) LIKE ?", ['%' . $searchKeyword . '%'])
                        ->orWhereRaw("CONCAT(last_name,' ', last_name) LIKE ?", ['%' . $searchKeyword . '%']);
                })
                ->get();

            if ($bookings){
                $bookingsRoutes = $providerRoutes->filter(function ($route) {
                    return str_contains($route->uri(), 'booking') && str_contains($route->uri(), 'details') && !str_contains($route->uri(), 'post');
                });
                if (isset($bookingsRoutes)) {
                    foreach ($bookings as $booking)
                    {
                        foreach ($bookingsRoutes as $route) {
                            $validRoutes[] = $this->filterRoute(model: $booking, route: $route, type: 'booking', name: $booking->readable_id, prefix: 'Booking');
                        }
                    }
                }
            }

            //serviceman
            $allServiceman = Serviceman::where(['provider_id' => $providerId])
                ->whereHas('user', function ($query) use ($searchKeyword){
                    $query->where('first_name', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhere('last_name', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhere('email', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhere('phone', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $searchKeyword . '%'])
                        ->orWhereRaw("CONCAT(first_name, last_name) LIKE ?", ['%' . $searchKeyword . '%'])
                        ->orWhereRaw("CONCAT(last_name,' ', last_name) LIKE ?", ['%' . $searchKeyword . '%']);
                })
                ->get();

            if ($allServiceman){
                $serviceRoutes = $providerRoutes->filter(function ($route) {
                    return str_contains($route->uri(), 'service') && (str_contains($route->uri(), 'show') || str_contains($route->uri(), 'edit'));
                });

                if (isset($serviceRoutes)) {
                    foreach ($allServiceman as $serviceman){
                        foreach ($serviceRoutes as $route) {
                            $validRoutes[] = $this->filterRoute(model: $serviceman, route: $route, name:$serviceman->user?->first_name. ' '. $serviceman->user?->last_name, prefix: 'Serviceman');
                        }
                    }
                }
            }

            //ads
            $allAds = Advertisement::where(['provider_id' => $providerId])
                ->where(function($query) use ($searchKeyword) {
                    $query->where('readable_id', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhere('title', 'LIKE', '%' . $searchKeyword . '%');
                })
                ->get();

            if ($allAds){
                $adsRoutes = $providerRoutes->filter(function ($route) {
                    return str_contains($route->uri(), 'advertisements') && (str_contains($route->uri(), 'edit') || str_contains($route->uri(), 'detail'));
                });
                if (isset($adsRoutes)) {
                    foreach ($allAds as $ads){
                        foreach ($adsRoutes as $route) {
                            $validRoutes[] = $this->filterRoute(model: $ads, route: $route, name: $ads->readable_id, prefix: 'Advertisement');
                        }
                    }
                }
            }
        }

        $allRoutes = array_merge($formattedRoutes, $validRoutes);

        return $allRoutes;
    }

    private function filterRoute($model, $route, $type = null, $name = null, $prefix = null)
    {
        $uri = $route->uri();
        $routeName = $route->getName();
        $formattedRouteName = ucwords(str_replace(['.', '_'], ' ', Str::afterLast($routeName, '.')));
        $uriWithParameter = str_replace('{id}', $model->id, $uri);
        $fullURL = url('/') . '/' . $uriWithParameter;
        if ($type == 'booking'){
            $fullURL = url('/') . '/' . $uriWithParameter. '?web_page=details';
        }
        if ($type == 'customer'){
            $fullURL = $formattedRouteName == 'Detail' ? $fullURL. '?web_page=overview' : $fullURL;
        }

        $routeName = $prefix ? $prefix. ' '. $formattedRouteName : $formattedRouteName;
        $routeName = $name ? $routeName. ' - (' . $name. ')' : $routeName;

        $routeInfo = [
            'routeName' => $routeName,
            'URI' => $uriWithParameter,
            'fullRoute' => $fullURL,
        ];
        return $routeInfo;
    }

    public function setModalClosed(Request $request): JsonResponse
    {
        Session::put('modalClosed', true);
        return response()->json(['success' => true]);
    }

    public function subscribeToTopic(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'topic' => 'required|string',
        ]);
        $token = $request->input('token');
        $topic = $request->input('topic');
        try {
            if($this->messaging){
                $this->messaging->subscribeToTopic($topic, $token);
                return response()->json(['message' => 'Successfully subscribed to topic'], 200);
            }
            return response()->json(['message' => 'Unauthorized'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    // city pabel 
    public function dashboardCityPanel(Request $request): Renderable
    {
        dd("stop");
        
        return view('providermanagement::franchise-dashboard');

        return view('providermanagement::dashboard', compact('data', 'chart_data', 'booking_counts'));
    }
}
