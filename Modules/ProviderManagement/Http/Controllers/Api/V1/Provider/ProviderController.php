<?php

namespace Modules\ProviderManagement\Http\Controllers\Api\V1\Provider;

use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Modules\BidModule\Entities\IgnoredPost;
use Modules\BidModule\Entities\Post;
use Modules\BookingModule\Entities\Booking;
use Modules\PromotionManagement\Entities\Advertisement;
use Modules\PromotionManagement\Entities\PushNotification;
use Modules\ProviderManagement\Entities\BankDetail;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ProviderManagement\Entities\SubscribedService;
use Modules\ReviewModule\Entities\Review;
use Modules\SMSModule\Lib\SMS_gateway;
use Modules\TransactionModule\Entities\Account;
use Modules\TransactionModule\Entities\Transaction;
use Modules\UserManagement\Entities\Serviceman;
use Modules\UserManagement\Entities\User;
use Modules\PaymentModule\Traits\SmsGateway;
use Modules\ProviderManagement\Entities\TendorAppl;


class ProviderController extends Controller
{
    private $bankDetail, $provider, $account, $user, $pushNotification, $serviceman, $ignoredPost;

    protected $post;
    private $google_map;
    private $subscribedService;
    private Booking $booking;
    private Review $review;
    private Advertisement $advertisement;


    protected Transaction $transaction;


