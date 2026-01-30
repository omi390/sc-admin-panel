<?php

namespace Modules\ServiceManagement\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\BookingModule\Entities\Booking;
use Modules\BusinessSettingsModule\Entities\Translation;
use Modules\CategoryManagement\Entities\Category;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ReviewModule\Entities\Review;
use Modules\ReviewModule\Entities\ReviewReply;
use Modules\ServiceManagement\Entities\Faq;
use Modules\ServiceManagement\Entities\Service;
use Modules\ServiceManagement\Entities\ServiceSection;
use Modules\ServiceManagement\Entities\Tag;
use Modules\ServiceManagement\Entities\Variation;
use Modules\ZoneManagement\Entities\Zone;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ServiceController extends Controller
{
    private Review $review;
    private ReviewReply $reviewReply;
    private Faq $faq;
    private Variation $variation;
    private Zone $zone;
    private Category $category;
    private Booking $booking;
    private Service $service;
    private Provider $provider;

    use AuthorizesRequests;

    public function __construct(Service $service, Booking $booking, Category $category, Zone $zone, Variation $variation, Faq $faq, Review $review, ReviewReply $reviewReply, Provider $provider)
    {
        $this->service = $service;
        $this->booking = $booking;
        $this->category = $category;
        $this->zone = $zone;
        $this->variation = $variation;
        $this->faq = $faq;
        $this->review = $review;
        $this->reviewReply = $reviewReply;
        $this->provider = $provider;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function create(Request $request): View|Factory|Application
    {
        $this->authorize('service_add');
        $categories = $this->category->ofStatus(1)->ofType('main')->latest()->get();
        $zones = $this->zone->ofStatus(1)->latest()->get();

        return view('servicemanagement::admin.create', compact('categories', 'zones'));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function index(Request $request): View|Factory|Application
    {
        $this->authorize('service_view');
        $request->validate([
            'status' => 'in:active,inactive,all',
            'zone_id' => 'uuid'
        ]);

        $search = $request->has('search') ? $request['search'] : '';
        $status = $request->has('status') ? $request['status'] : 'all';
        $queryParam = ['search' => $search, 'status' => $status];

        $services = $this->service->with(['category.zonesBasicInfo'])->latest()
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('name', 'LIKE', '%' . $key . '%');
                }
            })
            ->when($request->has('category_id'), function ($query) use ($request) {
                return $query->where('category_id', $request->category_id);
            })->when($request->has('sub_category_id'), function ($query) use ($request) {
                return $query->where('sub_category_id', $request->sub_category_id);
            })->when($request->has('status') && $request['status'] != 'all', function ($query) use ($request) {
                if ($request['status'] == 'active') {
                    return $query->where(['is_active' => 1]);
                } else {
                    return $query->where(['is_active' => 0]);
                }
            })->when($request->has('zone_id'), function ($query) use ($request) {
                return $query->whereHas('category.zonesBasicInfo', function ($queryZone) use ($request) {
                    $queryZone->where('zone_id', $request['zone_id']);
                });
            })->paginate(pagination_limit())->appends($queryParam);

        return view('servicemanagement::admin.list', compact('services', 'search', 'status'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('service_add');
        $variations = session('variations');
        session()->forget('variations');

        $request->validate([
                'name' => 'required|max:191',
                'name.0' => 'required|max:191',
                'category_id' => 'required|uuid',
                'sub_category_id' => 'required|uuid',
                'cover_image' => 'required_without:cover_image_url|nullable|image|mimes:jpeg,jpg,png,gif|max:10000',
                'cover_image_url' => 'required_without:cover_image|nullable|url',
                'description' => 'required',
                'description.0' => 'required',
                'short_description' => 'required',
                'short_description.0' => 'required',
                'thumbnail' => 'required',
                'tax' => 'required|numeric|min:0|max:100',
                'min_bidding_price' => 'required|numeric|min:0|not_in:0',
            ]
        );


        $tagIds = [];
        if ($request->tags != null) {
            $tags = explode(",", $request->tags);
        }
        if (isset($tags)) {
            foreach ($tags as $key => $value) {
                $tag = Tag::firstOrNew(['tag' => $value]);
                $tag->save();
                $tagIds[] = $tag->id;
            }
        }

        $service = $this->service;
        $service->name = $request->name[array_search('default', $request->lang)];
        $service->category_id = $request->category_id;
        $service->sub_category_id = $request->sub_category_id;
        $service->short_description = $request->short_description[array_search('default', $request->lang)];
        $service->description = $request->description[array_search('default', $request->lang)];
        if ($request->hasFile('cover_image')) {
            $service->cover_image = file_uploader('service/', 'png', $request->file('cover_image'));
        } elseif ($request->filled('cover_image_url')) {
            $service->cover_image = $request->cover_image_url;
        }
        $service->thumbnail = file_uploader('service/', 'png', $request->file('thumbnail'));
        $service->tax = $request->tax;
        $service->min_bidding_price = $request->min_bidding_price;
        $service->images = $request->input('images') ? array_values(array_filter((array) $request->input('images'))) : null;
        $service->save();
        $service->tags()->sync($tagIds);

        //decoding url encoded keys
        $data = $request->all();
        $data = collect($data)->map(function ($value, $key) {
            $key = urldecode($key);
            return [$key => $value];
        })->collapse()->all();

        $variationFormat = [];
        if ($variations) {
            $zones = $this->zone->ofStatus(1)->latest()->get();
            foreach ($variations as $item) {
                // If provider_id is set, create variant for that specific provider and zone
                if (isset($item['provider_id']) && isset($item['zone_id'])) {
                    $variationFormat[] = [
                        'variant' => $item['variant'],
                        'variant_key' => $item['variant_key'],
                        'zone_id' => $item['zone_id'],
                        'provider_id' => $item['provider_id'],
                        'price' => $item['price'] ?? 0,
                        'variation_image' => $item['variation_image'] ?? null,
                        'service_id' => $service->id
                    ];
                } else {
                    // Original behavior: create for all zones
                    foreach ($zones as $zone) {
                        $variationFormat[] = [
                            'variant' => $item['variant'],
                            'variant_key' => $item['variant_key'],
                            'zone_id' => $zone->id,
                            'price' => $data[$item['variant_key'] . '_' . $zone->id . '_price'] ?? 0,
                            'service_id' => $service->id
                        ];
                    }
                }
            }
        }

        // Handle provider variants from form submission
        if ($request->has('provider_variants') && is_array($request->provider_variants)) {
            foreach ($request->provider_variants as $providerVariant) {
                $variationFormat[] = [
                    'variant' => str_replace('-', ' ', $providerVariant['variant_key']),
                    'variant_key' => $providerVariant['variant_key'],
                    'zone_id' => $providerVariant['zone_id'],
                    'provider_id' => $providerVariant['provider_id'],
                    'price' => $providerVariant['price'] ?? 0,
                    'variation_image' => $providerVariant['variation_image'] ?? null,
                    'service_id' => $service->id
                ];
            }
        }

        $service->variations()->createMany($variationFormat);

        // Save service sections (admin only)
        $sectionsData = $request->input('service_sections', []);
        if (is_array($sectionsData)) {
            $sectionsData = array_values(array_filter($sectionsData, function ($s) {
                return !empty($s['title'] ?? '') || !empty($s['description'] ?? '');
            }));
            foreach ($sectionsData as $sortOrder => $sectionRow) {
                $service->sections()->create([
                    'title' => $sectionRow['title'] ?? '',
                    'description' => $sectionRow['description'] ?? '',
                    'sort_order' => $sortOrder,
                ]);
            }
        }

        $defaultLang = str_replace('_', '-', app()->getLocale());

        foreach ($request->lang as $index => $key) {
            if ($defaultLang == $key && !($request->name[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'name'],
                        ['value' => $service->name]
                    );
                }
            } else {

                if ($request->name[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'name'],
                        ['value' => $request->name[$index]]
                    );
                }
            }

            if ($defaultLang == $key && !($request->short_description[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'short_description'],
                        ['value' => $service->short_description]
                    );
                }
            } else {

                if ($request->short_description[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'short_description'],
                        ['value' => $request->short_description[$index]]
                    );
                }
            }

            if ($defaultLang == $key && !($request->description[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'description'],
                        ['value' => $service->description]
                    );
                }
            } else {

                if ($request->description[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'description'],
                        ['value' => $request->description[$index]]
                    );
                }
            }
        }

        Toastr::success(translate(SERVICE_STORE_200['message']));

        return back();
    }

    /**
     * Show the specified resource.
     * @param Request $request
     * @param string $id
     * @return Application|Factory|View|RedirectResponse
     * @throws AuthorizationException
     */
    public function show(Request $request, string $id): View|Factory|RedirectResponse|Application
    {
        $this->authorize('service_view');
        $service = $this->service
            ->where('id', $id)
            ->with(['category' => function ($query) {
                $query->ofStatus(1);
            },'subCategory' => function ($query) {
                $query->ofStatus(1);
            }, 'category.zones', 'category.children', 'variations.zone', 'reviews'])
            ->withCount(['bookings'])
            ->first();

        $service->total_review_count = $service->reviews->avg('review_rating');

        $ongoing = $this->booking
            ->whereHas('detail', function ($query) use ($id) {
                return $query->where('service_id', $id);
            })
            ->where(['booking_status' => 'ongoing'])
            ->count();

        $canceled = $this->booking
            ->whereHas('detail', function ($query) use ($id) {
                return $query->where('service_id', $id);
            })
            ->where(['booking_status' => 'canceled'])
            ->count();

        $faqs = $this->faq->latest()->where('service_id', $id)->get();

        $search = $request->has('review_search') ? $request['review_search'] : '';
        $webPage = $request->has('review_page') || $request->has('review_search') ? 'review' : 'general';
        $queryParam = ['search' => $search, 'web_page' => $webPage];

        $reviews = $this->review->with(['customer', 'booking'])
            ->where('service_id', $id)
            ->when($request->has('review_search') && !empty($request['review_search']), function ($query) use ($request) {
                $keys = explode(' ', $request['review_search']);
                foreach ($keys as $key) {
                    $query->where('review_comment', 'LIKE', '%' . $key . '%')
                        ->orWhere('readable_id', 'LIKE', '%' . $key . '%');
                }
            })
            ->latest()->paginate(pagination_limit(), ['*'], 'review_page')->appends($queryParam);

        $rating_group_count = DB::table('reviews')
            ->select('review_rating', DB::raw('count(*) as total'))
            ->groupBy('review_rating')
            ->get();

        if (isset($service)) {
            $service['ongoing_count'] = $ongoing;
            $service['canceled_count'] = $canceled;
            return view('servicemanagement::admin.detail', compact('service', 'faqs', 'reviews', 'rating_group_count', 'webPage', 'search'));
        }

        Toastr::error(translate(DEFAULT_204['message']));
        return back();
    }

    /**
     * Show the form for editing the specified resource.
     * @param string $id
     * @return Application|Factory|View|RedirectResponse
     * @throws AuthorizationException
     */
    public function edit(string $id): View|Factory|RedirectResponse|Application
    {
        $this->authorize('service_update');
        $service = $this->service->withoutGlobalScope('translate')->where('id', $id)->with(['category.children', 'category.zones', 'variations.provider', 'variations.zone', 'sections'])->first();
        if (isset($service)) {
            $editingVariants = $service->variations->pluck('variant_key')->unique()->toArray();
            session()->put('editing_variants', $editingVariants);
            $categories = $this->category->ofStatus(1)->ofType('main')->latest()->get();

            $category = $this->category->where('id', $service->category_id)->with(['zones'])->first();
            $zones = $category->zones ?? [];
            session()->put('category_wise_zones', $zones);

            $tagNames = [];
            if ($service->tags) {
                foreach ($service->tags as $tag) {
                    $tagNames[] = $tag['tag'];
                }
            }

            return view('servicemanagement::admin.edit', compact('categories', 'zones', 'service', 'tagNames'));
        }

        Toastr::info(translate(DEFAULT_204['message']));
        return back();
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param string $id
     * @return JsonResponse|RedirectResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $this->authorize('service_update');
        $request->validate([
            'name' => 'required|max:191',
            'name.0' => 'required|max:191',
            'category_id' => 'required|uuid',
            'sub_category_id' => 'required|uuid',
            'description' => 'required',
            'description.0' => 'required',
            'short_description' => 'required',
            'short_description.0' => 'required',
            'tax' => 'required|numeric|min:0',
            'variants' => 'required|array',
            'min_bidding_price' => 'required|numeric|min:0|not_in:0',
        ]);

        $service = $this->service->find($id);
        if (!isset($service)) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        $tagIds = [];
        if ($request->tags != null) {
            $tags = explode(",", $request->tags);
        }
        if (isset($tags)) {
            foreach ($tags as $key => $value) {
                $tag = Tag::firstOrNew(['tag' => $value]);
                $tag->save();
                $tagIds[] = $tag->id;
            }
        }


        $service->name = $request->name[array_search('default', $request->lang)];
        $service->category_id = $request->category_id;
        $service->sub_category_id = $request->sub_category_id;
        $service->short_description = $request->short_description[array_search('default', $request->lang)];;
        $service->description = $request->description[array_search('default', $request->lang)];

        if ($request->hasFile('cover_image')) {
            $service->cover_image = file_uploader('service/', 'png', $request->file('cover_image'));
        } elseif ($request->filled('cover_image_url')) {
            $service->cover_image = $request->cover_image_url;
        }

        if ($request->has('thumbnail')) {
            $service->thumbnail = file_uploader('service/', 'png', $request->file('thumbnail'));
        }

        $service->images = $request->input('images') ? array_values(array_filter((array) $request->input('images'))) : null;

        $service->tax = $request->tax;
        $service->min_bidding_price = $request->min_bidding_price;
        $service->save();
        $service->tags()->sync($tagIds);

        $service->variations()->delete();

        //decoding url encoded keys
        $data = $request->all();
        $data = collect($data)->map(function ($value, $key) {
            $key = urldecode($key);
            return [$key => $value];
        })->collapse()->all();

        $variationFormat = [];
        $zones = $this->zone->ofStatus(1)->latest()->get();
        
        // Handle regular variants (for all zones)
        if (isset($data['variants']) && is_array($data['variants'])) {
            foreach ($data['variants'] as $item) {
                foreach ($zones as $zone) {
                    $variationFormat[] = [
                        'variant' => str_replace('_', ' ', $item),
                        'variant_key' => $item,
                        'zone_id' => $zone->id,
                        'price' => $data[$item . '_' . $zone->id . '_price'] ?? 0,
                        'service_id' => $service->id
                    ];
                }
            }
        }

        // Handle provider-specific variants
        if ($request->has('provider_variants') && is_array($request->provider_variants)) {
            foreach ($request->provider_variants as $providerVariant) {
                $variationFormat[] = [
                    'variant' => str_replace('-', ' ', $providerVariant['variant_key']),
                    'variant_key' => $providerVariant['variant_key'],
                    'zone_id' => $providerVariant['zone_id'],
                    'provider_id' => $providerVariant['provider_id'],
                    'price' => $providerVariant['price'] ?? 0,
                    'variation_image' => $providerVariant['variation_image'] ?? null,
                    'service_id' => $service->id
                ];
            }
        }

        $service->variations()->createMany($variationFormat);
        session()->forget('variations');
        session()->forget('editing_variants');

        // Update service sections (admin only): replace all
        $service->sections()->delete();
        $sectionsData = $request->input('service_sections', []);
        if (is_array($sectionsData)) {
            $sectionsData = array_values(array_filter($sectionsData, function ($s) {
                return !empty($s['title'] ?? '') || !empty($s['description'] ?? '');
            }));
            foreach ($sectionsData as $sortOrder => $sectionRow) {
                $service->sections()->create([
                    'title' => $sectionRow['title'] ?? '',
                    'description' => $sectionRow['description'] ?? '',
                    'sort_order' => $sortOrder,
                ]);
            }
        }

        $defaultLang = str_replace('_', '-', app()->getLocale());

        foreach ($request->lang as $index => $key) {
            if ($defaultLang == $key && !($request->name[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'name'],
                        ['value' => $service->name]
                    );
                }
            } else {

                if ($request->name[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'name'],
                        ['value' => $request->name[$index]]
                    );
                }
            }

            if ($defaultLang == $key && !($request->short_description[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'short_description'],
                        ['value' => $service->short_description]
                    );
                }
            } else {

                if ($request->short_description[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'short_description'],
                        ['value' => $request->short_description[$index]]
                    );
                }
            }

            if ($defaultLang == $key && !($request->description[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'description'],
                        ['value' => $service->description]
                    );
                }
            } else {

                if ($request->description[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'description'],
                        ['value' => $request->description[$index]]
                    );
                }
            }
        }


        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function destroy(Request $request, $id): RedirectResponse
    {
        $this->authorize('service_delete');
        $service = $this->service->where('id', $id)->first();
        if (isset($service)) {
            foreach (['thumbnail', 'cover_image'] as $item) {
                file_remover('service/', $service[$item]);
            }
            $service->translations()->delete();
            $service->variations()->delete();
            $service->delete();

            Toastr::success(translate(DEFAULT_DELETE_200['message']));
            return back();
        }
        Toastr::success(translate(DEFAULT_204['message']));
        return back();
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function statusUpdate(Request $request, $id): JsonResponse
    {
        $this->authorize('service_manage_status');
        $service = $this->service->where('id', $id)->first();
        $this->service->where('id', $id)->update(['is_active' => !$service->is_active]);

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function reviewStatusUpdate(Request $request, $id): JsonResponse
    {
        $review = $this->review->where('id', $id)->first();
        $this->review->where('id', $id)->update(['is_active' => !$review->is_active]);

        foreach (['service_id' => $review->service_id, 'provider_id' => $review->provider_id] as $key => $value) {
            $ratingGroupCount = DB::table('reviews')->where($key, $value)->where('is_active', 1)
                ->select('review_rating', DB::raw('count(*) as total'))
                ->groupBy('review_rating')
                ->get();

            $totalRating = 0;
            $ratingCount = 0;
            foreach ($ratingGroupCount as $count) {
                $totalRating += round($count->review_rating * $count->total, 2);
                $ratingCount += $count->total;
            }

            $query = collect([]);
            if ($key == 'service_id') {
                $query = $this->service->where(['id' => $value]);
            } elseif ($key == 'provider_id') {
                $query = $this->provider->where(['id' => $value]);
            }

            // Check if $ratingCount is greater than 0 before calculating the average rating
            if ($ratingCount > 0) {
                $avgRating = round($totalRating / $ratingCount, 2);
            } else {
                $avgRating = 0; // Handle cases where there are no ratings
            }

            $query->update([
                'rating_count' => $ratingCount,
                'avg_rating' => $avgRating
            ]);
        }

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }


    public function ajaxAddVariant(Request $request): JsonResponse
    {
        $variation = [
            'variant' => $request['name'],
            'variant_key' => str_replace(' ', '-', $request['name']),
            'price' => $request['price'],
            'provider_id' => $request['provider_id'] ?? null,
            'variation_image' => $request['variation_image'] ?? null,
            'zone_id' => $request['zone_id'] ?? null
        ];

        $zones = session()->has('category_wise_zones') ? session('category_wise_zones') : [];
        $existingData = session()->has('variations') ? session('variations') : [];
        $editingVariants = session()->has('editing_variants') ? session('editing_variants') : [];

        // Check for duplicate variant with same provider and zone
        $isDuplicate = false;
        if ($request->has('provider_id') && $request->has('zone_id')) {
            foreach ($existingData as $item) {
                if ($item['variant'] === $request['name'] 
                    && ($item['provider_id'] ?? null) == $request['provider_id']
                    && ($item['zone_id'] ?? null) == $request['zone_id']) {
                    $isDuplicate = true;
                    break;
                }
            }
        } else {
            $isDuplicate = self::searchForKey($request['name'], $existingData) || in_array(str_replace(' ', '-', $request['name']), $editingVariants);
        }

        if (!$isDuplicate) {
            $existingData[] = $variation;
            session()->put('variations', $existingData);
        } else {
            return response()->json(['flag' => 0, 'message' => translate('already_exist')]);
        }

        return response()->json(['flag' => 1, 'template' => view('servicemanagement::admin.partials._variant-data', compact('zones'))->render()]);
    }

    public function ajaxRemoveVariant($variant_key)
    {
        $zones = session()->has('category_wise_zones') ? session('category_wise_zones') : [];
        $existingData = session()->has('variations') ? session('variations') : [];

        // Remove variant by variant_key, but if it's a provider variant, we need to match provider_id and zone_id too
        $request = request();
        if ($request->has('provider_id') && $request->has('zone_id')) {
            $filtered = collect($existingData)->filter(function ($values) use ($variant_key, $request) {
                return !($values['variant_key'] == $variant_key 
                    && ($values['provider_id'] ?? null) == $request->provider_id
                    && ($values['zone_id'] ?? null) == $request->zone_id);
            })->values()->toArray();
        } else {
            $filtered = collect($existingData)->filter(function ($values) use ($variant_key) {
                return $values['variant_key'] != $variant_key || (isset($values['provider_id']) && $values['provider_id'] != null);
            })->values()->toArray();
        }

        session()->put('variations', $filtered);

        return response()->json(['flag' => 1, 'template' => view('servicemanagement::admin.partials._variant-data', compact('zones'))->render()]);
    }

    public function ajaxDeleteDbVariant($variant_key, $service_id)
    {
        $zones = session()->has('category_wise_zones') ? session('category_wise_zones') : $this->zone->ofStatus(1)->latest()->get();
        $this->variation->where(['variant_key' => $variant_key, 'service_id' => $service_id])->delete();
        $variants = $this->variation->where(['service_id' => $service_id])->get();

        return response()->json(['flag' => 1, 'template' => view('servicemanagement::admin.partials._update-variant-data', compact('zones', 'variants'))->render()]);
    }

    function searchForKey($variant, $array): int|string|null
    {
        foreach ($array as $key => $val) {
            if ($val['variant'] === $variant) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get providers by zone and subcategory
     * @param Request $request
     * @return JsonResponse
     */
    public function getProvidersByZoneAndSubcategory(Request $request): JsonResponse
    {
        $request->validate([
            'sub_category_id' => 'required|uuid',
            'zone_id' => 'nullable|uuid' // Make zone_id optional
        ]);

        // Use SubscribedService model directly to find providers
        $subscribedServiceIds = \Modules\ProviderManagement\Entities\SubscribedService::where('sub_category_id', $request->sub_category_id)
            ->where('is_subscribed', 1)
            ->pluck('provider_id')
            ->toArray();

        $providers = $this->provider
            ->whereIn('id', $subscribedServiceIds)
          
         
            ->select('id', 'company_name', 'company_phone')
            ->get();

        // Return in format that matches what JavaScript expects
        return response()->json([
            'flag' => 1,
            'data' => $providers
        ], 200);
    }

    /**
     * Update provider-specific variant
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxUpdateProviderVariant(Request $request): JsonResponse
    {
        $request->validate([
            'variant_id' => 'nullable|integer', // For existing variants in DB
            'variant_key' => 'required|string',
            'variant_name' => 'required|string',
            'price' => 'required|numeric|min:0',
            'variation_image' => 'nullable|url',
            'provider_id' => 'required|uuid',
            'zone_id' => 'nullable|uuid',
            'service_id' => 'nullable|uuid'
        ]);

        // If variant_id is provided, update existing variant in database
        if ($request->has('variant_id') && $request->variant_id) {
            $variant = $this->variation->find($request->variant_id);
            if ($variant) {
                $variant->variant = $request->variant_name;
                $variant->variant_key = str_replace(' ', '-', $request->variant_name);
                $variant->price = $request->price;
                $variant->variation_image = $request->variation_image ?? null;
                $variant->save();

                // Return updated variant data
                $zones = session()->has('category_wise_zones') ? session('category_wise_zones') : $this->zone->ofStatus(1)->latest()->get();
                $variants = $this->variation->where(['service_id' => $variant->service_id])->with(['provider', 'zone'])->get();

                return response()->json([
                    'flag' => 1,
                    'template' => view('servicemanagement::admin.partials._update-variant-data', compact('zones', 'variants'))->render()
                ]);
            }
        } else {
            // Update variant in session (for new variants not yet saved)
            $existingData = session()->has('variations') ? session('variations') : [];
            
            $updated = false;
            foreach ($existingData as $key => $item) {
                if (isset($item['variant_key']) && $item['variant_key'] == $request->variant_key
                    && isset($item['provider_id']) && $item['provider_id'] == $request->provider_id
                    && isset($item['zone_id']) && $item['zone_id'] == $request->zone_id) {
                    $existingData[$key]['variant'] = $request->variant_name;
                    $existingData[$key]['variant_key'] = str_replace(' ', '-', $request->variant_name);
                    $existingData[$key]['price'] = $request->price;
                    $existingData[$key]['variation_image'] = $request->variation_image ?? null;
                    $updated = true;
                    break;
                }
            }

            if ($updated) {
                session()->put('variations', $existingData);
                $zones = session()->has('category_wise_zones') ? session('category_wise_zones') : [];
                
                return response()->json([
                    'flag' => 1,
                    'template' => view('servicemanagement::admin.partials._variant-data', compact('zones'))->render()
                ]);
            }
        }

        return response()->json(['flag' => 0, 'message' => translate('variant_not_found')]);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return string|StreamedResponse
     */
    public function download(Request $request): string|StreamedResponse
    {
        $this->authorize('service_export');
        $items = $this->service->with(['category.zonesBasicInfo'])->latest()
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('name', 'LIKE', '%' . $key . '%');
                }
            })
            ->when($request->has('category_id'), function ($query) use ($request) {
                return $query->where('category_id', $request->category_id);
            })->when($request->has('sub_category_id'), function ($query) use ($request) {
                return $query->where('sub_category_id', $request->sub_category_id);
            })->when($request->has('zone_id'), function ($query) use ($request) {
                return $query->whereHas('category.zonesBasicInfo', function ($queryZone) use ($request) {
                    $queryZone->where('zone_id', $request['zone_id']);
                });
            })->latest()->get();

        return (new FastExcel($items))->download(time() . '-file.xlsx');
    }

    public function reviewsDownload(Request $request)
    {
        $items = $this->review->with(['customer', 'booking'])
            ->when($request->has('review_search') && !empty($request['review_search']), function ($query) use ($request) {
                $keys = explode(' ', $request['review_search']);
                foreach ($keys as $key) {
                    $query->where('review_comment', 'LIKE', '%' . $key . '%')
                        ->orWhere('readable_id', 'LIKE', '%' . $key . '%');
                }
            })
            ->where('service_id', $request->service_id)
            ->latest()
            ->get();

        return (new FastExcel($items))->download(time() . '-file.xlsx');
    }
}
