<?php

namespace Modules\AdminModule\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Entities\Tendor;

use Modules\BookingModule\Entities\BookingDetailsAmount;
use Modules\CategoryManagement\Entities\Category;
use Modules\ChattingModule\Entities\ChannelList;
use Modules\PaymentModule\Entities\Bonus;
use Modules\PromotionManagement\Entities\Advertisement;
use Modules\PromotionManagement\Entities\Banner;
use Modules\PromotionManagement\Entities\Campaign;
use Modules\PromotionManagement\Entities\Coupon;
use Modules\PromotionManagement\Entities\Discount;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ServiceManagement\Entities\Service;
use Modules\TransactionModule\Entities\Account;
use Modules\TransactionModule\Entities\Transaction;
use Modules\UserManagement\Entities\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use function auth;
use function bcrypt;
use function file_uploader;
use function response;
use function response_formatter;
use function view;

class AdminController extends Controller
{
    protected Provider $provider;
    protected Account $account;
    protected Booking $booking;
    protected Service $service;
    protected User $user;
    protected Transaction $transaction;
    protected ChannelList $channelList;
    protected BookingDetailsAmount $booking_details_amount;
    use AuthorizesRequests;

    public function __construct(ChannelList $channelList, Provider $provider, Service $service, Account $account, Booking $booking, User $user, Transaction $transaction, BookingDetailsAmount $booking_details_amount)
    {
        $this->provider = $provider;
        $this->service = $service;
        $this->account = $account;
        $this->booking = $booking;
        $this->user = $user;
        $this->transaction = $transaction;
        $this->channelList = $channelList;
        $this->booking_details_amount = $booking_details_amount;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @param Transaction $transaction
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function dashboard(Request $request, Transaction $transaction): View|Factory|Application
    {
        $commission_earning = BookingDetailsAmount::whereHas('booking', function ($query) use ($request) {
            $query->ofBookingStatus('completed');
            })
            ->sum('admin_commission');

        $fee_amounts = $this->transaction->where('trx_type', TRX_TYPE['received_extra_fee'])->sum('credit');
        $subscription_amounts = $this->transaction->whereIn('trx_type', ['subscription_purchase', 'subscription_renew', 'subscription_shift'])->sum('credit');

        $data = [];
        $data[] = ['top_cards' => [
            'total_commission_earning' => $commission_earning ?? 0,
            'total_fee_earning' => $fee_amounts ?? 0,
            'total_subscription_earning' => $subscription_amounts ?? 0,
            'total_system_earning' => $this->account->sum('received_balance') + $this->account->sum('total_withdrawn'),
            'total_customer' => $this->user->where(['user_type' => 'customer'])->count(),
            'total_provider' => $this->provider->where(['is_approved' => 1])->count(),
            'total_services' => $this->service->count()
        ]];

        $total_earning = $this->booking_details_amount
            ->whereHas('booking', function ($query) use ($request) {
                $query->ofBookingStatus('completed');
            })->get()->sum('admin_commission');

        $data[] = ['admin_total_earning' => $total_earning];

        $recent_transactions = $this->transaction
            ->with(['booking'])
            ->whereMonth('created_at', now()->month)
            ->latest()
            ->take(5)
            ->get();
        $data[] = [
            'recent_transactions' => $recent_transactions,
            'this_month_trx_count' => $transaction->count()
        ];

        $bookings = $this->booking->with(['detail.service' => function ($query) {
            $query->select('id', 'name', 'thumbnail');
        }])
            ->where('booking_status', 'pending')
            ->take(5)->latest()->get();
        $data[] = ['bookings' => $bookings];

        $top_providers = $this->provider
            ->withCount(['reviews'])
            ->with(['owner', 'reviews'])
            ->ofApproval(1)
            ->take(5)->orderBy('avg_rating', 'DESC')->get();
        $data[] = ['top_providers' => $top_providers];

        $zone_wise_bookings = $this->booking
            ->with(['zone' => function ($query) {
                $query->withoutGlobalScope('translate');
            }])
            ->whereHas('zone', function ($query) {
                $query->ofStatus(1)->withoutGlobalScope('translate');
            })
            ->whereMonth('created_at', now()->month)
            ->select('zone_id', DB::raw('count(*) as total'))
            ->groupBy('zone_id')
            ->get();
        $data[] = ['zone_wise_bookings' => $zone_wise_bookings, 'total_count' => $this->booking->count()];

        $year = session()->has('dashboard_earning_graph_year') ? session('dashboard_earning_graph_year') : date('Y');
        $amounts = $this->booking_details_amount
            ->whereHas('booking', function ($query) use ($request, $year) {
                $query->whereYear('created_at', '=', $year)->ofBookingStatus('completed');
            })
            ->select(
                DB::raw('sum(admin_commission) as admin_commission'),

                DB::raw('MONTH(created_at) month')
            )
            ->groupby('month')->get()->toArray();

        $fee_amounts = $this->transaction
            ->whereIn('trx_type', [
                TRX_TYPE['received_extra_fee'],
                TRX_TYPE['subscription_purchase'],
                TRX_TYPE['subscription_renew'],
                TRX_TYPE['subscription_shift']
            ])
            ->select(
                DB::raw('sum(credit) as fee'),

                DB::raw('MONTH(created_at) month')
            )
            ->groupby('month')->get()->toArray();

        $all_earnings = [];
        if (empty($amounts) && !empty($fee_amounts)) {
            foreach ($fee_amounts as $key => $fee) {
                $all_earnings[$key] = $fee;
                if (!array_key_exists('fee', $all_earnings[$key])) {
                    $all_earnings[$key]['fee'] = 0;
                }
            }
        } else {
            foreach ($amounts as $amount) {
                foreach ($fee_amounts as $key => $fee) {
                    if ($amount['month'] == $fee['month']) {
                        $all_earnings[$key] = array_merge($amount, $fee);
                    }
                    if (!isset($all_earnings[$key])) {
                        $all_earnings[$key] = $amount;
                    }
                    if (!array_key_exists('fee', $all_earnings[$key])) {
                        $all_earnings[$key]['fee'] = 0;
                    }
                }
            }
        }

        $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        foreach ($months as $month) {
            $found = 0;
            foreach ($all_earnings as $key => $item) {
                if (isset($item['month']) && $item['month'] == $month) {
                    $admin_commission = $item['admin_commission'] ?? 0;
                    $itemFee = $item['fee'] ?? 0;

                    $chart_data['total_earning'][] = with_decimal_point($admin_commission + $itemFee);
                    $chart_data['commission_earning'][] = with_decimal_point($admin_commission);
                    $found = 1;
                    break;
                }
            }
            if (!$found) {
                $chart_data['total_earning'][] = with_decimal_point(0);
                $chart_data['commission_earning'][] = with_decimal_point(0);
            }
        }

        return view('adminmodule::dashboard', compact('data', 'chart_data'));
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
                $query->whereYear('created_at', '=', $year)->ofBookingStatus('completed');
            })
            ->select(
                DB::raw('sum(admin_commission) as admin_commission'),

                DB::raw('MONTH(created_at) month')
            )
            ->groupby('month')->get()->toArray();

