<?php

namespace Modules\ServiceManagement\Http\Controllers\Web\Provider;

use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\BookingModule\Entities\Booking;
use Modules\BusinessSettingsModule\Entities\PackageSubscriber;
use Modules\BusinessSettingsModule\Entities\PackageSubscriberLimit;
use Modules\CategoryManagement\Entities\Category;
use Modules\ProviderManagement\Entities\SubscribedService;
use Modules\ReviewModule\Entities\Review;
use Modules\ReviewModule\Entities\ReviewReply;
use Modules\ServiceManagement\Entities\Faq;
use Modules\ServiceManagement\Entities\Service;
use Modules\ServiceManagement\Entities\ServiceRequest;
use Auth;
use Rap2hpoutre\FastExcel\FastExcel;

class ServiceController extends Controller
{
    private Service $service;
    private Review $review;
    private ReviewReply $reviewReply;
    private SubscribedService $subscribed_service;
    private Category $category;
    private Booking $booking;
    private Faq $faq;
    private PackageSubscriber $packageSubscriber;
    private PackageSubscriberLimit $packageSubscriberLimit;

    public function __construct(Service $service, Review $review, ReviewReply $reviewReply, SubscribedService $subscribed_service, Category $category, Booking $booking, Faq $faq, PackageSubscriber $packageSubscriber, PackageSubscriberLimit $packageSubscriberLimit)
    {
        $this->service = $service;
        $this->review = $review;
        $this->reviewReply = $reviewReply;
        $this->subscribed_service = $subscribed_service;
        $this->packageSubscriber = $packageSubscriber;
        $this->packageSubscriberLimit = $packageSubscriberLimit;
        $this->category = $category;
        $this->booking = $booking;
        $this->faq = $faq;
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request): View|Factory|Application
    {
        $activeCategory = $request->has('active_category') ? $request['active_category'] : 'all';

        $subscribedIds = $this->subscribed_service->where('provider_id', $request->user()->provider->id)
            ->ofStatus(1)
            ->pluck('sub_category_id')
            ->toArray();

        $categories = $this->category->ofStatus(1)->ofType('main')
            ->whereHas('zones', function ($query) use ($request) {
                return $query->where('zone_id', $request->user()->provider->zone_id);
            })->latest()->get();

        $subCategories = $this->category->with(['services'])
            ->with(['services' => function ($query) {
                $query->where(['is_active' => 1]);
            }])
            ->withCount(['services' => function ($query) {
                $query->where(['is_active' => 1]);
            }])
            ->when($activeCategory != 'all', function ($query) use ($activeCategory) {
                $query->where(['parent_id' => $activeCategory]);
            })
            ->when($request->has('category_id') && $request['category_id'] != 'all', function ($query) use ($request) {
                $query->where('parent_id', $request['category_id']);
            })
            ->whereHas('parent.zones', function ($query) use ($request) {
                $query->where('zone_id', $request->user()->provider->zone_id);
            })
            ->whereHas('parent', function ($query) {
                $query->where('is_active', 1);
            })
            ->ofStatus(1)->ofType('sub')
            ->latest()->get();

        return view('servicemanagement::provider.available-services', compact('categories', 'subCategories', 'subscribedIds', 'activeCategory'));
    }

