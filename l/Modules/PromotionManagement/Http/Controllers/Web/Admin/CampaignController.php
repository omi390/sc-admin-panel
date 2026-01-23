<?php

namespace Modules\PromotionManagement\Http\Controllers\Web\Admin;

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
use Modules\CategoryManagement\Entities\Category;
use Modules\PromotionManagement\Entities\Campaign;
use Modules\PromotionManagement\Entities\Discount;
use Modules\PromotionManagement\Entities\DiscountType;
use Modules\ServiceManagement\Entities\Service;
use Modules\ZoneManagement\Entities\Zone;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CampaignController extends Controller
{
    protected $discount, $campaign, $discountType, $service, $category, $zone, $discountTypes;
    use AuthorizesRequests;

    public function __construct(Campaign $campaign, Discount $discount, DiscountType $discountType, Service $service, Category $category, Zone $zone)
    {
        $this->discount = $discount;
        $this->campaign = $campaign;
        $this->discountType = $discountType;
        $this->service = $service;
        $this->category = $category;
        $this->zone = $zone;
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Factory|View|Application
     * @throws AuthorizationException
     */
    public function index(Request $request): Factory|View|Application
    {
        $this->authorize('campaign_view');
        $search = $request->has('search') ? $request['search'] : '';
        $discountType = $request->has('discount_type') ? $request['discount_type'] : 'all';
        $queryParam = ['search' => $search, 'discount_type' => $discountType];

        $campaigns = $this->campaign->with(['discount', 'discount.category_types.category', 'discount.service_types.service', 'discount.zone_types'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('campaign_name', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request->has('discount_type') && $request['discount_type'] != 'all', function ($query) use ($request) {
                return $query->whereHas('discount',function ($query) use ($request) {
                    $query->where(['discount_type' => $request['discount_type']]);
                });
            })->latest()->paginate(pagination_limit())->appends($queryParam);

        return view('promotionmanagement::admin.campaigns.list', compact('campaigns', 'search', 'discountType'));
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function create(Request $request): View|Factory|Application
    {
        $this->authorize('campaign_add');
        $categories = $this->category->ofStatus(1)->ofType('main')->latest()->get();
        $zones = $this->zone->ofStatus(1)->latest()->get();
        $services = $this->service->active()->latest()->get();

        return view('promotionmanagement::admin.campaigns.create', compact('categories', 'zones', 'services'));
    }


    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('campaign_add');
        $request->validate([
            'campaign_name' => 'required',
            'cover_image' => 'required|image|required|max:10000|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'thumbnail' => 'required|image|required|max:10000|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'discount_type' => 'required',
            'discount_title' => 'required',
            'discount_amount' => 'required|numeric',
            'discount_amount_type' => 'required|in:percent,amount',
            'min_purchase' => 'required|numeric',
            'max_discount_amount' => $request['discount_amount_type'] == 'amount' ? '' : 'required' . '|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'limit_per_user' => 'numeric',
        ]);

        DB::transaction(function () use ($request) {
            $discount = $this->discount;
            $discount->discount_type = $request['discount_type'];
            $discount->discount_title = $request['discount_title'];
            $discount->discount_amount = $request['discount_amount'];
            $discount->discount_amount_type = $request['discount_amount_type'];
            $discount->min_purchase = $request['min_purchase'];
            $discount->max_discount_amount = !is_null($request['max_discount_amount']) ? $request['max_discount_amount'] : 0;
            $discount->limit_per_user = $request['limit_per_user']??0;
            $discount->promotion_type = 'campaign';
            $discount->start_date = $request['start_date'];
            $discount->end_date = $request['end_date'];
            $discount->is_active = 1;
            $discount->save();

            $campaign = $this->campaign;
            $campaign->thumbnail = file_uploader('campaign/', 'png', $request->file('thumbnail'));
            $campaign->cover_image = file_uploader('campaign/', 'png', $request->file('cover_image'));
            $campaign->campaign_name = $request['campaign_name'];
            $campaign->discount_id = $discount['id'];
            $campaign->is_active = 1;
            $campaign->save();

            $disTypes = ['category', 'service', 'zone'];
            foreach ((array)$disTypes as $disType) {
                $types = [];
                foreach ((array)$request[$disType . '_ids'] as $id) {
                    $types[] = [
                        'discount_id' => $discount['id'],
                        'discount_type' => $disType,
                        'type_wise_id' => $id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                $discount->discount_types()->createMany($types);
            }
        });

        Toastr::success(translate(DEFAULT_STORE_200['message']));
        return back();
    }

    /**
     * Show the form for editing the specified resource.
     * @param string $id
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function edit(string $id): View|Factory|Application
    {
        $this->authorize('campaign_update');
        $campaign = $this->campaign->with(['discount', 'discount.category_types', 'discount.service_types', 'discount.zone_types'])->where('id', $id)->first();
        $categories = $this->category->ofStatus(1)->ofType('main')->latest()->get();
        $zones = $this->zone->ofStatus(1)->latest()->get();
        $services = $this->service->active()->latest()->get();

        return view('promotionmanagement::admin.campaigns.edit', compact('categories', 'zones', 'services', 'campaign'));
    }


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param string $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $this->authorize('campaign_update');
        $request->validate([
            'campaign_name' => 'required',
            'cover_image' => 'image|max:10000|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'thumbnail' => 'image|max:10000|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'discount_type' => 'required',
            'discount_title' => 'required',
            'discount_amount' => 'required|numeric',
            'discount_amount_type' => 'required|in:percent,amount',
            'min_purchase' => 'required|numeric',
            'max_discount_amount' => $request['discount_amount_type'] == 'amount' ? '' : 'required' . '|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'limit_per_user' => 'numeric',
        ]);

        DB::transaction(function () use ($request, $id) {
            $campaign = $this->campaign->where(['id' => $id])->first();
            if ($request->has('thumbnail')) {
                $campaign->thumbnail = file_uploader('campaign/', 'png', $request->file('thumbnail'), $campaign->thumbnail);
            }
            if ($request->has('cover_image')) {
                $campaign->cover_image = file_uploader('campaign/', 'png', $request->file('cover_image'), $campaign->cover_image);
            }
            $campaign->campaign_name = $request['campaign_name'];
            $campaign->save();

            $discount = $this->discount->find($campaign['discount_id']);
            $discount->discount_type = $request['discount_type'];
            $discount->discount_title = $request['discount_title'];
            $discount->discount_amount = $request['discount_amount'];
            $discount->discount_amount_type = $request['discount_amount_type'];
            $discount->min_purchase = $request['min_purchase'];
            $discount->max_discount_amount = !is_null($request['max_discount_amount']) ? $request['max_discount_amount'] : 0;
            $discount->limit_per_user = $request['limit_per_user']??0;
            $discount->start_date = $request['start_date'];
            $discount->end_date = $request['end_date'];
            $discount->is_active = 1;
            $discount->save();

            $discount->discount_types()->delete();

            $disTypes = ['category', 'service', 'zone'];
            foreach ((array)$disTypes as $disType) {
                $types = [];
                foreach ((array)$request[$disType . '_ids'] as $id) {
                    $types[] = [
                        'discount_id' => $discount['id'],
                        'discount_type' => $disType,
                        'type_wise_id' => $id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                $discount->discount_types()->createMany($types);
            }
        });

        Toastr::success(translate(CAMPAIGN_UPDATE_200['message']));
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
        $this->authorize('campaign_delete');

        $campaign = $this->campaign->where('id', $id)->first();

        if (isset($campaign)){
            file_remover('campaign/', $campaign['thumbnail']);
            file_remover('campaign/', $campaign['cover_image']);
            $this->discount->where('id', $campaign['discount_id'])->delete();
            $this->discountType->where('discount_id', $campaign['discount_id'])->delete();
            $this->campaign->where('id', $id)->delete();
        }

        Toastr::success(translate(DEFAULT_DELETE_200['message']));
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
        $this->authorize('campaign_manage_status');
        $campaign = $this->campaign->where('id', $id)->first();
        $this->campaign->where('id', $id)->update(['is_active' => !$campaign->is_active]);
        $this->discount->where('id', $campaign->discount_id)->update(['is_active' => !$campaign->is_active]);

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return string|StreamedResponse
     */
    public function download(Request $request): string|StreamedResponse
    {
        $this->authorize('campaign_export');
        $items = $this->campaign->with(['discount', 'discount.category_types', 'discount.service_types', 'discount.zone_types'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('campaign_name', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request->has('discount_type') && $request['discount_type'] != 'all', function ($query) use ($request) {
                return $query->whereHas('discount',function ($query) use ($request) {
                    $query->where(['discount_type' => $request['discount_type']]);
                });
            })->latest()->get();

        return (new FastExcel($items))->download(time().'-file.xlsx');
    }
}