        $fee_amounts = $this->transaction
            ->whereYear('created_at', '=', $year)
            ->whereIn('trx_type', [
                TRX_TYPE['received_extra_fee'],
                TRX_TYPE['subscription_purchase'],
                TRX_TYPE['subscription_renew'],
                TRX_TYPE['subscription_shift']
            ])
            ->select(
                DB::raw('sum(credit) as fee'),

                DB::raw('MONTH(created_at) month')
            )
            ->groupby('month')->get()->toArray();

        $all_earnings = [];
        foreach ($amounts as $amount) {
            foreach ($fee_amounts as $key=>$fee) {
                if ($amount['month'] == $fee['month']) {
                    $all_earnings[$key] = array_merge($amount, $fee);
                }
                if (!isset($all_earnings[$key])) {
                    $all_earnings[$key] = $amount;
                }
                if (!array_key_exists('fee', $all_earnings[$key])) {
                    $all_earnings[$key]['fee'] = 0;
                }
            }
        }

        $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        foreach ($months as $month) {
            $found = 0;
            foreach ($all_earnings as $key => $item) {
                if ($item['month'] == $month) {
                    $chart_data['total_earning'][] = with_decimal_point($item['admin_commission']+$item['fee']);
                    $chart_data['commission_earning'][] = with_decimal_point($item['admin_commission']);
                    $found = 1;
                }
            }
            if (!$found) {
                $chart_data['total_earning'][] = with_decimal_point(0);
                $chart_data['commission_earning'][] = with_decimal_point(0);
            }
        }