    public function __construct(Transaction $transaction, SubscribedService $subscribedService, BankDetail $bankDetail, Provider $provider, Account $account, User $user, PushNotification $pushNotification, Serviceman $serviceman, Booking $booking, Review $review, Post $post, IgnoredPost $ignoredPost, Advertisement $advertisement,)
    {
        $this->bankDetail = $bankDetail;
        $this->provider = $provider;
        $this->user = $user;
        $this->account = $account;
        $this->pushNotification = $pushNotification;
        $this->serviceman = $serviceman;
        $this->subscribedService = $subscribedService;
        $this->google_map = business_config('google_map', 'third_party');
        $this->booking = $booking;
        $this->review = $review;
        $this->transaction = $transaction;
        $this->post = $post;
        $this->ignoredPost = $ignoredPost;
        $this->advertisement = $advertisement;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @param Transaction $transaction
     * @param SubscribedService $subscribedService
     * @param Serviceman $serviceman
     * @return JsonResponse
     */
    public function dashboard(Request $request, Transaction $transaction, SubscribedService $subscribedService, Serviceman $serviceman): JsonResponse
    {
        $request['sections'] = explode(',', $request['sections']);


        $validator = Validator::make($request->all(), [
            'sections' => 'required|array',
            'sections.*' => 'in:top_cards,earning_stats,booking_stats,recent_bookings,my_subscriptions,serviceman_list,customized_post,additional_info_count,banners',
            'year' => 'integer|min:2000|max:' . (date('Y') + 1),
            'month' => 'integer|min:1|max:12',
            'stats_type' => 'in:full_year,full_month'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $data = [];
        $banners = [
            'https://mediamodifier.com/blog/wp-content/uploads/2020/03/free-banner-maker-online-design-templates.jpg'
            ];
            
                    if (in_array('banners', $request['sections'])) {
                        $data[] = ['banners' => $banners];
                    }
     
     
     \Log::info($request->all());


        $maxBookingAmount = (business_config('max_booking_amount', 'booking_setup'))->live_values;

        if (in_array('top_cards', $request['sections'])) {
            $account = $this->account->where('user_id', $request->user()->id)->first();
            \Log::info("account-");
            \Log::info($account);
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
            \Log::info("prov");
                 \Log::info($request->user()->provider->id);
        }

        if (in_array('earning_stats', $request['sections'])) {
            $allTransactions = $transaction->where(['to_user_id' => $request->user()->id])->where('credit', '>', 0)
                ->whereIn('to_user_account', ['received_balance', 'total_withdrawn'])
                ->when($request->has('stats_type') && $request['stats_type'] == 'full_year', function ($query) use ($request) {
                    return $query->whereYear('created_at', '=', $request['year'])->select(
                        DB::raw('IFNULL(sum(credit),0) as sums'),
                        DB::raw('YEAR(created_at) year, MONTHNAME(created_at) month')
                    )->groupby('year', 'month');
                })->when($request->has('stats_type') && $request['stats_type'] == 'full_month', function ($query) use ($request) {
                    return $query->whereYear('created_at', '=', $request['year'])->whereMonth('created_at', '=', $request['month'])->select(
                        DB::raw('IFNULL(sum(credit),0) as sums'),
                        DB::raw('YEAR(created_at) year, MONTHNAME(created_at) month, DAY(created_at) day')
                    )->groupby('year', 'month', 'day');
                })->get()->toArray();

            $data[] = ['earning_stats' => $allTransactions];
        }

        if (in_array('booking_stats', $request['sections'])) {
            $bookingOverview = DB::table('bookings')->where('provider_id', $request->user()->provider->id)
                ->select('booking_status', DB::raw('count(*) as total'))
                ->groupBy('booking_status')
                ->get();
            $totalBookings = $this->booking->where('provider_id', $request->user()->provider->id)->count();
            $data[] = ['booking_stats' => $bookingOverview, 'total_bookings' => $totalBookings];
        }

        if (in_array('recent_bookings', $request['sections'])) {
            $subscribedSubCategories = $this->subscribedService
                ->where(['provider_id' => $request->user()->provider->id])
                ->where(['is_subscribed' => 1])->pluck('sub_category_id')->toArray();

            $recentBookings = $this->booking->with(['detail.service' => function ($query) {
                $query->select('id', 'name', 'thumbnail');

            }])->where('booking_status', 'pending')
                ->whereIn('sub_category_id', $subscribedSubCategories)
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
                ->latest()->take(5)->get();
            $recentNotBooking = [];
            $recentBookings = $request->user()?->provider?->is_suspended == 0 || !business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values ? $recentBookings : $recentNotBooking;
            $data[] = ['recent_bookings' => $recentBookings];
        }

        if (in_array('my_subscriptions', $request['sections'])) {
            $subscriptions = $subscribedService
                ->whereHas('category', function ($query) {
                    $query->where('is_active', 1);
                })->whereHas('sub_category', function ($query) {
                    $query->where('is_active', 1);
                })
                ->ofStatus(1)
                ->with(['sub_category'])
                ->withCount(['services', 'completed_booking'])
                ->where(['provider_id' => $request->user()->provider->id])->take(5)->get();
            $data[] = ['subscriptions' => $subscriptions];
        }

        if (in_array('serviceman_list', $request['sections'])) {
            $servicemanList = $this->serviceman->with(['user'])->whereHas('user', function ($query) {
                $query->ofStatus(1);
            })
                ->where(['provider_id' => $request->user()->provider->id])
                ->latest()
                ->take(5)->get();

            $data[] = ['serviceman_list' => $servicemanList];
        }

        if (in_array('customized_post', $request['sections'])) {


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

            $data[] = ['customized_post' => $posts];
        }

        if (in_array('additional_info_count', $request['sections'])) {

            $ignoredPosts = $this->ignoredPost->where('provider_id', $request->user()->provider->id)->pluck('post_id')->toArray();
            $subCategories = $this->subscribedService
                ->where(['provider_id' => $request->user()->provider->id])
                ->where(['is_subscribed' => 1])->pluck('sub_category_id')->toArray();

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

            $advertisementCount = $this->advertisement->with(['attachments'])
                ->where('provider_id', auth('api')->user()->provider->id)
                ->count();

            $pendingBookingCount = $this->booking->where('booking_status', 'pending')
                ->whereIn('sub_category_id', $subscribedSubCategories)
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

            $recentNotBooking = [];
            $pendingBookingCount = $request->user()?->provider?->is_suspended == 0 || !business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values ? $pendingBookingCount : $recentNotBooking;

            $data[] = ['additional_info_count' =>
                [
                    'customized_post_count' => $postCount,
                    'advertisement_count' => $advertisementCount,
                    'pending_booking_count' => $pendingBookingCount
                ]];
        }

        return response()->json(response_formatter(DEFAULT_200, $data), 200);
    }
    
    public function tendors(){
     
        $userId = auth()->id();
        $tendors = DB::table('tendors')->orderBy('id','desc')->get();
        $tendors = DB::table('tendors')
            ->leftJoin('tendor_applicants', function($join) use ($userId) {
                $join->on('tendors.id', '=', 'tendor_applicants.tendor_id')
                     ->where('tendor_applicants.user_id', '=', $userId);
            })
            ->select('tendors.*', DB::raw('IF(tendor_applicants.id IS NULL, false, true) as applied'))
            ->orderBy('tendors.id', 'desc')
            ->get();
        
        return response()->json(response_formatter(DEFAULT_200, $tendors), 200);
    }
    
     public function saveTendor(Request $request){
     
        $newTendor = new TendorAppl();
        $newTendor->name = auth()->user()->first_name.' '.auth()->user()->last_name;
        $newTendor->mobile =auth()->user()->phone;
        $newTendor->address = auth()->user()->email;
        $newTendor->user_id = auth()->user()->id;
        $newTendor->tendor_id = $request['tendor_id'];
        $newTendor->save();


        return response()->json("Applied To The Tendor", 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $provider = $this->provider->where(['user_id' => auth('api')->user()->id])->with(['owner', 'zone'])->first();
        if (in_array($request->user()->user_type, PROVIDER_USER_TYPES)) {
            return response()->json(response_formatter(DEFAULT_200, $provider), 200);
        }
        return response()->json(response_formatter(DEFAULT_403), 401);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function getBankDetails(Request $request): JsonResponse
    {
        $bankDetails = $this->bankDetail->where('provider_id', $request->user()->provider->id)->first();

        return response()->json(response_formatter(DEFAULT_200, $bankDetails), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteProvider(Request $request): JsonResponse
    {
        $provider = $this->provider::where('user_id', $request->user()->id)->first();
        if ($provider) {

            // Disable is_active for associated servicemen users
            $provider->servicemen->each(function ($serviceman) {
                $servicemanUser = $serviceman->user;
                $servicemanUser->is_active = 0;
                $servicemanUser->save();
            });

            $provider->delete();
            $provider->owner->delete();
            return response()->json(response_formatter(DEFAULT_DELETE_200), 200);
        }
        return response()->json(response_formatter(DEFAULT_404), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateBankDetails(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'bank_name' => 'required',
            'branch_name' => 'required',
            'acc_no' => 'required',
            'acc_holder_name' => 'required',
            'routing_number' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $this->bankDetail->updateOrCreate(
            [
                'provider_id' => $request->user()->provider->id,
            ],
            [
                'provider_id' => $request->user()->provider->id,
                'bank_name' => $request->bank_name,
                'branch_name' => $request->branch_name,
                'acc_no' => $request->acc_no,
                'acc_holder_name' => $request->acc_holder_name,
                'routing_number' => $request->routing_number
            ],
        );

        return response()->json(response_formatter(DEFAULT_STORE_200), 200);
    }

    /**
     * Modify provider information
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'contact_person_name' => 'required',
            'contact_person_phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
            'contact_person_email' => 'required',
            'zone_ids' => 'required|array',
            'zone_ids.*' => 'uuid',

            'password' => '',
            'confirm_password' => isset($request->password) ? 'required|same:password' : '',

            'company_name' => 'required',
            'company_phone' => 'required|unique:providers,id,' . auth()->user()->provider->id,
            'company_address' => 'required',
            'logo' => 'image|mimes:jpeg,jpg,png,gif|max:10000',

            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $provider = $this->provider::where('user_id', $request->user()->id)->first();
        $provider->company_name = $request->company_name;
        $provider->company_phone = $request->company_phone;
        if ($request->has('logo')) {
            $provider->logo = file_uploader('provider/logo/', 'png', $request->file('logo'), $provider->logo);
        }
        $provider->company_address = $request->company_address;

        $provider->contact_person_name = $request->contact_person_name;
        $provider->contact_person_phone = $request->contact_person_phone;
        $provider->contact_person_email = $request->contact_person_email;
        $provider->zone_id = $request['zone_ids'][0];
        $provider->coordinates = ['latitude' => $request['latitude'], 'longitude' => $request['longitude']];

        $owner = $this->user->where('id', $request->user()->id)->first();
        if ($request->has('password')) {
            $owner->password = bcrypt($request->password);
        }

        DB::transaction(function () use ($provider, $owner) {
            $owner->save();
            $provider->save();
        });

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }


    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone_or_email' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        DB::table('password_resets')->where('phone', $request['phone_or_email'])->delete();
        $customer = $this->user->whereIn('user_type', PROVIDER_USER_TYPES)
            ->where(['phone' => $request['phone_or_email']])
            ->first();

        if (isset($customer)) {
            $token = env('APP_ENV') != 'live' ? '1234' : rand(1000, 9999);

            DB::table('password_resets')->insert([
                'phone' => $customer['phone'],
                'email' => $customer['email'],
                'token' => $token,
                'created_at' => now(),
                'expires_at' => now()->addMinutes(3),
            ]);

            $method = business_config('forget_password_verification_method', 'business_information')?->live_values;
            if ($method == 'phone') {
                $publishedStatus = 0;
                $paymentPublishedStatus = config('get_payment_publish_status');
                if (isset($paymentPublishedStatus[0]['is_published'])) {
                    $publishedStatus = $paymentPublishedStatus[0]['is_published'];
                }

                if ($publishedStatus == 1) {
                    $response = SmsGateway::send($customer->phone, $token);
                } else {
                    SMS_gateway::send($customer->phone, $token);
                }

            } elseif ($method == 'email') {
                //mail will be sent
                try {
                    Mail::to($customer['email'])->send(new \App\Mail\PasswordResetMail($token));
                } catch (\Exception $exception) {
                }
            }

        } else {
            return response()->json(response_formatter(DEFAULT_404), 200);
        }

        return response()->json(response_formatter(DEFAULT_SENT_OTP_200), 200);
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function otpVerification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone_or_email' => 'required',
            'otp' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $data = DB::table('password_resets')
            ->where('phone', $request['phone_or_email'])
            ->where(['token' => $request['otp']])->first();

        if (isset($data)) {
            return response()->json(response_formatter(DEFAULT_VERIFIED_200), 200);
        }

        return response()->json(response_formatter(DEFAULT_404), 200);
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone_or_email' => 'required',
            'otp' => 'required',
            'password' => 'required',
            'confirm_password' => 'required|same:confirm_password'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $data = DB::table('password_resets')
            ->where('phone', $request['phone_or_email'])
            ->where(['token' => $request['otp']])
            ->where('expires_at', '>', now())
            ->first();

        if (isset($data)) {
            $this->user->whereIn('user_type', PROVIDER_USER_TYPES)
                ->where('phone', $request['phone_or_email'])
                ->update([
                    'password' => bcrypt(str_replace(' ', '', $request['password']))
                ]);
            DB::table('password_resets')
                ->where('phone', $request['phone_or_email'])
                ->where(['token' => $request['otp']])->delete();

        } else {
            return response()->json(response_formatter(DEFAULT_404), 200);
        }

        return response()->json(response_formatter(DEFAULT_PASSWORD_RESET_200), 200);
    }


    /**
     * Modify provider information
     * @param Request $request
     * @return JsonResponse
     */
    public function updateFcmToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $customer = $this->user::find($request->user()->id);
        $customer->fcm_token = $request->fcm_token;
        $customer->save();

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function notifications(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $pushNotification = $this->pushNotification->ofStatus(1)->whereJsonContains('to_users', 'provider-admin')
            ->whereJsonContains('zone_ids', $request->user()->provider->zone_id)
            ->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        return response()->json(response_formatter(DEFAULT_200, $pushNotification), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function subscribedSubCategories(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $subscribed = $this->subscribedService->where('provider_id', $request->user()->provider->id)
            ->with(['sub_category' => function ($query) {
                return $query->withCount('services')->with(['services']);
            }])->whereHas('category', function ($query) {
                $query->where('is_active', 1);
            })->whereHas('sub_category', function ($query) {
                $query->where('is_active', 1);
            })
            ->ofStatus(1)
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        return response()->json(response_formatter(DEFAULT_200, $subscribed), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @param string $service_id
     * @return JsonResponse
     */
    public function review(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $reviews = $this->review->with(['booking.detail', 'provider', 'customer','reviewReply','service'])
            ->where('provider_id', $request->user()->provider->id)
            ->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        $ratingGroupCount = DB::table('reviews')
            ->where('provider_id', $request->user()->provider->id)
            ->select('review_rating', DB::raw('count(review_comment) as total_comment'), DB::raw('count(*) as total'))
            ->groupBy('review_rating')
            ->get();

        $activeReviews = DB::table('reviews')
            ->where('provider_id', $request->user()->provider->id)
            ->where('is_active', 1)
            ->select('review_rating', DB::raw('count(*) as total'))
            ->groupBy('review_rating')
            ->get();

        $totalRating = 0;
        $ratingCount = 0;
        $reviewCount = 0;

        foreach ($ratingGroupCount as $count) {
            $ratingCount += $count->total;
            $reviewCount += $count->total_comment;
        }

        $totalActiveRating = 0;
        $activeRatingCount = 0;

        foreach ($activeReviews as $activeReview) {
            $totalActiveRating += round($activeReview->review_rating * $activeReview->total, 2);
            $activeRatingCount += $activeReview->total;
        }

        $ratingInfo = [
            'rating_count' => $ratingCount,
            'review_count' => $reviewCount,
            'average_rating' => $activeRatingCount > 0 ? round($totalActiveRating / $activeRatingCount, 2) : 0,
            'rating_group_count' => $ratingGroupCount,
        ];

        if ($reviews->count() > 0) {
            return response()->json(response_formatter(DEFAULT_200, ['reviews' => $reviews, 'rating' => $ratingInfo]), 200);
        }

        return response()->json(response_formatter(DEFAULT_404), 200);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function changeLanguage(Request $request): JsonResponse
    {
        if (auth('api')->user()) {
            $customer = $this->user::find(auth('api')->user()->id);
            $customer->current_language_key = $request->header('X-localization') ?? 'en';
            $customer->save();
            return response()->json(response_formatter(DEFAULT_200), 200);
        }
        return response()->json(response_formatter(DEFAULT_404), 200);
    }

    public function adjust(Request $request): JsonResponse
    {
        $provider = Provider::where('user_id', $request->user()->id)->first();
        $account = $this->account->where('user_id', $request->user()->id)->first();
        $receivable = $account->account_receivable;
        $payable = $account->account_payable;

        if ($receivable == $payable){

            withdrawRequestAcceptForAdjustTransaction($request->user()->id, $receivable);
            collectCashTransaction($provider->id, $payable);

            return response()->json(response_formatter(ADJUST_AMOUNT_SUCCESS_200), 200);
        }

        return response()->json(response_formatter(DEFAULT_404), 200);

    }
    public function transaction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'transaction_type' => 'nullable|in:paid_commission,paid_amount,all',
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }
        $filteredTransactions = $this->transaction
            ->with(['booking', 'from_user.provider', 'to_user.provider'])
            ->when($request->transaction_type !== 'all', function ($query) use ($request) {
                return $query->where('trx_type', $request->transaction_type);
            })
            ->when($request->transaction_type === 'all', function ($query) {
                return $query->whereIn('trx_type', ['paid_commission', 'paid_amount']);
            })
            ->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        return response()->json(response_formatter(DEFAULT_200, $filteredTransactions, 200));
    }

}
