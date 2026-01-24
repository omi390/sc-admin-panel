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
use Modules\BusinessSettingsModule\Entities\Translation;
use Modules\CategoryManagement\Entities\Category;
use Modules\PromotionManagement\Entities\Coupon;
use Modules\PromotionManagement\Entities\CouponCustomer;
use Modules\PromotionManagement\Entities\Discount;
use Modules\PromotionManagement\Entities\DiscountType;
use Modules\ServiceManagement\Entities\Service;
use Modules\UserManagement\Entities\User;
use Modules\ZoneManagement\Entities\Zone;
use Ramsey\Uuid\Uuid;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CouponController extends Controller
{
    protected CouponCustomer $couponCustomer;
    protected User $customer;
    protected Zone $zone;
    protected Coupon $coupon;
    protected Category $category;
    protected Service $service;
    protected DiscountType $discountType;
    protected Discount $discount;
    use AuthorizesRequests;

    public function __construct(Coupon $coupon, Discount $discount, DiscountType $discountType, Service $service, Category $category, Zone $zone, User $customer, CouponCustomer $couponCustomer)
    {
        $this->discount = $discount;
        $this->discountQuery = $discount->ofPromotionTypes('coupon');
        $this->coupon = $coupon;
        $this->discountType = $discountType;
        $this->service = $service;
        $this->category = $category;
        $this->zone = $zone;
        $this->customer = $customer;
        $this->couponCustomer = $couponCustomer;
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Factory|View|Application
     * @throws AuthorizationException
     */
    public function index(Request $request): Factory|View|Application
    {
        $this->authorize('coupon_view');
        $search = $request->has('search') ? $request['search'] : '';
        $discountType = $request->has('discount_type') ? $request['discount_type'] : 'all';
        $queryParam = ['search' => $search, 'discount_type' => $discountType];

        $coupons = $this->coupon->with(['discount', 'discount.category_types', 'discount.service_types', 'discount.zone_types'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('coupon_code', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request->has('coupon_type') && $request['coupon_type'] != 'all', function ($query) use ($request) {
                return $query->where(['coupon_type' => $request['coupon_type']]);
            })->when($request->has('discount_type') && $request['discount_type'] != 'all', function ($query) use ($request) {
                return $query->whereHas('discount', function ($query) use ($request) {
                    $query->where(['discount_type' => $request['discount_type']]);
                });
            })->latest()->paginate(pagination_limit())->appends($queryParam);

        return view('promotionmanagement::admin.coupons.list', compact('coupons', 'search', 'discountType'));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function create(Request $request): View|Factory|Application
    {
        $this->authorize('coupon_add');
        $categories = $this->category->ofStatus(1)->ofType('main')->latest()->get();
        $zones = $this->zone->ofStatus(1)->latest()->get();
        $services = $this->service->active()->latest()->get();
        $customers = $this->customer->ofType(CUSTOMER_USER_TYPES)->ofStatus(1)->get();

        return view('promotionmanagement::admin.coupons.create', compact('categories', 'zones', 'services', 'customers'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('coupon_add');
        $request->validate([
            'coupon_code' => 'required|unique:coupons',
            'discount_type' => 'required|in:category,service,zone,mixed',
            'discount_title' => 'required',
            'discount_title.0' => 'required',
            'coupon_type' => 'required|in:' . implode(',', array_keys(COUPON_TYPES)),
            'discount_amount' => 'required|numeric',
            'discount_amount_type' => 'required|in:percent,amount',
            'min_purchase' => 'required|numeric',
            'max_discount_amount' => $request['discount_amount_type'] == 'amount' ? '' : 'required' . '|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'limit_per_user' => $request['coupon_type'] != 'first_booking' ? 'numeric' : '',

            'customer_user_ids' => $request['coupon_type'] == 'customer_wise' ? 'required|array' : '',
            'customer_user_ids.*' => $request['coupon_type'] == 'customer_wise' ? 'uuid' : ''
        ]);

        DB::transaction(function () use ($request) {
            $discount = $this->discount;
            $discount->discount_type = $request['discount_type'];
            $discount->discount_title = $request->discount_title[array_search('default', $request->lang)];
            $discount->discount_amount = $request['discount_amount'];
            $discount->discount_amount_type = $request['discount_amount_type'];
            $discount->min_purchase = $request['min_purchase'];
            $discount->max_discount_amount = !is_null($request['max_discount_amount']) ? $request['max_discount_amount'] : 0;
            $discount->limit_per_user = $request['coupon_type'] != 'first_booking' ? $request['limit_per_user'] : 1;
            $discount->promotion_type = 'coupon';
            $discount->start_date = $request['start_date'];
            $discount->end_date = $request['end_date'];
            $discount->is_active = 1;
            $discount->save();

            $coupon = $this->coupon;
            $coupon->coupon_code = $request['coupon_code'];
            $coupon->coupon_type = $request['coupon_type'];
            $coupon->discount_id = $discount['id'];
            $coupon->is_active = 1;
            $coupon->save();

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

            if ($request->has('customer_user_ids')) {
                $data = [];
                foreach ($request['customer_user_ids'] as $item) {
                    $data[] = [
                        'id' => Uuid::uuid4(),
                        'coupon_id' => $coupon['id'],
                        'customer_user_id' => $item,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                $coupon->coupon_customers()->createmany($data);
            }

            $defaultLang = str_replace('_', '-', app()->getLocale());

            foreach ($request->lang as $index => $key) {
                if ($defaultLang == $key && !($request->discount_title[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'Modules\PromotionManagement\Entities\Discount',
                                'translationable_id' => $discount->id,
                                'locale' => $key,
                                'key' => 'discount_title'],
                            ['value' => $discount->discount_title]
                        );
                    }
                } else {

                    if ($request->discount_title[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'Modules\PromotionManagement\Entities\Discount',
                                'translationable_id' => $discount->id,
                                'locale' => $key,
                                'key' => 'discount_title'],
                            ['value' => $request->discount_title[$index]]
                        );
                    }
                }
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
        $this->authorize('coupon_update');
        $coupon = $this->coupon->with(['discount', 'discount.category_types', 'discount.service_types', 'discount.zone_types', 'coupon_customers'])->where('id', $id)->first();
        $discount = $this->discount->where('id', $coupon->discount_id)->withoutGlobalScope('translate')->first();
        $categories = $this->category->ofStatus(1)->ofType('main')->latest()->get();
        $zones = $this->zone->ofStatus(1)->latest()->get();
        $services = $this->service->active()->latest()->get();
        $customers = $this->customer->ofType(CUSTOMER_USER_TYPES)->ofStatus(1)->get();

        return view('promotionmanagement::admin.coupons.edit', compact('categories', 'zones', 'services', 'coupon', 'customers', 'discount'));
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
        $this->authorize('coupon_update');
        $request->validate([
            'coupon_code' => ['nullable', 'unique:coupons,coupon_code,' . $id . ',id'],
            'discount_type' => 'required|in:category,service,zone,mixed',
            'discount_title' => 'required',
            'discount_title.0' => 'required',
            'coupon_type' => 'required|in:' . implode(',', array_keys(COUPON_TYPES)),
            'discount_amount' => 'required|numeric',
            'discount_amount_type' => 'required|in:percent,amount',
            'min_purchase' => 'required|numeric',
            'max_discount_amount' => $request['discount_amount_type'] == 'amount' ? '' : 'required' . '|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'limit_per_user' => $request['coupon_type'] != 'first_booking' ? 'numeric' : '',
        ]);

        DB::transaction(function () use ($request, $id) {
            $coupon = $this->coupon->where(['id' => $id])->first();
            $coupon->coupon_code = $request['coupon_code'];
            $coupon->coupon_type = $request['coupon_type'];
            $coupon->save();

            $discount = $this->discountQuery->where('id', $coupon['discount_id'])->first();
            $discount->discount_type = $request['discount_type'];
            $discount->discount_title = $request->discount_title[array_search('default', $request->lang)];
            $discount->discount_amount = $request['discount_amount'];
            $discount->discount_amount_type = $request['discount_amount_type'];
            $discount->min_purchase = $request['min_purchase'];
            $discount->max_discount_amount = !is_null($request['max_discount_amount']) ? $request['max_discount_amount'] : 0;
            $discount->limit_per_user = $request['coupon_type'] != 'first_booking' ? $request['limit_per_user'] : 1;
            $discount->promotion_type = 'coupon';
            $discount->start_date = $request['start_date'];
            $discount->end_date = $request['end_date'];
            $discount->is_active = 1;
            $discount->save();

            $this->discountType->where(['discount_id' => $discount['id']])->delete();

            if ($request['discount_type'] == 'service') {
                $disTypes = ['service', 'zone'];
            } elseif ($request['discount_type'] == 'category') {
                $disTypes = ['category', 'zone'];
            } else {
                $disTypes = ['category', 'service', 'zone'];
            }

            foreach ($disTypes as $disType) {
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

            //coupon customers
            if ($request->has('customer_user_ids')) {
                $data = [];
                foreach ($request['customer_user_ids'] as $item) {
                    $data[] = [
                        'id' => Uuid::uuid4(),
                        'coupon_id' => $coupon['id'],
                        'customer_user_id' => $item,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }

                $this->couponCustomer->where('coupon_id', $coupon->id)->delete();
                $coupon->coupon_customers()->createmany($data);
            }

            $defaultLang = str_replace('_', '-', app()->getLocale());

            foreach ($request->lang as $index => $key) {
                if ($defaultLang == $key && !($request->discount_title[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'Modules\PromotionManagement\Entities\Discount',
                                'translationable_id' => $discount->id,
                                'locale' => $key,
                                'key' => 'discount_title'],
                            ['value' => $coupon->discount_title]
                        );
                    }
                } else {

                    if ($request->discount_title[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'Modules\PromotionManagement\Entities\Discount',
                                'translationable_id' => $discount->id,
                                'locale' => $key,
                                'key' => 'discount_title'],
                            ['value' => $request->discount_title[$index]]
                        );
                    }
                }
            }
        });


        Toastr::success(trans(COUPON_UPDATE_200['message']));
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
        $this->authorize('coupon_delete');
        $coupon = $this->coupon->where('id', $id)->first();
        $this->discount->where('id', $coupon['discount_id'])->withoutGlobalScope('translate')->delete();
        $this->discount->where('id', $coupon['discount_id'])->withoutGlobalScope('translate')->delete();
        $this->discountType->where('discount_id', $coupon['discount_id'])->delete();
        $this->coupon->where('id', $id)->delete();
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
        $this->authorize('coupon_manage_status');
        $coupon = $this->coupon->where('id', $id)->first();
        $this->coupon->where('id', $id)->update(['is_active' => !$coupon->is_active]);
        $this->discount->where('id', $coupon->discount_id)->update(['is_active' => !$coupon->is_active]);

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return string|StreamedResponse
     */
    public function download(Request $request): string|StreamedResponse
    {
        $this->authorize('coupon_export');
        $items = $this->coupon->with(['discount', 'discount.category_types', 'discount.service_types', 'discount.zone_types'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('coupon_code', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request->has('coupon_type') && $request['coupon_type'] != 'all', function ($query) use ($request) {
                return $query->where(['coupon_type' => $request['coupon_type']]);
            })->when($request->has('discount_type') && $request['discount_type'] != 'all', function ($query) use ($request) {
                return $query->whereHas('discount', function ($query) use ($request) {
                    $query->where(['discount_type' => $request['discount_type']]);
                });
            })->latest()->get();

        return (new FastExcel($items))->download(time() . '-file.xlsx');
    }
}