    /**
     * Display a listing of the resource.
     * @return Application|Factory|View
     */
    public function requestList(Request $request): View|Factory|Application
    {
        $search = $request['search'];
        $requests = ServiceRequest::with(['category'])
            ->where('user_id', Auth::id())
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->whereHas('category', function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->where('name', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->latest()
            ->paginate(pagination_limit());

        return view('servicemanagement::provider.service.request-list', compact('requests', 'search'));
    }

    /**
     * Display a listing of the resource.
     * @return Application|Factory|View
     */
    public function makeRequest(): View|Factory|Application
    {
        $categories = $this->category->ofType('main')->select('id', 'name')->get();
        return view('servicemanagement::provider.service.make-request', compact('categories'));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return RedirectResponse
     */
    public function storeRequest(Request $request): RedirectResponse
    {
        Validator::make($request->all(), [
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'nullable|uuid',
            'service_name' => 'required|max:255',
            'service_description' => 'required',
        ])->validate();

        ServiceRequest::create([
            'category_id' => strtolower($request['category_id']) == 'null' || $request['category_id'] == '' ? null : $request['category_id'],
            'service_name' => $request['service_name'],
            'service_description' => $request['service_description'],
            'status' => 'pending',
            'user_id' => $request->user()->id,
        ]);

        Toastr::success(translate(SERVICE_REQUEST_STORE_200['message']));
        return back();
    }


    public function updateSubscription(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sub_category_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 200);
        }

        $packageSubscriber = $this->packageSubscriber->where('provider_id', $request->user()->provider->id)->first();
        $limit = $this->packageSubscriberLimit
            ->where('provider_id', $request->user()->provider->id)
            ->where('subscription_package_id', $packageSubscriber?->subscription_package_id)
            ->where('key', 'category')
            ->first();

        $packageSubscriberLimit = $limit?->limit_count;
        $isLimit = $limit?->is_limited;
        $startDate = $packageSubscriber?->package_start_date;
        $endDate = $packageSubscriber?->package_end_date;
        $providerId = $packageSubscriber?->provider_id;
        $currentDate = Carbon::now()->subDays();
        $packageEndDate = $endDate ? Carbon::parse($endDate)->endOfDay() : null;
        $isPackageEnded = $packageEndDate ? $currentDate->diffInDays($packageEndDate, false) : null;

        $categoryCount = $this->subscribed_service->where('provider_id', $providerId)->where('is_subscribed', 1)
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $startDate = Carbon::parse($startDate)->startOfDay();
                $endDate = Carbon::parse($endDate)->endOfDay();
                return $query ->whereBetween(DB::raw('DATE(updated_at)'), [date('Y-m-d', strtotime($startDate)), date('Y-m-d', strtotime($endDate))]);
            })
            ->count();

        $subscribedService = $this->subscribed_service::where('sub_category_id', $request['sub_category_id'])->where('provider_id', $request->user()->provider->id)->first();

        if (!$subscribedService) {
            if ($packageSubscriberLimit <= $categoryCount && $packageSubscriber && $isLimit && $isPackageEnded) {
                return response()->json(response_formatter(CATEGORY_LIMIT_END), 400);
            }

            $subscribedService = new $this->subscribed_service;
            $subscribedService->is_subscribed = 1;

        } elseif($subscribedService) {
            if ($subscribedService->is_subscribed == 0){
                if ($packageSubscriberLimit <= $categoryCount && $packageSubscriber && $isLimit && $isPackageEnded) {
                    return response()->json(response_formatter(CATEGORY_LIMIT_END), 400);
                }
            }

            $subscribedService->is_subscribed = !$subscribedService->is_subscribed;
        }

        $subscribedService->provider_id = $request->user()->provider->id;
        $subscribedService->sub_category_id = $request['sub_category_id'];

        $parent = $this->category->where('id', $request['sub_category_id'])->whereHas('parent.zones', function ($query) {
            $query->where('zone_id', auth()->user()->provider->zone_id);
        })->first();

        if ($parent) {
            $subscribedService->category_id = $parent->parent_id;
            $subscribedService->save();
            return response()->json(response_formatter(DEFAULT_200), 200);
        }

        return response()->json(response_formatter(DEFAULT_204), 200);
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @param string $service_id
     * @return JsonResponse
     */
    public function review(Request $request, string $service_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'status' => 'required|in:active,inactive,all'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $reviews = $this->review->where('provider_id', $request->user()->provider->id)->where('service_id', $service_id)
            ->when($request->has('status') && $request['status'] != 'all', function ($query) use ($request) {
                return $query->ofStatus(($request['status'] == 'active') ? 1 : 0);
            })->latest()->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        $ratingGroupCount = DB::table('reviews')->where('provider_id', $request->user()->provider->id)
            ->where('service_id', $service_id)
            ->select('review_rating', DB::raw('count(*) as total'))
            ->groupBy('review_rating')
            ->get();

        $totalAvg = 0;
        $mainDivider = 0;
        foreach ($ratingGroupCount as $count) {
            $totalAvg = round($count->review_rating / $count->total, 2);
            $mainDivider += 1;
        }

        $ratingInfo = [
            'rating_count' => $ratingGroupCount->count(),
            'average_rating' => round($totalAvg / ($mainDivider == 0 ? $mainDivider + 1 : $mainDivider), 2),
            'rating_group_count' => $ratingGroupCount,
        ];

        if ($reviews->count() > 0) {
            return response()->json(response_formatter(DEFAULT_200, ['reviews' => $reviews, 'rating' => $ratingInfo]), 200);
        }

        return response()->json(response_formatter(DEFAULT_404), 200);
    }


