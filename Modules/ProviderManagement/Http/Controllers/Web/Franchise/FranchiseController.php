<?php

namespace Modules\ProviderManagement\Http\Controllers\Web\Franchise;

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
use Modules\PaymentModule\Traits\SubscriptionTrait;
use Modules\BusinessSettingsModule\Entities\SubscriptionPackage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\BusinessSettingsModule\Entities\PackageSubscriberFeature;
use Modules\BusinessSettingsModule\Entities\PackageSubscriberLimit;
use Modules\PaymentModule\Entities\PaymentRequest;

class FranchiseController extends Controller
{
   protected Provider $provider;
    protected User $owner;
    protected User $user;
    protected Service $service;
    protected SubscribedService $subscribedService;
    private Booking $booking;
    private Serviceman $serviceman;
    private SubscriptionPackage $subscriptionPackage;
    private PackageSubscriber $packageSubscriber;
    private PackageSubscriberFeature $packageSubscriberFeature;
    private PackageSubscriberLimit $packageSubscriberLimit;
    private Review $review;
    protected Transaction $transaction;
    protected Zone $zone;
    protected BankDetail $bank_detail;
    protected PaymentRequest $paymentRequest;

    use AuthorizesRequests;
    use SubscriptionTrait;

    public function __construct
    (
        Transaction $transaction,
        Review $review,
        Serviceman $serviceman,
        Provider $provider,
        User $owner,
        Service $service,
        SubscribedService $subscribedService,
        Booking $booking,
        Zone $zone,
        BankDetail $bank_detail,
        PackageSubscriber $packageSubscriber,
        SubscriptionPackage $subscriptionPackage,
        PackageSubscriberFeature $packageSubscriberFeature,
        PackageSubscriberLimit $packageSubscriberLimit,
        PaymentRequest $paymentRequest
    )
    {
        $this->provider = $provider;
        $this->owner = $owner;
        $this->user = $owner;
        $this->service = $service;
        $this->subscribedService = $subscribedService;
        $this->booking = $booking;
        $this->serviceman = $serviceman;
        $this->review = $review;
        $this->transaction = $transaction;
        $this->zone = $zone;
        $this->bank_detail = $bank_detail;
        $this->subscriptionPackage = $subscriptionPackage;
        $this->packageSubscriber = $packageSubscriber;
        $this->packageSubscriberFeature = $packageSubscriberFeature;
        $this->packageSubscriberLimit = $packageSubscriberLimit;
        $this->paymentRequest = $paymentRequest;
    }
    
    // city pabel 
    public function dashboardCityPanel(Request $request): Renderable
    {

        return view('providermanagement::franchise-dashboard');

        return view('providermanagement::dashboard', compact('data', 'chart_data', 'booking_counts'));
    }
    
