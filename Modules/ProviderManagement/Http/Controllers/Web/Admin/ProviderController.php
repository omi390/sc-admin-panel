<?php

namespace Modules\ProviderManagement\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\BookingModule\Entities\Booking;
use Modules\BusinessSettingsModule\Entities\PackageSubscriber;
use Modules\BusinessSettingsModule\Entities\PackageSubscriberFeature;
use Modules\BusinessSettingsModule\Entities\PackageSubscriberLimit;
use Modules\BusinessSettingsModule\Entities\SubscriptionPackage;
use Modules\PaymentModule\Entities\PaymentRequest;
use Modules\PaymentModule\Traits\SubscriptionTrait;
use Modules\ProviderManagement\Emails\AccountSuspendMail;
use Modules\ProviderManagement\Emails\AccountUnsuspendMail;
use Modules\ProviderManagement\Emails\NewJoiningRequestMail;
use Modules\ProviderManagement\Emails\RegistrationApprovedMail;
use Modules\TransactionModule\Entities\Account;

use Modules\ProviderManagement\Emails\RegistrationDeniedMail;
use Modules\ProviderManagement\Entities\BankDetail;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ProviderManagement\Entities\SubscribedService;
use Modules\ReviewModule\Entities\Review;
use Modules\CategoryManagement\Entities\Category;
use Modules\ServiceManagement\Entities\Service;
use Modules\TransactionModule\Entities\Transaction;
use Modules\UserManagement\Entities\Serviceman;
use Modules\UserManagement\Entities\User;
use Modules\ZoneManagement\Entities\Zone;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProviderController extends Controller
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
    protected Category $category;

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
        PaymentRequest $paymentRequest,
        Category $category
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
        $this->category = $category;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     * @throws AuthorizationException
     */
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
        return view('providermanagement::admin.provider.index', compact('providers', 'topCards', 'search', 'status'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     * @throws AuthorizationException
     */
    public function create(): Renderable
    {
        $this->authorize('provider_add');
        $zones = $this->zone->get();
        $commission = (int)((business_config('provider_commision', 'provider_config'))->live_values ?? null);
        $subscription = (int)((business_config('provider_subscription', 'provider_config'))->live_values ?? null);
        $duration = (int)((business_config('free_trial_period', 'subscription_Setting'))->live_values ?? null);
        $freeTrialStatus = (int)((business_config('free_trial_period', 'subscription_Setting'))->is_active ?? 0);
        $subscriptionPackages = $this->subscriptionPackage->OfStatus(1)->with('subscriptionPackageFeature', 'subscriptionPackageLimit')->get();
        $formattedPackages = $subscriptionPackages->map(function ($subscriptionPackage) {
            return formatSubscriptionPackage($subscriptionPackage, PACKAGE_FEATURES);
        });
        return view('providermanagement::admin.provider.create', compact('zones','commission','subscription','formattedPackages', 'duration', 'freeTrialStatus'));
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
           

$account = Account::where('user_id', $provider->user_id)->first();
    if (!$account) {
        Account::create([
            'user_id' => $provider->user_id,
            'balance_pending' => 0.00,
            'received_balance' => 0.00,
            'account_payable' => 0.00,
            'account_receivable' => 0.00,
            'total_withdrawn' => 0.00,
            'total_expense' => 0.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    $provider = $this->provider->with('owner.account')->withCount(['bookings'])->find($id);
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
     * Get available subcategories for a provider (not yet subscribed)
     * @param string $id
     * @return JsonResponse
     */
    public function getAvailableSubCategories(string $id): JsonResponse
    {
        $this->authorize('provider_view');
        
        // Get all subscribed subcategory IDs for this provider
        $subscribedSubCategoryIds = $this->subscribedService
            ->where('provider_id', $id)
            ->pluck('sub_category_id')
            ->toArray();

        // Get all active subcategories that are not yet subscribed
        $availableSubCategories = $this->category
            ->ofType('sub')
            ->ofStatus(1)
            ->with(['parent:id,name'])
            ->whereNotIn('id', $subscribedSubCategoryIds)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'parent_id']);

        return response()->json(response_formatter(DEFAULT_200, $availableSubCategories), 200);
    }

    /**
     * Add subscribed subcategories for a provider
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function addSubscribedSubCategories(Request $request, string $id): JsonResponse
    {
        $this->authorize('provider_manage_status');
        
        $validator = Validator::make($request->all(), [
            'sub_category_ids' => 'required|array|min:1',
            'sub_category_ids.*' => 'required|uuid|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $provider = $this->provider->find($id);
        if (!$provider) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        $addedCount = 0;
        foreach ($request->sub_category_ids as $subCategoryId) {
            // Check if already exists
            $existing = $this->subscribedService
                ->where('provider_id', $id)
                ->where('sub_category_id', $subCategoryId)
                ->first();

            if (!$existing) {
                // Get parent category
                $subCategory = $this->category->find($subCategoryId);
                $parentCategoryId = $subCategory ? $subCategory->parent_id : null;

                // Create new subscribed service
                $this->subscribedService->create([
                    'provider_id' => $id,
                    'category_id' => $parentCategoryId,
                    'sub_category_id' => $subCategoryId,
                    'is_subscribed' => 1,
                ]);
                $addedCount++;
            } else {
                // If exists but not subscribed, update it
                if ($existing->is_subscribed == 0) {
                    $existing->update(['is_subscribed' => 1]);
                    $addedCount++;
                }
            }
        }

        if ($addedCount > 0) {
            return response()->json(response_formatter(DEFAULT_STORE_200, ['added_count' => $addedCount]), 200);
        }

        return response()->json(response_formatter(DEFAULT_200, ['message' => translate('All_selected_sub_categories_are_already_subscribed')]), 200);
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

    /**
     * Remove the specified resource from storage.
     * @param $id
     * @return JsonResponse
     */
    public function serviceAvailability($id): JsonResponse
    {
        $this->authorize('provider_manage_status');

        $provider = $this->provider->where('id', $id)->first();
        $this->provider->where('id', $id)->update(['service_availability' => !$provider->service_availability]);
        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    /**
     * Remove the specified resource from storage.
     * @param $id
     * @return JsonResponse
     */
    public function suspendUpdate($id): JsonResponse
    {
        $this->authorize('provider_manage_status');

        $provider = $this->provider->where('id', $id)->first();
        $this->provider->where('id', $id)->update(['is_suspended' => !$provider->is_suspended]);
        $provider_info = $this->provider->where('id', $id)->first();

        if ($provider_info?->is_suspended == '1') {
            $provider = $provider_info?->owner;
            $title = get_push_notification_message('provider_suspend', 'provider_notification', $provider?->current_language_key);
            if ($provider?->fcm_token && $title) {
                device_notification($provider?->fcm_token, $title, null, null, $provider_info->id, 'suspend');
            }
            try {
                Mail::to($provider?->owner?->email)->send(new AccountSuspendMail($provider));
            } catch (\Exception $exception) {
                info($exception);
            }
        } else {
            $provider = $provider_info?->owner;
            $title = get_push_notification_message('provider_suspension_remove', 'provider_notification', $provider?->current_language_key);
            if ($provider?->fcm_token && $title) {
                device_notification($provider?->fcm_token, $title, null, null, $provider_info->id, 'suspend');
            }
            try {
                Mail::to($provider?->owner?->email)->send(new AccountUnsuspendMail($provider));
            } catch (\Exception $exception) {
                info($exception);
            }
        }

        return response()->json(response_formatter(DEFAULT_SUSPEND_UPDATE_200), 200);
    }

    /**
     * Remove the specified resource from storage.
     * @param $id
     * @param Request $request
     * @return RedirectResponse
     */
    public function commissionUpdate($id, Request $request): RedirectResponse
    {
        $this->authorize('provider_manage_status');

        $provider = $this->provider->where('id', $id)->first();
        $provider->commission_status = $request->commission_status == 'default' ? 0 : 1;
        if ($request->commission_status == 'custom') {
            $provider->commission_percentage = $request->custom_commission_value;
        }
        $provider->save();

        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();
    }

    public function onboardingRequest(Request $request): Factory|View|Application
    {

        $this->authorize('onboarding_request_view');

        $status = $request->status == 'denied' ? 'denied' : 'onboarding';
        $search = $request['search'];
        $queryParam = ['status' => $status, 'search' => $request['search']];

        $providers = $this->provider->with(['owner', 'zone'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('company_name', 'LIKE', '%' . $key . '%')
                        ->orWhere('contact_person_name', 'LIKE', '%' . $key . '%')
                        ->orWhere('contact_person_phone', 'LIKE', '%' . $key . '%')
                        ->orWhere('contact_person_email', 'LIKE', '%' . $key . '%');
                }
            })
            ->ofApproval($status == 'onboarding' ? 2 : 0)
            ->latest()
            ->paginate(pagination_limit())
            ->appends($queryParam);

        $providersCount = [
            'onboarding' => $this->provider->ofApproval(2)->get()->count(),
            'denied' => $this->provider->ofApproval(0)->get()->count(),
        ];

        return View('providermanagement::admin.provider.onboarding', compact('providers', 'search', 'status', 'providersCount'));
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @param Request $request
     * @return View|\Illuminate\Foundation\Application|Factory|Application
     */
    public function onboardingDetails($id, Request $request): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $this->authorize('onboarding_request_view');
        $provider = $this->provider->with('owner.account')->withCount(['bookings'])->find($id);
        return view('providermanagement::admin.provider.detail.onboarding-details', compact('provider'));
    }

    public function updateApproval($id, $status, Request $request): JsonResponse
    {
        $this->authorize('onboarding_request_manage_status');

        if ($status == 'approve') {
            $this->provider->where('id', $id)->update(['is_active' => 1, 'is_approved' => 1]);
            $provider = $this->provider->with('owner')->where('id', $id)->first();
            $provider->owner->is_active = 1;
            $provider->owner->save();

            $approval  = isNotificationActive(null, 'registration', 'email', 'provider');
            if ($approval) {
                try {
                    Mail::to($provider?->owner?->email)->send(new RegistrationApprovedMail($provider));
                } catch (\Exception $exception) {
                    info($exception);
                }
            }

        } elseif ($status == 'deny') {
            $this->provider->where('id', $id)->update(['is_active' => 0, 'is_approved' => 0]);
            $provider = $this->provider->with('owner')->where('id', $id)->first();
            $provider->owner->is_active = 0;
            $provider->owner->save();
            $deny  = isNotificationActive(null, 'registration', 'email', 'provider');
            if ($deny) {
                try {
                    Mail::to($provider?->owner?->email)->send(new RegistrationDeniedMail($provider));
                } catch (\Exception $exception) {
                    info($exception);
                }
            }

        } else {
            return response()->json(response_formatter(DEFAULT_400), 200);
        }

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return string|StreamedResponse
     */
    public function download(Request $request): string|StreamedResponse
    {
        $this->authorize('provider_delete');

        $items = $this->provider->with(['owner', 'zone'])->where(['is_approved' => 1])->withCount(['subscribed_services', 'bookings'])
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
            ->latest()->get();

        return (new FastExcel($items))->download(time() . '-file.xlsx');
    }

    public function reviewsDownload(Request $request)
    {
        $items = $this->review->with(['booking'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('readable_id', 'LIKE', '%' . $key . '%');
                }
            })
            ->where('provider_id', $request->provider_id)
            ->latest()
            ->get();
        return (new FastExcel($items))->download(time() . '-file.xlsx');
    }

    public function availableProviderList(Request $request): JsonResponse
    {
        $sortBy = $request->sort_by ?? 'default';
        $search = $request->search;
        $sortBy = $request->sort_by;
        $booking = $this->booking->where('id', $request->booking_id)->first();

        $providers = $this->provider
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
            ->when($sortBy === 'top-rated', function ($query) {
                return $query->orderBy('avg_rating', 'desc');
            })
            ->when($sortBy === 'bookings-completed', function ($query) {
                $query->withCount(['bookings' => function ($query) {
                    $query->where('booking_status', 'completed');
                }]);
                $query->orderBy('bookings_count', 'desc');
            })
            ->when($sortBy !== 'bookings-completed', function ($query) {
                return $query->withCount('bookings');
            })
            ->whereHas('subscribed_services', function ($query) use ($request, $booking) {
                $query->where('sub_category_id', $booking->sub_category_id)->where('is_subscribed', 1);
            })
            ->when(business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values, function ($query) {
                $query->where('is_suspended', 0);
            })
            ->where('service_availability', 1)
            ->withCount('reviews')
            ->ofApproval(1)->ofStatus(1)->get();

        $booking = $this->booking->with(['detail.service' => function ($query) {
            $query->withTrashed();
        }, 'detail.service.category', 'detail.service.subCategory', 'detail.variation', 'customer', 'provider', 'service_address', 'serviceman', 'service_address', 'status_histories.user'])->find($request->booking_id);

        return response()->json([
            'view' => view('providermanagement::admin.partials.details.provider-info-modal-data', compact('providers', 'booking', 'search', 'sortBy'))->render()
        ]);
    }

    public function providerInfo(Request $request): JsonResponse
    {
        $booking = $this->booking->where('id', $request->booking_id)->first();

        return response()->json([
            'view' => view('providermanagement::admin.partials.details._provider-data', compact('booking'))->render(),
            'serviceman_view' => view('providermanagement::admin.partials.details._serviceman-data', compact('booking'))->render(),
        ]);
    }

    public function reassignProvider(Request $request): JsonResponse
    {

        $booking = $this->booking->where('id', $request->booking_id)->first();

        if (isset($booking)) {
            $booking->provider_id = $request->provider_id;
            $booking->serviceman_id = null;
            $booking->save();

            $fcmToken = $this->provider->with('owner')->whereId($booking->provider_id)->first()->owner->fcm_token ?? null;
            $languageKey = $this->provider->with('owner')->whereId($booking->provider_id)->first()->owner->current_language_key;
            $bookingNotificationStatus = business_config('booking', 'notification_settings')->live_values;

            if (!is_null($fcmToken) && isset($bookingNotificationStatus) && $bookingNotificationStatus['push_notification_booking']) {
                $reassign  = isNotificationActive(null, 'booking', 'notification', 'user');
                $title = get_push_notification_message('booking_accepted', 'provider_notification', $languageKey);
                device_notification($fcmToken, $title, null, null, $booking->id, 'booking');
            }

            $sortBy = $request->sort_by ?? 'default';
            $search = $request->search;
            $sortBy = $request->sort_by;
            $providers = $this->provider
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
                ->when($sortBy === 'top-rated', function ($query) {
                    return $query->orderBy('avg_rating', 'desc');
                })
                ->when($sortBy === 'bookings-completed', function ($query) {
                    $query->withCount(['bookings' => function ($query) {
                        $query->where('booking_status', 'completed');
                    }]);
                    $query->orderBy('bookings_count', 'desc');
                })
                ->whereHas('subscribed_services', function ($query) use ($request, $booking) {
                    $query->where('sub_category_id', $booking->sub_category_id)->where('is_subscribed', 1);
                })
                ->when($sortBy !== 'bookings-completed', function ($query) {
                    return $query->withCount('bookings');
                })
                ->when(business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values, function ($query) {
                    $query->where('is_suspended', 0);
                })
                ->where('service_availability', 1)
                ->withCount('reviews')
                ->ofApproval(1)->ofStatus(1)->get();

            return response()->json([
                'view' => view('providermanagement::admin.partials.details.provider-info-modal-data', compact('providers', 'booking', 'search', 'sortBy'))->render()
            ]);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }

    public function getProviderInfo($providerId): JsonResponse
    {
        $provider = $this->provider->with('reviews')->findOrFail($providerId);
        $reviews = DB::table('reviews')->where('provider_id', $provider->id)->count();
        return response()->json(['reviews' => $reviews, 'rating' => $provider->avg_rating]);
    }
    
    public function chargeAmount(Request $request)
    {
        $userId = $request['user_id'];
        $amountToAdd = $request['amount'];
    
        // Get current account_payable
        $account = DB::table('accounts')->where('user_id', $userId)->first();
    
        if (!$account) {
            return redirect()->back()->with('error','Account not found');
        }
    
        $newAmount = $account->account_payable + $amountToAdd;
    
        // Update the account_payable
        DB::table('accounts')->where('user_id', $userId)->update([
            'account_payable' => $newAmount,
            'updated_at' => now() // optional
        ]);

        return redirect()->back()->with('success','User Charged Successfully');
     }

}
