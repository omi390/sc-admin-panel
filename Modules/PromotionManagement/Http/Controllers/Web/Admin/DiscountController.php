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
use Modules\PromotionManagement\Entities\Discount;
use Modules\PromotionManagement\Entities\DiscountType;
use Modules\ServiceManagement\Entities\Service;
use Modules\ZoneManagement\Entities\Zone;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DiscountController extends Controller
{

    protected $discount, $service, $category, $zone, $discount_types;
    use AuthorizesRequests;

    public function __construct(Discount $discount, Service $service, Category $category, Zone $zone, DiscountType $discount_types)
    {
        $this->discountQuery = $discount->ofPromotionTypes('discount');
        $this->discount = $discount;
        $this->service = $service;
        $this->category = $category;
        $this->zone = $zone;
        $this->discount_types = $discount_types;
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function index(Request $request): View|Factory|Application
    {

        $this->authorize('discount_view');

        $search = $request->has('search') ? $request['search'] : '';
        $type = $request->has('type') ? $request['type'] : 'all';
        $queryParam = ['search' => $search, 'type' => $type];

        $discounts = $this->discountQuery->with(['category_types', 'service_types', 'zone_types'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('discount_title', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($type != 'all', function ($query) use ($type) {
                return $query->where(['discount_type' => $type]);
            })->orderBy('created_at', 'desc')->paginate(pagination_limit())->appends($queryParam);

        return view('promotionmanagement::admin.discounts.list', compact('discounts', 'search', 'type'));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function create(Request $request): View|Factory|Application
    {
        $this->authorize('discount_add');
        $categories = $this->category->ofStatus(1)->ofType('main')->latest()->get();
        $zones = $this->zone->withoutGlobalScope('translate')->ofStatus(1)->latest()->get();
        $services = $this->service->active()->latest()->get();

        return view('promotionmanagement::admin.discounts.create', compact('categories', 'zones', 'services'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('discount_add');
        $request->validate([
            'discount_type' => 'required|in:category,service,zone,mixed',
            'discount_amount' => 'required|numeric',
            'discount_title' => 'required|string',
            'discount_amount_type' => 'required|in:percent,amount',
            'min_purchase' => 'required|numeric|min:0',
            'max_discount_amount' => $request['discount_amount_type'] == 'amount' ? '' : 'required' . '|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'category_ids' => 'array',
            'service_ids' => 'array',
            'zone_ids' => 'required|array',
        ]);

        DB::transaction(function () use ($request) {
            $discount = $this->discount;
            $discount->discount_type = $request['discount_type'];
            $discount->discount_title = $request['discount_title'];
            $discount->discount_amount = $request['discount_amount'];
            $discount->discount_amount_type = $request['discount_amount_type'];
            $discount->min_purchase = $request['min_purchase'];
            $discount->max_discount_amount = !is_null($request['max_discount_amount']) ? $request['max_discount_amount'] : 0;
            $discount->promotion_type = 'discount';
            $discount->start_date = $request['start_date'];
            $discount->end_date = $request['end_date'];
            $discount->is_active = 1;
            $discount->save();

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

        Toastr::success(translate(DISCOUNT_CREATE_200['message']));
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
        $this->authorize('discount_update');
        $discount = $this->discountQuery->with(['category_types', 'service_types', 'zone_types'])->where('id', $id)->first();
        $categories = $this->category->ofStatus(1)->ofType('main')->latest()->get();
        $zones = $this->zone->withoutGlobalScope('translate')->ofStatus(1)->latest()->get();
        $services = $this->service->active()->latest()->get();

        return view('promotionmanagement::admin.discounts.edit', compact('categories', 'zones', 'services', 'discount'));
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
        $this->authorize('discount_update');
        $request->validate([
            'discount_type' => 'required|in:category,service,zone,mixed',
            'discount_amount' => 'required|numeric',
            'discount_title' => 'required|string',
            'discount_amount_type' => 'required|in:percent,amount',
            'min_purchase' => 'required|numeric',
            'max_discount_amount' => $request['discount_amount_type'] == 'amount' ? '' : 'required' . '|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'category_ids' => 'array',
            'service_ids' => 'array',
            'zone_ids' => 'required|array',
        ]);

        $discount = $this->discountQuery->where(['id' => $id])->first();
        if (isset($discount)) {
            DB::transaction(function () use ($request, $id, $discount) {

                $discount->discount_type = $request['discount_type'];
                $discount->discount_title = $request['discount_title'];
                $discount->discount_amount = $request['discount_amount'];
                $discount->discount_amount_type = $request['discount_amount_type'];
                $discount->min_purchase = $request['min_purchase'];
                $discount->max_discount_amount = !is_null($request['max_discount_amount']) ? $request['max_discount_amount'] : 0;
                $discount->promotion_type = 'discount';
                $discount->start_date = $request['start_date'];
                $discount->end_date = $request['end_date'];
                $discount->is_active = 1;
                $discount->save();

                $discount->discount_types()->delete();

                if ($request['discount_type'] == 'category') {
                    $disTypes = ['category', 'zone'];
                } elseif ($request['discount_type'] == 'service') {
                    $disTypes = ['service', 'zone'];
                } elseif ($request['discount_type'] == 'mixed') {
                    $disTypes = ['category', 'service', 'zone'];
                }

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
        }

        Toastr::success(translate(DISCOUNT_UPDATE_200['message']));
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
        $this->authorize('discount_delete');
        $discount = $this->discountQuery->where('id', $id);
        $this->discount_types->where(['discount_id' => $id])->delete();
        $discount->delete();

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
        $this->authorize('discount_manage_status');
        $discount = $this->discountQuery->where('id', $id)->first();
        $this->discountQuery->where('id', $id)->update(['is_active' => !$discount->is_active]);

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return string|StreamedResponse
     */
    public function download(Request $request): string|StreamedResponse
    {
        $this->authorize('discount_export');
        $items = $this->discountQuery->with(['category_types', 'service_types', 'zone_types'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('discount_title', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->latest()->get();

        return (new FastExcel($items))->download(time().'-file.xlsx');
    }
}