        public function index(Request $request): Renderable
    {
        $this->authorize('provider_view');

        Validator::make($request->all(), [
            'search' => 'string',
            'status' => 'required|in:active,inactive,all'
        ]);

        $search = $request->has('search') ? $request['search'] : '';
        $status = $request->has('status') ? $request['status'] : 'all';
        $queryParam = ['search' => $search, 'status' => $status];

        $providers = $this->provider->with(['owner', 'zone'])->where(['is_approved' => 1])->withCount(['subscribed_services', 'bookings'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('company_phone', 'LIKE', '%' . $key . '%')
                            ->orWhere('company_email', 'LIKE', '%' . $key . '%')
                            ->orWhere('company_name', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->ofApproval(1)
            ->when($request->has('status') && $request['status'] != 'all', function ($query) use ($request) {
                return $query->ofStatus(($request['status'] == 'active') ? 1 : 0);
            })->latest()
            ->paginate(pagination_limit())->appends($queryParam);

        $topCards = [];
        $topCards['total_providers'] = $this->provider->ofApproval(1)->count();
        $topCards['total_onboarding_requests'] = $this->provider->ofApproval(2)->count();
        $topCards['total_active_providers'] = $this->provider->ofApproval(1)->ofStatus(1)->count();
        $topCards['total_inactive_providers'] = $this->provider->ofApproval(1)->ofStatus(0)->count();
        return view('providermanagement::provider.provider.index', compact('providers', 'topCards', 'search', 'status'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     * @throws AuthorizationException
     */
    public function create(): Renderable
    {
         $zones = $this->zone->get();
        $commission = (int)((business_config('provider_commision', 'provider_config'))->live_values ?? null);
        $subscription = (int)((business_config('provider_subscription', 'provider_config'))->live_values ?? null);
        $duration = (int)((business_config('free_trial_period', 'subscription_Setting'))->live_values ?? null);
        $freeTrialStatus = (int)((business_config('free_trial_period', 'subscription_Setting'))->is_active ?? 0);
        $subscriptionPackages = $this->subscriptionPackage->OfStatus(1)->with('subscriptionPackageFeature', 'subscriptionPackageLimit')->get();
        $formattedPackages = $subscriptionPackages->map(function ($subscriptionPackage) {
            return formatSubscriptionPackage($subscriptionPackage, PACKAGE_FEATURES);
        });
        return view('providermanagement::provider.panel.create', compact('zones','commission','subscription','formattedPackages', 'duration', 'freeTrialStatus'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('provider_add');
        $request->validate([
            'contact_person_name' => 'required',
            'contact_person_phone' => 'required',
            'contact_person_email' => 'required',

            'account_email' => 'required|email',
            'account_phone' => 'required',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password',

            'company_name' => 'required',
            'company_phone' => 'required',
            'company_address' => 'required',
            'company_email' => 'required|email',
            'logo' => 'required|image|mimes:jpeg,jpg,png,gif',

            'identity_type' => 'required|in:passport,driving_license,nid,trade_license,company_id',
            'identity_number' => 'required',
            'identity_images' => 'array',
            'identity_images.*' => 'image|mimes:jpeg,jpg,png,gif',
            'latitude' => 'required',
            'longitude' => 'required',

            'zone_id' => 'required|uuid',
        ]);

        if (User::where('email', $request['account_email'])->first()) {
            Toastr::error(translate('Email already taken'));
            return back();
        }
        if (User::where('phone', $request['account_phone'])->first()) {
            Toastr::error(translate('Phone already taken'));
            return back();
        }

        if ($request->plan_type == 'subscription_based'){
            $package = $this->subscriptionPackage->where('id',$request->selected_package_id)->ofStatus(1)->first();
            $vatPercentage      = (int)((business_config('subscription_vat', 'subscription_Setting'))->live_values ?? 0);
            if (!$package){
                Toastr::error(translate('Please Select valid plan'));
                return back();
            }

            $id                 = $package?->id;
            $price              = $package?->price;
            $name               = $package?->name;
        }

        $identityImages = [];
        if ($request->has('identity_images')) {
            foreach ($request->identity_images as $image) {
                $imageName = file_uploader('provider/identity/', 'png', $image);
                $identityImages[] = ['image'=>$imageName, 'storage'=> getDisk()];
            }
        }

        $provider = $this->provider;
        $provider->company_name = $request->company_name;
        $provider->company_phone = $request->company_phone;
        $provider->company_email = $request->company_email;
        $provider->logo = file_uploader('provider/logo/', 'png', $request->file('logo'));
        $provider->company_address = $request->company_address;

        $provider->contact_person_name = $request->contact_person_name;
        $provider->contact_person_phone = $request->contact_person_phone;
        $provider->contact_person_email = $request->contact_person_email;
        $provider->is_approved = 1;
        $provider->is_active = 1;
        $provider->zone_id = $request['zone_id'];
        $provider->coordinates = ['latitude' => $request['latitude'], 'longitude' => $request['longitude']];

        $owner = $this->owner;
        $owner->email = $request->company_email;
        $owner->phone = $request->company_phone;
        $owner->identification_number = $request->identity_number;
        $owner->identification_type = $request->identity_type;
        $owner->is_active = 1;
        $owner->identification_image = $identityImages;
        $owner->password = bcrypt($request->password);
        $owner->user_type = 'provider-admin';

        DB::transaction(function () use ($provider, $owner, $request) {
            $owner->save();
            $owner->zones()->sync($request->zone_id);
            $provider->user_id = $owner->id;
            $provider->save();
        });

        try {
            Mail::to(User::where('user_type', 'super-admin')->value('email'))->send(new NewJoiningRequestMail($provider));
        } catch (\Exception $exception) {
            info($exception);
        }

        if ($request->plan_type == 'subscription_based') {
            $provider_id = $provider?->id;
            if ($request->plan_price == 'received_money') {

                $payment = $this->paymentRequest;
                $payment->payment_amount = $price;
                $payment->success_hook = 'subscription_success';
                $payment->failure_hook = 'subscription_fail';
                $payment->payer_id = $provider->user_id;
                $payment->payment_method = 'manually';
                $payment->additional_data = json_encode($request->all());
                $payment->attribute = 'provider-reg';
                $payment->attribute_id = $provider_id;
                $payment->payment_platform = 'web';
                $payment->is_paid = 1;
                $payment->save();
                $request['payment_id'] = $payment->id;

                $result = $this->handlePurchasePackageSubscription($id, $provider_id, $request->all() , $price, $name);

                if (!$result) {
                    Toastr::error(translate('Something error'));
                    return back();
                }
            }
            if ($request->plan_price == 'free_trial') {
                $result = $this->handleFreeTrialPackageSubscription($id, $provider_id, $price, $name);
                if (!$result) {
                    Toastr::error(translate('Something error'));
                    return back();
                }
            }
        }

        Toastr::success(translate(DEFAULT_200['message']));
        return back();
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @param Request $request
     * @return Application|Factory|View|\Illuminate\Foundation\Application|RedirectResponse
     */
    public function details($id, Request $request): \Illuminate\Foundation\Application|View|Factory|RedirectResponse|Application
    {
        $this->authorize('provider_view');
        $request->validate([
            'web_page' => 'in:overview,subscribed_services,bookings,serviceman_list,settings,bank_information,reviews,subscription',
        ]);

        $webPage = $request->has('web_page') ? $request['web_page'] : 'overview';

        //overview
        if ($request->web_page == 'overview') {
            $provider = $this->provider->with('owner.account')->withCount(['bookings'])->find($id);
            $bookingOverview = DB::table('bookings')->where('provider_id', $id)
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

            return view('providermanagement::admin.provider.detail.overview', compact('provider', 'webPage', 'total'));

        } //subscribed_services
        elseif ($request->web_page == 'subscribed_services') {
            $search = $request->has('search') ? $request['search'] : '';
            $status = $request->has('status') ? $request['status'] : 'all';
            $queryParam = ['web_page' => $webPage, 'status' => $status, 'search' => $search];


            $subCategories = $this->subscribedService->where('provider_id', $id)
                ->with(['sub_category' => function ($query) {
                    return $query->withCount('services')->with(['services']);
                }])
                ->when($request->has('status') && $request['status'] != 'all', function ($query) use ($request) {
                    return $query->where('is_subscribed', (($request['status'] == 'subscribed') ? 1 : 0));
                })
                ->where(function ($query) use ($request) {
                    $keys = explode(' ', $request['search']);
                    foreach ($keys as $key) {
                        $query->orWhereHas('sub_category', function ($query) use ($key) {
                            $query->where('name', 'LIKE', '%' . $key . '%');
                        });
                    }
                })
                ->latest()->paginate(pagination_limit())->appends($queryParam);

            //$subscribed_services = $this->subscribedService->with(['sub_category'])->withCount(['services'])->where('provider_id', $id)->latest()->paginate(pagination_limit())->appends($queryParam);

            return view('providermanagement::admin.provider.detail.subscribed-services', compact('subCategories', 'webPage', 'status', 'search'));

        } //bookings
        elseif ($request->web_page == 'bookings') {

            $search = $request->has('search') ? $request['search'] : '';
            $queryParam = ['web_page' => $webPage, 'search' => $search];

            $bookings = $this->booking->where('provider_id', $id)
                ->with(['customer'])
                ->where(function ($query) use ($request) {
                    $keys = explode(' ', $request['search']);
                    foreach ($keys as $key) {
                        $query->where('readable_id', 'LIKE', '%' . $key . '%');
                    }
                })
                ->latest()
                ->paginate(pagination_limit())->appends($queryParam);

            return view('providermanagement::admin.provider.detail.bookings', compact('bookings', 'webPage', 'search'));

        } //serviceman_list
        elseif ($request->web_page == 'serviceman_list') {
            $queryParam = ['web_page' => $webPage];

            $servicemen = $this->serviceman
                ->with(['user'])
                ->where('provider_id', $id)
                ->latest()
                ->paginate(pagination_limit())->appends($queryParam);

            return view('providermanagement::admin.provider.detail.serviceman-list', compact('servicemen', 'webPage'));

        } //settings
        elseif ($request->web_page == 'settings') {
            $provider = $this->provider->find($id);
            return view('providermanagement::admin.provider.detail.settings', compact('webPage', 'provider'));

        } //bank_info
        elseif ($request->web_page == 'bank_information') {
            $provider = $this->provider->with('owner.account', 'bank_detail')->find($id);
            return view('providermanagement::admin.provider.detail.bank-information', compact('webPage', 'provider'));

        } //reviews
        elseif ($request->web_page == 'reviews') {

            $search = $request->has('search') ? $request['search'] : '';
            $queryParam = ['search' => $search, 'web_page' => $request['web_page']];

            $provider = $this->provider->with(['reviews'])->where('user_id', $request->user()->id)->first();

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
                ->whereHas('reviews', function ($query) use ($id) {
                    $query->where('provider_id', $id);
                })
                ->latest()
                ->paginate(pagination_limit())
                ->appends($queryParam);

            $provider = $this->provider->with('owner.account')->withCount(['bookings'])->find($id);

            $bookingOverview = DB::table('bookings')
                ->where('provider_id', $id)
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


            return view('providermanagement::admin.provider.detail.reviews', compact('webPage', 'provider', 'reviews', 'search', 'provider', 'total'));

        }//reviews
        elseif ($request->web_page == 'subscription') {

            $provider = $this->provider->where('id', $id)->first();
            $providerId = $provider->id;
            $subscriptionStatus = (int)((business_config('provider_subscription', 'provider_config'))->live_values);
            $commission = $provider->commission_status == 1 ? $provider->commission_percentage : (business_config('default_commission', 'business_information'))->live_values;
            $subscriptionDetails = $this->packageSubscriber->where('provider_id', $id)->first();

            if ($subscriptionDetails){
                $subscriptionPrice = $this->subscriptionPackage->where('id', $subscriptionDetails?->subscription_package_id)->value('price');
                $vatPercentage      = (int)((business_config('subscription_vat', 'subscription_Setting'))->live_values ?? 0);

                $start = Carbon::parse($subscriptionDetails?->package_start_date)->subDay() ?? '';
                $end = Carbon::parse($subscriptionDetails?->package_end_date)?? '';
                $daysDifference = $start->diffInDays($end, false);

                $bookingCheck = $subscriptionDetails?->limits->where('provider_id', $id)->where('key', 'booking')->first();
                $categoryCheck = $subscriptionDetails?->limits->where('provider_id', $id)->where('key', 'category')->first();
                $isBookingLimit = $bookingCheck?->is_limited;
                $isCategoryLimit = $categoryCheck?->is_limited;

                $totalBill = $subscriptionDetails?->logs->where('provider_id', $providerId)->sum('package_price') ?? 0.00;
                $totalPurchase = $subscriptionDetails?->logs->where('provider_id', $providerId)->count() ?? 0;
                $calculationVat = $subscriptionPrice * ($vatPercentage / 100);
                $renewalPrice = $subscriptionPrice + $calculationVat;

                return view('providermanagement::admin.provider.detail.subscription', compact('webPage', 'subscriptionDetails', 'daysDifference', 'bookingCheck', 'categoryCheck', 'isBookingLimit', 'isCategoryLimit', 'totalBill', 'totalPurchase', 'renewalPrice'));
            }

            return view('providermanagement::admin.provider.detail.subscription', compact('webPage','subscriptionDetails','commission', 'subscriptionStatus'));

        }
        return back();
    }


    /**
     * Show the form for editing the specified resource.
     * @param $id
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function updateAccountInfo($id, Request $request): RedirectResponse
    {
        $this->authorize('provider_update');

        $this->bank_detail::updateOrCreate(
            ['provider_id' => $id],
            [
                'bank_name' => $request->bank_name,
                'branch_name' => $request->branch_name,
                'acc_no' => $request->acc_no,
                'acc_holder_name' => $request->acc_holder_name,
            ]
        );

        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();
    }


    /**
     * Show the form for editing the specified resource.
     * @param $id
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function deleteAccountInfo($id, Request $request): JsonResponse
    {
        $this->authorize('provider_delete');

        $provider = $this->provider->with(['bank_detail'])->find($id);

        if (!$provider->bank_detail) {
            return response()->json(response_formatter(DEFAULT_404), 200);
        }
        $provider->bank_detail->delete();
        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }


    /**
     * Show the form for editing the specified resource.
     * @param string $id
     * @return JsonResponse
     */
    public function updateSubscription($id): JsonResponse
    {
        $subscribedService = $this->subscribedService->find($id);
        $this->subscribedService->where('id', $id)->update(['is_subscribed' => !$subscribedService->is_subscribed]);

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }


    /**
     * Show the form for editing the specified resource.
     * @param string $id
     * @return Application|Factory|View
     */
    public function edit(string $id): View|Factory|Application
    {
        $this->authorize('provider_update');

        $zones = $this->zone->ofStatus(1)->get();
        $provider = $this->provider->with(['owner', 'zone'])->find($id);
        $commission = (int)((business_config('provider_commision', 'provider_config'))->live_values ?? null);
        $subscription = (int)((business_config('provider_subscription', 'provider_config'))->live_values ?? null);
        $duration = (int)((business_config('free_trial_period', 'subscription_Setting'))->live_values ?? null);
        $freeTrialStatus = (int)((business_config('free_trial_period', 'subscription_Setting'))->is_active ?? 0);
        $subscriptionPackages = $this->subscriptionPackage->OfStatus(1)->with('subscriptionPackageFeature', 'subscriptionPackageLimit')->get();
        $formattedPackages = $subscriptionPackages->map(function ($subscriptionPackage) {
            return formatSubscriptionPackage($subscriptionPackage, PACKAGE_FEATURES);
        });
        $packageSubscription = $this->packageSubscriber->where('provider_id', $id)->first();
        return view('providermanagement::admin.provider.edit', compact('provider', 'zones', 'commission','subscription','formattedPackages', 'duration', 'freeTrialStatus', 'packageSubscription'));
    }


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param string $id
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $this->authorize('provider_update');

        $provider = $this->provider->with('owner')->find($id);

        Validator::make($request->all(), [
            'contact_person_name' => 'required',
            'contact_person_phone' => 'required',
            'contact_person_email' => 'required',

            'password' => !is_null($request->password) ? 'string|min:8' : '',
            'confirm_password' => !is_null($request->password) ? 'required|same:password' : '',

            'company_name' => 'required',
            'company_phone' => 'required',
            'company_address' => 'required',
            'company_email' => 'required|email',
            'logo' => 'image|mimes:jpeg,jpg,png,gif|max:10000',

            'identity_type' => 'required|in:passport,driving_license,nid,trade_license,company_id',
            'identity_number' => 'required',
            'identity_images' => 'array',
            'identity_images.*' => 'image|mimes:jpeg,jpg,png,gif',
            'latitude' => 'required',
            'longitude' => 'required',

            'zone_id' => 'required|uuid'
        ])->validate();

        if (User::where('email', $request['company_email'])->where('id', '!=', $provider->user_id)->exists()) {
            Toastr::error(translate('Email already taken'));
            return back();
        }

        if (User::where('phone', $request['company_phone'])->where('id', '!=', $provider->user_id)->exists()) {
            Toastr::error(translate('Phone already taken'));
            return back();
        }

        if ($request->plan_type == 'subscription_based'){
            $package = $this->subscriptionPackage->where('id',$request->selected_package_id)->ofStatus(1)->first();
            $vatPercentage      = (int)((business_config('subscription_vat', 'subscription_Setting'))->live_values ?? 0);
            if (!$package){
                Toastr::error(translate('Please Select valid plan'));
                return back();
            }

            $packageId          = $package?->id;
            $price              = $package?->price;
            $name               = $package?->name;
        }

        $identityImages = [];
        if (!is_null($request->identity_images)) {
            foreach ($request->identity_images as $image) {
                $imageName = file_uploader('provider/identity/', 'png', $image);
                $identityImages[] = ['image'=>$imageName, 'storage'=> getDisk()];
            }
        }

        $provider->company_name = $request->company_name;
        $provider->company_phone = $request->company_phone;
        $provider->company_email = $request->company_email;
        if ($request->has('logo')) {
            $provider->logo = file_uploader('provider/logo/', 'png', $request->file('logo'));
        }
        $provider->company_address = $request->company_address;
        $provider->contact_person_name = $request->contact_person_name;
        $provider->contact_person_phone = $request->contact_person_phone;
        $provider->contact_person_email = $request->contact_person_email;
        $provider->zone_id = $request['zone_id'];
        $provider->coordinates = ['latitude' => $request['latitude'], 'longitude' => $request['longitude']];

        $owner = $provider->owner()->first();
        $owner->identification_number = $request->identity_number;
        $owner->identification_type = $request->identity_type;
        if (count($identityImages) > 0) {
            $owner->identification_image = $identityImages;
        }
        if (!is_null($request->password)) {
            $owner->password = bcrypt($request->password);
        }
        $owner->user_type = 'provider-admin';

        if ($provider->is_approved == '2' || $provider->is_approved == '0') {
            $provider->is_approved = 1;
            $provider->is_active = 1;
            $owner->is_active = 1;
            try {
                Mail::to($provider?->owner?->email)->send(new RegistrationApprovedMail($provider));
            } catch (\Exception $exception) {
                info($exception);
            }
        }

        DB::transaction(function () use ($provider, $owner, $request) {
            $owner->save();
            $owner->zones()->sync($request->zone_id);
            $provider->save();
        });

        if ($request->plan_type == 'subscription_based') {
            $provider_id = optional($provider)->id;
            $result = true;

            $packageSubscription = $this->packageSubscriber->where('provider_id', $id)->first();

            if ($packageSubscription === null || $packageSubscription->subscription_package_id != $packageId) {

                if ($request->plan_price == 'received_money') {

                    $payment = $this->paymentRequest;
                    $payment->payment_amount = $price;
                    $payment->success_hook = 'subscription_success';
                    $payment->failure_hook = 'subscription_fail';
                    $payment->payer_id = $provider->user_id;
                    $payment->payment_method = 'manually';
                    $payment->additional_data = json_encode($request->all());
                    $payment->attribute = 'provider-reg';
                    $payment->attribute_id = $provider_id;
                    $payment->payment_platform = 'web';
                    $payment->is_paid = 1;
                    $payment->save();
                    $request['payment_id'] = $payment->id;

                    $result = $packageSubscription === null
                        ? $this->handlePurchasePackageSubscription($packageId, $provider_id, $request->all(), $price, $name)
                        : $this->handleShiftPackageSubscription($packageId, $provider_id, $request->all(), $price, $name);
                } elseif ($request->plan_price == 'free_trial') {
                    $result = $this->handleFreeTrialPackageSubscription($packageId, $provider_id, $price, $name);
                } else {
                    Toastr::error(translate('Invalid plan price'));
                    return back();
                }
            }

            if (!$result) {
                Toastr::error(translate('Something went wrong'));
                return back();
            }
        }

        if ($request->plan_type == 'commission_based'){
            $this->packageSubscriber->where('provider_id', $id)->delete();
        }


        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function destroy(Request $request, $id): RedirectResponse
    {
        $this->authorize('provider_delete');

        Validator::make($request->all(), [
            'provider_id' => 'required'
        ]);

        $providers = $this->provider->where('id', $id);
        if ($providers->count() > 0) {
            foreach ($providers->get() as $provider) {
                file_remover('provider/logo/', $provider->logo);
                if (!empty($provider->owner->identification_image)) {
                    foreach ($provider->owner->identification_image as $image) {
                        file_remover('provider/identity/', $image);
                    }
                }

                $provider->servicemen->each(function ($serviceman) {
                    $serviceman->user->update(['is_active' => 0]);
                });

                $provider->owner()->delete();
            }
            $providers->delete();
            Toastr::success(translate(DEFAULT_DELETE_200['message']));
            return back();
        }

        Toastr::error(translate(DEFAULT_FAIL_200['message']));
        return back();
    }

    /**
     * Remove the specified resource from storage.
     * @param $id
     * @return JsonResponse
     */
    public function statusUpdate($id): JsonResponse
    {
        $this->authorize('provider_manage_status');

        $provider = $this->provider->where('id', $id)->first();
        $this->provider->where('id', $id)->update(['is_active' => !$provider->is_active]);
        $owner = $this->owner->where('id', $provider->user_id)->first();
        $owner->is_active = !$provider->is_active;
        $owner->save();

        if ($owner?->is_active == 1) {
            try {
                Mail::to($provider?->owner?->email)->send(new AccountUnsuspendMail($provider));
            } catch (\Exception $exception) {
                info($exception);
            }
        } else {
            try {
                Mail::to($provider?->owner?->email)->send(new AccountSuspendMail($provider));
            } catch (\Exception $exception) {
                info($exception);
            }
        }

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }
}