        session()->put('dashboard_earning_graph_year', $request['year']);

        return response()->json($chart_data);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        if (in_array($request->user()->user_type, ADMIN_USER_TYPES)) {
            $user = $this->user->where(['id' => auth('api')->id()])->with(['roles'])->first();
            return response()->json(response_formatter(DEFAULT_200, $user), 200);
        }
        return response()->json(response_formatter(DEFAULT_403), 401);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function edit(Request $request): JsonResponse
    {
        if (in_array($request->user()->user_type, ADMIN_USER_TYPES)) {
            return response()->json(response_formatter(DEFAULT_200, auth('api')->user()), 200);
        }
        return response()->json(response_formatter(DEFAULT_403), 401);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function profileInfo(Request $request): Renderable
    {
        return view('adminmodule::admin.profile-update');
    }

    /**
     * Modify provider information
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'profile_image' => 'image|mimes:jpeg,jpg,png,gif|max:10240',
            'password' => '',
            'confirm_password' => !is_null($request->password) ? 'required|same:password' : '',
        ]);

        $user = $this->user->find($request->user()->id);
        $user->first_name = $request->first_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->last_name = $request->last_name;
        if ($request->has('profile_image')) {
            $user->profile_image = file_uploader('user/profile_image/', 'png', $request->profile_image, $user->profile_image);
        }
        if (!is_null($request->password)) {
            $user->password = bcrypt($request->confirm_password);
        }
        $user->save();

        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function getUpdatedData(Request $request): JsonResponse
    {
        $message = $this->channelList->wherehas('channelUsers', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id)->where('is_read', 0);
        })->count();

        return response()->json([
            'status' => 1,
            'data' => [
                'message' => $message
            ]
        ]);
    }

    private function routeFullUrl($uri)
    {
        $fullURL = url($uri);
        if ($uri == 'admin/booking/list/verification'){
            $fullURL = url($uri). '?booking_status=pending&type=pending';
        }
        if ($uri == 'admin/booking/list'){
            $fullURL = url($uri). '?booking_status=pending';
        }
        if ($uri == 'admin/configuration/get-notification-setting'){
            $fullURL = url($uri). '?type=customers';
        }
        if ($uri == 'admin/customer/settings'){
            $fullURL = url($uri). '?web_page=loyalty_point';
        }
        if ($uri == 'admin/chat/index'){
            $fullURL = url($uri). '?user_type=customer';
        }
        return $fullURL;
    }

    public function searchRouting(Request $request)
    {
        $searchKeyword = $request->input('search');

        //1st layer
        $formattedRoutes = [];
        $jsonFilePath = public_path('admin_formatted_routes.json');
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
        $adminRoutes = collect($routes->getRoutesByMethod()['GET'])->filter(function ($route) {
            return str_starts_with($route->uri(), 'admin');
        });

        $validRoutes = [];
        $isUuid = preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $searchKeyword);
        if ($isUuid === 1) {
            //provider
            $provider = Provider::with('owner')
                ->where('id', $searchKeyword)
                ->orWhereHas('owner', function ($query) use ($searchKeyword){
                    $query->where('id', $searchKeyword);
                })
                ->first();

            if ($provider){
                if ($provider->is_active == 1){
                    $providerRoutes = $adminRoutes->filter(function ($route) {
                        return str_contains($route->uri(), 'provider') &&
                            (str_contains($route->uri(), 'edit') || (str_contains($route->uri(), 'details') && !str_contains($route->uri(), 'onboarding-details')));
                    });
                }else{
                    $providerRoutes = $adminRoutes->filter(function ($route) {
                        return str_contains($route->uri(), 'provider') &&
                            (str_contains($route->uri(), 'edit') || str_contains($route->uri(), 'onboarding-details'));
                    });
                }

                if (isset($providerRoutes)) {
                    foreach ($providerRoutes as $route) {
                        $validRoutes[] = $this->filterRoute(model: $provider, route: $route, prefix: 'Provider');
                    }
                }
            }

            //customer
            $customer = User::where(['user_type' => 'customer'])->find($searchKeyword);
            if ($customer){
                $customerRoutes = $adminRoutes->filter(function ($route) {
                    return str_contains($route->uri(), 'customer') && (str_contains($route->uri(), 'edit') || str_contains($route->uri(), 'detail'));
                });

                if (isset($customerRoutes)) {
                    foreach ($customerRoutes as $route) {
                        $validRoutes[] = $this->filterRoute(model: $customer, route: $route, type: 'customer', prefix: 'Customer');
                    }
                }
            }

            //booking
            $booking = Booking::find($searchKeyword);
            if ($booking){
                $bookingRoutes = $adminRoutes->filter(function ($route) {
                    return str_contains($route->uri(), 'booking') && str_contains($route->uri(), 'details') && !str_contains($route->uri(), 'post');
                });
                if (isset($bookingRoutes)) {
                    foreach ($bookingRoutes as $route) {
                        $validRoutes[] = $this->filterRoute(model: $booking, route: $route, type: 'booking', prefix: 'Booking');
                    }
                }
            }

            //multiple bookings with customer id
            $bookings = Booking::with(['customer', 'provider'])
                ->whereHas('customer', function ($query) use ($searchKeyword){
                    $query->where('id', $searchKeyword);
                })
                ->orWhereHas('provider', function ($query) use ($searchKeyword){
                    $query->where('id', $searchKeyword);
                })
                ->get();

            if ($bookings){
                $bookingsRoutes = $adminRoutes->filter(function ($route) {
                    return str_contains($route->uri(), 'booking') && str_contains($route->uri(), 'details') && !str_contains($route->uri(), 'post');
                });
                if (isset($bookingsRoutes)) {
                    foreach ($bookings as $booking)
                    {
                        foreach ($bookingsRoutes as $route) {
                            $validRoutes[] = $this->filterRoute(model: $booking, route: $route, type: 'booking', prefix: 'Booking');
                        }
                    }
                }
            }

            //service
            $service = Service::find($searchKeyword);
            if ($service){
                $serviceRoutes = $adminRoutes->filter(function ($route) {
                    return str_contains($route->uri(), 'service') && (str_contains($route->uri(), 'edit') || str_contains($route->uri(), 'detail'));
                });

                if (isset($serviceRoutes)) {
                    foreach ($serviceRoutes as $route) {
                        $validRoutes[] = $this->filterRoute(model: $service, route: $route, prefix: 'Service');
                    }
                }
            }

            //ads
            $ads = Advertisement::find($searchKeyword);
            if ($ads){
                $adsRoutes = $adminRoutes->filter(function ($route) {
                    return str_contains($route->uri(), 'advertisements') && (str_contains($route->uri(), 'edit') || str_contains($route->uri(), 'detail'));
                });
                if (isset($adsRoutes)) {
                    foreach ($adsRoutes as $route) {
                        $validRoutes[] = $this->filterRoute(model: $ads, route: $route, prefix: 'Advertisement');
                    }
                }
            }

        }
        else {

            //provider
            $providers = Provider::where('company_name', 'LIKE', '%' . $searchKeyword . '%')
                ->orWhere('company_phone', 'LIKE', '%' . $searchKeyword . '%')
                ->orWhere('company_email', 'LIKE', '%' . $searchKeyword . '%')
                ->orWhere('contact_person_name', 'LIKE', '%' . $searchKeyword . '%')
                ->orWhere('contact_person_phone', 'LIKE', '%' . $searchKeyword . '%')
                ->orWhere('contact_person_email', 'LIKE', '%' . $searchKeyword . '%')
                ->orWhereHas('owner', function($query) use ($searchKeyword){
                    return $query->where('first_name', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhere('last_name', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhere('email', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhere('phone', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $searchKeyword . '%'])
                        ->orWhereRaw("CONCAT(last_name, ' ', first_name) LIKE ?", ['%' . $searchKeyword . '%'])
                        ->orWhereRaw("CONCAT(last_name,first_name) LIKE ?", ['%' . $searchKeyword . '%'])
                        ->orWhereRaw("CONCAT(first_name,last_name) LIKE ?", ['%' . $searchKeyword . '%']);
                })
                ->get();

            if ($providers){
                foreach ($providers as $provider){
                    if ($provider->is_active == 1){
                        $providerRoutes = $adminRoutes->filter(function ($route) {
                            return str_contains($route->uri(), 'provider') &&
                                (str_contains($route->uri(), 'edit') || (str_contains($route->uri(), 'details') && !str_contains($route->uri(), 'onboarding-details')));
                        });
                    }else{
                        $providerRoutes = $adminRoutes->filter(function ($route) {
                            return str_contains($route->uri(), 'provider') &&
                                (str_contains($route->uri(), 'edit') || str_contains($route->uri(), 'onboarding-details'));
                        });
                    }

                    if (isset($providerRoutes)) {
                        foreach ($providerRoutes as $route) {
                            $validRoutes[] = $this->filterRoute(model: $provider, route: $route, name: $provider->contact_person_name, prefix: 'Provider');
                        }
                    }
                }
            }

            //customer

            $customers = User::where('user_type', '=', 'customer')
                ->where(function($query) use ($searchKeyword) {
                    $query->where('first_name', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhere('last_name', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhere('email', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhere('phone', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $searchKeyword . '%'])
                        ->orWhereRaw("CONCAT(first_name,last_name) LIKE ?", ['%' . $searchKeyword . '%'])
                        ->orWhereRaw("CONCAT(last_name,first_name) LIKE ?", ['%' . $searchKeyword . '%']);
                })
                ->get();

            if ($customers){
                $customerRoutes = $adminRoutes->filter(function ($route) {
                    return str_contains($route->uri(), 'customer') && (str_contains($route->uri(), 'edit') || str_contains($route->uri(), 'detail'));
                });
                if (isset($customerRoutes)) {
                    foreach ($customers as $customer){
                        foreach ($customerRoutes as $route) {
                            $validRoutes[] = $this->filterRoute(model: $customer, route: $route, type: 'customer', name: $customer->first_name. ' '. $customer->last_name, prefix: 'Customer');
                        }
                    }
                }
            }

            //booking
            $booking = Booking::firstWhere(['readable_id' => $searchKeyword]);
            if ($booking){
                $bookingRoutes = $adminRoutes->filter(function ($route) {
                    return str_contains($route->uri(), 'booking') && str_contains($route->uri(), 'details') && !str_contains($route->uri(), 'post');
                });
                if (isset($bookingRoutes)) {
                    foreach ($bookingRoutes as $route) {
                        $validRoutes[] = $this->filterRoute(model: $booking, route: $route, type: 'booking', name: $booking->readable_id, prefix: 'Booking');
                    }
                }
            }

            $bookings = Booking::with('customer')
                ->whereHas('customer', function ($query) use ($searchKeyword){
                    $query->where('first_name', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhere('last_name', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhere('email', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhere('phone', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $searchKeyword . '%'])
                        ->orWhereRaw("CONCAT(first_name,last_name) LIKE ?", ['%' . $searchKeyword . '%'])
                        ->orWhereRaw("CONCAT(last_name,first_name) LIKE ?", ['%' . $searchKeyword . '%']);
                })
                ->orWhereHas('provider', function ($query) use ($searchKeyword){
                    $query->where('company_name', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhere('company_phone', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhere('company_email', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhere('contact_person_name', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhere('contact_person_phone', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhere('contact_person_email', 'LIKE', '%' . $searchKeyword . '%');
                })
                ->get();

            if ($bookings){
                $bookingsRoutes = $adminRoutes->filter(function ($route) {
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

            //service
            $services = Service::with(['tags', 'faqs'])
                ->where('name', 'LIKE', '%' . $searchKeyword . '%')
                ->orWhere('short_description', 'LIKE', '%' . $searchKeyword . '%')
                ->orWhereHas('tags', function ($query) use ($searchKeyword){
                    $query->where('tag', 'LIKE', '%' . $searchKeyword . '%');
                })
                ->orWhereHas('faqs', function ($query) use ($searchKeyword){
                    $query->where('question', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhere('answer', 'LIKE', '%' . $searchKeyword . '%');
                })
                ->get();

            if ($services){
                $serviceRoutes = $adminRoutes->filter(function ($route) {
                    return str_contains($route->uri(), 'service') && (str_contains($route->uri(), 'edit') || str_contains($route->uri(), 'detail'));
                });
                if (isset($serviceRoutes)) {
                    foreach ($services as $service){
                        foreach ($serviceRoutes as $route) {
                            $validRoutes[] = $this->filterRoute(model: $service, route: $route, name: $service->name, prefix: 'Service');
                        }
                    }
                }
            }

            //ads
            $allAds = Advertisement::where('readable_id', 'LIKE', '%' . $searchKeyword . '%')
                ->orWhere('title', 'LIKE', '%' . $searchKeyword . '%')
                ->get();

            if ($allAds){
                $adsRoutes = $adminRoutes->filter(function ($route) {
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

            //coupon
            $coupons = Coupon::with('discount')
                ->where('coupon_code', 'LIKE', '%' . $searchKeyword . '%')
                ->orWhereHas('discount', function ($query) use($searchKeyword){
                    return $query->where('discount_title', 'LIKE', '%' . $searchKeyword . '%');
                })
                ->get();

            if ($coupons){
                $couponRoutes = $adminRoutes->filter(function ($route) {
                    return str_contains($route->uri(), 'coupon') && (str_contains($route->uri(), 'edit'));
                });
                if (isset($couponRoutes)) {
                    foreach ($coupons as $coupon){
                        foreach ($couponRoutes as $route) {
                            $validRoutes[] = $this->filterRoute(model: $coupon, route: $route, name: $coupon->coupon_code, prefix: 'Coupon');
                        }
                    }
                }
            }

            //discount
            $discounts = Discount::where('discount_title', 'LIKE', '%' . $searchKeyword . '%')->where('promotion_type', '!=', 'coupon')->get();
            if ($discounts){
                $discountRoutes = $adminRoutes->filter(function ($route) {
                    return str_contains($route->uri(), 'discount') && (str_contains($route->uri(), 'edit'));
                });
                if (isset($discountRoutes)) {
                    foreach ($discounts as $discount){
                        foreach ($discountRoutes as $route) {
                            $validRoutes[] = $this->filterRoute(model: $discount, route: $route, name: $discount->discount_title, prefix: 'Discount');
                        }
                    }
                }
            }

            //bonus
            $bonuses = Bonus::where('bonus_title', 'LIKE', '%' . $searchKeyword . '%')->get();
            if ($bonuses){
                $bonusRoutes = $adminRoutes->filter(function ($route) {
                    return str_contains($route->uri(), 'bonus') && (str_contains($route->uri(), 'edit'));
                });
                if (isset($bonusRoutes)) {
                    foreach ($bonuses as $bonus){
                        foreach ($bonusRoutes as $route) {
                            $validRoutes[] = $this->filterRoute(model: $bonus, route: $route, name: $bonus->bonus_title, prefix: 'Bonus');
                        }
                    }
                }
            }

            //campaign
            $campaigns = Campaign::where('campaign_name', 'LIKE', '%' . $searchKeyword . '%')->get();
            if ($campaigns){
                $campaignRoutes = $adminRoutes->filter(function ($route) {
                    return str_contains($route->uri(), 'campaign') && (str_contains($route->uri(), 'edit'));
                });
                if (isset($campaignRoutes)) {
                    foreach ($campaigns as $campaign){
                        foreach ($campaignRoutes as $route) {
                            $validRoutes[] = $this->filterRoute(model: $campaign, route: $route, name: $campaign->campaign_name, prefix: 'Campaign');
                        }
                    }
                }
            }

            //banner
            $banners = Banner::where('banner_title', 'LIKE', '%' . $searchKeyword . '%')->get();
            if ($banners){
                $bannerRoutes = $adminRoutes->filter(function ($route) {
                    return str_contains($route->uri(), 'banner') && (str_contains($route->uri(), 'edit'));
                });
                if (isset($bannerRoutes)) {
                    foreach ($banners as $banner)
                    {
                        foreach ($bannerRoutes as $route) {
                            $validRoutes[] = $this->filterRoute(model: $banner, route: $route, name: $banner->banner_title, prefix: 'Banner');
                        }
                    }
                }
            }

            //category
            $categories = Category::ofType('main')->where('name', 'LIKE', '%' . $searchKeyword . '%')->get();
            if ($categories){
                $categoryRoutes = $adminRoutes->filter(function ($route) {
                    return str_contains($route->uri(), 'category') && (str_contains($route->uri(), 'edit'));
                });
                if (isset($categoryRoutes)) {
                    foreach ($categories as $category)
                    {
                        foreach ($categoryRoutes as $route) {
                            $validRoutes[] = $this->filterRoute(model: $category, route: $route, name: $category->name, prefix: 'Category');
                        }
                    }
                }
            }

            //sub category
            $subCategories = Category::ofType('sub')->where('name', 'LIKE', '%' . $searchKeyword . '%')->get();
            if ($subCategories){
                $subCategoryRoutes = $adminRoutes->filter(function ($route) {
                    return str_contains($route->uri(), 'sub-category') && (str_contains($route->uri(), 'edit'));
                });
                if (isset($subCategoryRoutes)) {
                    foreach ($subCategories as $category)
                    {
                        foreach ($subCategoryRoutes as $route) {
                            $validRoutes[] = $this->filterRoute(model: $category, route: $route, name: $category->name, prefix: 'Sub Category');
                        }
                    }
                }
            }
        }

        $allRoutes = array_merge($formattedRoutes, $validRoutes);

        return $allRoutes;
    }

    /**
     * @return array{routeName: string, URI: array|string|string[], fullRoute: string}
     */
    private function filterRoute($model, $route, $type = null, $name = null, $prefix = null): array
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
    
    
    public function tendors(Request $request): Renderable
    {
        $tendors = Tendor::with('applicants')->get();
  
        return view('adminmodule::admin.tendors', compact('tendors'));
    }

    public function tendorCreate()
    {
        return view('adminmodule::admin.tendorCreate');
    }

    public function tendorStore(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'type' => 'required',
            'location' => 'required',
            'duration' => 'required',
            'material_type' => 'required',
            'category_type' => 'required',
            'emd' => 'required',
            'fee' => 'required',
            'desc' => 'required',
        ]);

        Tendor::create($request->all());
        return redirect()->route('admin.tendors')->with('success', 'Tendor added!');
    }

    public function tendorEdit($td)
    {
        $tendor = Tendor::find($td);
        return view('adminmodule::admin.tendorsEdit', compact('tendor'));
    }

    public function tendorUpdate(Request $request, $td)
    {
        $tendor = Tendor::find($td);
        $tendor->update($request->all());
        return redirect()->route('admin.tendors')->with('success', 'Tendor updated!');
    }

    public function tendorDestroy($td)
    {
              $tendor = Tendor::find($td);
        $tendor->delete();
        return redirect()->route('admin.tendors')->with('success', 'Tendor deleted!');
    }

}