    /**
     * Show the specified resource.
     * @param Request $request
     * @param string $id
     * @return Application|Factory|View|RedirectResponse
     */
    public function show(Request $request, string $id): View|Factory|RedirectResponse|Application
    {
        $service = $this->service->where('id', $id)->with(['category.children', 'variations.zone',
            'reviews'])->withCount(['bookings'])->first();
        $ongoing = $this->booking->whereHas('detail', function ($query) use ($id) {
            $query->where('service_id', $id);
        })->where(['booking_status' => 'ongoing'])->count();
        $canceled = $this->booking->whereHas('detail', function ($query) use ($id) {
            $query->where('service_id', $id);
        })->where('provider_id', $request->user()->provider->id)->where(['booking_status' => 'canceled'])->count();

        $faqs = $this->faq->latest()->where('service_id', $id)->get();

        $search = $request->has('review_search') ? $request['review_search'] : '';
        $webPage = $request->has('review_page') || $request->has('review_search') ? 'review' : 'general';
        $queryParam = ['search' => $search, 'web_page' => $webPage];

        $reviews = $this->review->with(['customer', 'booking'])
            ->where('service_id', $id)
            ->where('is_active', 1)
            ->when($request->has('review_search') && !empty($request['review_search']), function ($query) use ($request) {
                $keys = explode(' ', $request['review_search']);
                foreach ($keys as $key) {
                    $query->where('review_comment', 'LIKE', '%' . $key . '%')
                        ->orWhere('readable_id', 'LIKE', '%' . $key . '%');
                }
            })
            ->where('provider_id', $request->user()->provider->id)
            ->latest()->paginate(pagination_limit(), ['*'], 'review_page')->appends($queryParam);

        $rating_group_count = DB::table('reviews')->where('provider_id', $request->user()->provider->id)
            ->where('service_id', $id)
            ->select('review_rating', DB::raw('count(*) as total'))
            ->groupBy('review_rating')
            ->get();

        if (isset($service)) {
            $service['ongoing_count'] = $ongoing;
            $service['canceled_count'] = $canceled;
            return view('servicemanagement::provider.detail', compact('service', 'faqs', 'reviews', 'rating_group_count', 'webPage','search'));
        }

        Toastr::error(translate(DEFAULT_204['message']));
        return back();
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('servicemanagement::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function statusUpdate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:1,0',
            'sub_category_ids' => 'required|array',
            'sub_category_ids.*' => 'uuid'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $this->subscribed_service->whereIn('sub_category_id', $request['sub_category_ids'])->update(['is_subscribed' => $request['status']]);

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    /**
     * Show the specified resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'string' => 'required',
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $keys = explode(' ', base64_decode($request['string']));

        $service = $this->service->where(function ($query) use ($keys) {
            foreach ($keys as $key) {
                $query->orWhere('name', 'LIKE', '%' . $key . '%');
            }
        })->when($request->has('status') && $request['status'] != 'all', function ($query) use ($request) {
            if ($request['status'] == 'active') {
                return $query->where(['is_active' => 1]);
            } else {
                return $query->where(['is_active' => 0]);
            }
        })->with(['category.zonesBasicInfo'])->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        if (count($service) > 0) {
            return response()->json(response_formatter(DEFAULT_200, $service), 200);
        }
        return response()->json(response_formatter(DEFAULT_204, $service), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function reviewReply(Request $request): JsonResponse|RedirectResponse
    {
        $providerUserId = auth()->user()->id;
        $review_id = $request->review_id;

        $readableId = $this->review->where('id', $review_id)->value('readable_id') ?? 0;


        $reviewReply = $this->reviewReply
            ->where('review_id', 'like', "{$review_id}%")
            ->orderBy('readable_id', 'desc')
            ->first();

        if (!$reviewReply) {
            $reviewReply = $this->reviewReply;
        }
            $reviewReply->review_id = $review_id;
            $reviewReply->readable_id = $readableId;
            $reviewReply->user_id = $providerUserId;
            $reviewReply->reply = $request->reply_content;
            $reviewReply->save();
//        dd($reviewReply);

        Toastr::success(translate(DEFAULT_200['message']));
        return back();

    }

    public function reviewsDownload(Request $request)
    {
        $items = $this->review->with(['customer', 'booking'])
            ->where('service_id', $request->service_id)
            ->where('provider_id', $request->user()->provider->id)
            ->when($request->has('review_search') && !empty($request['review_search']), function ($query) use ($request) {
                $keys = explode(' ', $request['review_search']);
                foreach ($keys as $key) {
                    $query->where('review_comment', 'LIKE', '%' . $key . '%')
                        ->orWhere('readable_id', 'LIKE', '%' . $key . '%');
                }
            })
            ->latest()
            ->get();

        return (new FastExcel($items))->download(time() . '-file.xlsx');
    }
}
