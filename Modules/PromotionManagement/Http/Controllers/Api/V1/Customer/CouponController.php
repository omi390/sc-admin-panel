<?php

namespace Modules\PromotionManagement\Http\Controllers\Api\V1\Customer;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\BookingModule\Entities\Booking;
use Modules\CartModule\Entities\Cart;
use Modules\PromotionManagement\Entities\Coupon;
use Modules\PromotionManagement\Entities\CouponCustomer;
use Modules\PromotionManagement\Entities\Discount;
use Modules\PromotionManagement\Entities\DiscountType;
use Modules\ServiceManagement\Entities\Service;

class CouponController extends Controller
{
    protected $discount, $coupon, $discountType, $cart, $service, $couponCustomer, $booking;
    private bool $is_customer_logged_in;
    private mixed $customer_user_id;

    public function __construct(Coupon $coupon, CouponCustomer $couponCustomer, Discount $discount, DiscountType $discountType, Cart $cart, Service $service, Booking $booking, Request $request)
    {
        $this->discount = $discount;
        $this->discountQuery = $discount->ofPromotionTypes('coupon');
        $this->coupon = $coupon;
        $this->couponCustomer = $couponCustomer;
        $this->discountType = $discountType;
        $this->cart = $cart;
        $this->service = $service;
        $this->booking = $booking;

        $this->is_customer_logged_in = (bool)auth('api')->user();
        $this->customer_user_id = $this->is_customer_logged_in ? auth('api')->user()->id : $request['guest_id'];
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $activeCoupons = $this->coupon->with(['discount', 'coupon_customers'])
            ->when(!is_null($request->status), function ($query) use ($request) {
                $query->ofStatus(1);
            })
            ->whereHas('discount', function ($query) {
                $query->where(['promotion_type' => 'coupon'])
                    ->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now())
                    ->where('is_active', 1);
            })
            ->whereHas('discount.discount_types', function ($query) {
                $query->where(['discount_type' => 'zone', 'type_wise_id' => config('zone_id')]);
            })
            ->where(function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                        $query->where('coupon_type', '<>', 'customer_wise')
                            ->orWhereHas('coupon_customers', function ($query) use ($request) {
                                $query->where('customer_user_id', $this->customer_user_id);
                            });
                    })
                    ->orWhereHas('coupon_customers', function ($query) use ($request) {
                        $query->where('customer_user_id', $this->customer_user_id);
                    });
            })
            ->paginate($request->limit, ['*'], 'page', $request->page);

        foreach ($activeCoupons as $key => $coupon) {
            $coupon['is_used'] = Cart::where('customer_id', $this->customer_user_id)
                ->where('coupon_code', $coupon->coupon_code)
                ->exists() ? 1 : 0;
        }

        $expiredCoupons = $this->coupon->with(['discount'])
            ->when(!is_null($request->status), function ($query) use ($request) {
                $query->ofStatus(1);
            })
            ->whereHas('discount', function ($query) {
                $query->where(['promotion_type' => 'coupon'])
                    ->whereDate('end_date', '<', now())
                    ->where('is_active', 1);
            })
            ->whereHas('discount.discount_types', function ($query) {
                $query->where(['discount_type' => 'zone', 'type_wise_id' => config('zone_id')]);
            })->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        return response()->json(response_formatter(DEFAULT_200, ['active_coupons' => $activeCoupons, 'expired_coupons' => $expiredCoupons]), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function applicable(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $couponQuery = $this->coupon
            ->with(['discount.discount_types'])
            ->ofStatus(1)
            ->whereHas('discount', function ($query) {
                $query->where(['promotion_type' => 'coupon'])
                    ->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now())
                    ->where('is_active', 1);
            })
            ->whereHas('discount.discount_types', function ($query) {
                $query->where(['discount_type' => 'zone', 'type_wise_id' => config('zone_id')]);
            })->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        $cartItems = $this->cart->where(['customer_id' => $this->customer_user_id])->get();

        if ($cartItems->count() < 1)  return response()->json(response_formatter(DEFAULT_404, []), 404);

        $typeWiseId = [];
        foreach ($cartItems as $item) {
            $typeWiseId['service_ids'][] = $item['service_id'];
            $typeWiseId['category_ids'][] = $item['category_id'];
        }

        $filteredCoupons = collect([]);
        foreach ($couponQuery as $key=>$coupon) {
            //category wise
            if ($coupon->discount->discount_type == 'category') {
                $categoryIds = collect($coupon->discount->discount_types)->where('discount_type', 'category')->pluck('type_wise_id');
                if (collect($typeWiseId['category_ids'])->intersect($categoryIds)->isNotEmpty()) {
                    $filteredCoupons->push($couponQuery[$key]);
                }
            }

            //service wise
            if ($coupon->discount->discount_type == 'service') {
                $serviceIds = collect($coupon->discount->discount_types)->where('discount_type', 'service')->pluck('type_wise_id');
                if (collect($typeWiseId['service_ids'])->intersect($serviceIds)->isNotEmpty()) {
                    $filteredCoupons->push($couponQuery[$key]);
                }
            }

            //mixed
            if ($coupon->discount->discount_type == 'mixed') {
                $serviceIds = collect($coupon->discount->discount_types)->whereIn('discount_type', ['category', 'service'])->pluck('type_wise_id');
                if (collect(array_merge($typeWiseId['service_ids'], $typeWiseId['category_ids']))->intersect($serviceIds)->isNotEmpty()) {
                    $filteredCoupons->push($couponQuery[$key]);
                }
            }
        }

        foreach ($filteredCoupons as $key => $coupon) {
            $filteredCoupons[$key]['is_used'] = Cart::where('customer_id', $this->customer_user_id)->where('coupon_code', $coupon->coupon_code)->exists() ? 1 : 0;
        }


        if ($filteredCoupons->count() < 1)  return response()->json(response_formatter(DEFAULT_404, []), 404);

        return response()->json(response_formatter(DEFAULT_200, $filteredCoupons), 200);
    }

    /**
     * Show the form for creating a new resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function applyCoupon(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $cartItems = $this->cart->where(['customer_id' => $this->customer_user_id])->get();
        $typeWiseId = [];
        foreach ($cartItems as $item) {
            $typeWiseId['service_ids'][] = $item['service_id'];
            $typeWiseId['category_ids'][] = $item['category_id'];
        }

        //find valid coupons
        $couponQuery = $this->coupon->where(['coupon_code' => $request['coupon_code']])
            ->withoutGlobalScope('zone_wise_data')
            ->with(['discount'])
            ->whereHas('discount', function ($query) {
                $query->where('promotion_type', 'coupon')->where('is_active', 1)
                    ->whereDate('start_date', '<=', now())->whereDate('end_date', '>=', now());
            });

        // Zone, Category & service check
        $zoneCheck = $couponQuery->whereHas('discount.discount_types', function ($query) {
            $query->where(['discount_type' => DISCOUNT_TYPE['zone'], 'type_wise_id' => config('zone_id')]);
        })->exists();
        if (!$zoneCheck) return response()->json(response_formatter(COUPON_NOT_VALID_FOR_ZONE), 200);

        foreach ($couponQuery->with(['discount.discount_types'])->get() as $coupon) {
            //category wise
            if ($coupon->discount->discount_type == 'category') {
                $categoryIds = collect($coupon->discount->discount_types)->where('discount_type', 'category')->pluck('type_wise_id');
                if (collect($typeWiseId['category_ids'])->intersect($categoryIds)->isEmpty()) {
                    return response()->json(response_formatter(COUPON_NOT_VALID_FOR_CATEGORY), 200);
                }
            }

            //service wise
            if ($coupon->discount->discount_type == 'service') {
                $serviceIds = collect($coupon->discount->discount_types)->where('discount_type', 'service')->pluck('type_wise_id');
                if (collect($typeWiseId['service_ids'])->intersect($serviceIds)->isEmpty()) {
                    return response()->json(response_formatter(COUPON_NOT_VALID_FOR_SERVICE), 200);
                }
            }

            //mixed
            if ($coupon->discount->discount_type == 'mixed') {
                $serviceIds = collect($coupon->discount->discount_types)->whereIn('discount_type', ['category', 'service'])->pluck('type_wise_id');

                if (collect(array_merge($typeWiseId['service_ids'], $typeWiseId['category_ids']))->intersect($serviceIds)->isEmpty()) {
                    return response()->json(response_formatter(COUPON_NOT_VALID_FOR_CATEGORY), 200);
                }
            }
        }

        $coupon = $couponQuery->latest()->first();

        $discountedIds = [];
        if (isset($coupon) && isset($coupon->discount) && $coupon->discount->discount_types->count() > 0) {
            $discountedIds = $coupon->discount->discount_types->pluck('type_wise_id')->toArray();
        }

        if (!isset($coupon)) {
            return response()->json(response_formatter(DEFAULT_404), 200);
        }

        //coupon type check
        if ($coupon->coupon_type == 'first_booking') {
            $bookings = $this->booking->where('customer_id', $this->customer_user_id)->count();
            if ($bookings > 1) {
                return response()->json(response_formatter(COUPON_IS_VALID_FOR_FIRST_TIME), 200);
            }
        } else if ($coupon->coupon_type == 'customer_wise') {
            $couponCustomer = $this->couponCustomer
                ->where('coupon_id', $coupon->id)
                ->where('customer_user_id', $this->customer_user_id)
                ->exists();
            if (!$couponCustomer) {
                return response()->json(response_formatter(COUPON_NOT_VALID_FOR_CART), 200);
            }
        }

        //coupon limit check
        $limit = $this->booking
            ->where('customer_id', $this->customer_user_id)
            ->where('coupon_code', $coupon['coupon_code'])
            ->count();

        if ($coupon->coupon_type != 'first_booking' && $limit >= $coupon->discount->limit_per_user) {
            return response()->json(response_formatter(COUPON_NOT_VALID_FOR_CART), 200);
        }

        if ($coupon->coupon_type == 'first_booking') {
            $limit = $this->booking
                ->where('customer_id', $this->customer_user_id)
                ->where('booking_status', '!=', 'canceled')
                ->count();
            if ($limit >= $coupon->discount->limit_per_user) {
                return response()->json(response_formatter(COUPON_NOT_VALID_FOR_CART), 200);
            }
        }

        //apply
        $applied = 0;
        foreach ($cartItems as $item) {
            if (in_array($item->service_id, $discountedIds) || in_array($item->category_id, $discountedIds)) {
                $cartItem = $this->cart->where('id', $item['id'])->first();
                $service = $this->service->find($cartItem['service_id']);

                //calculation
                $couponDiscountAmount = booking_discount_calculator($coupon->discount, $cartItem->service_cost * $cartItem['quantity']);
                $basicDiscount = $cartItem->discount_amount;
                $campaignDiscount = $cartItem->campaign_discount;
                $subtotal = round($cartItem->service_cost * $cartItem['quantity'], 2);
                $applicableDiscount = ($campaignDiscount >= $basicDiscount) ? $campaignDiscount : $basicDiscount;
                $tax = round(((($cartItem->service_cost - $applicableDiscount - $couponDiscountAmount) * $service['tax']) / 100) * $cartItem['quantity'], 2);

                //update carts table
                $cartItem->coupon_discount = $couponDiscountAmount;
                $cartItem->coupon_code = $coupon->coupon_code;
                $cartItem->tax_amount = $tax;
                $cartItem->total_cost = round($subtotal - $applicableDiscount - $couponDiscountAmount + $tax, 2);
                $cartItem->save();
                $applied = 1;
            }
        }

        if ($applied) {
            return response()->json(response_formatter(COUPON_APPLIED_200), 200);
        }
        return response()->json(response_formatter(COUPON_NOT_VALID_FOR_CART), 200);

    }

    /**
     * Show the form for creating a new resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function removeCoupon(Request $request): JsonResponse
    {
        $cartItems = $this->cart->where('customer_id', $this->customer_user_id)->get();
        if (!isset($cartItems)) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        foreach ($cartItems as $cart) {
            $service = $this->service->find($cart['service_id']);

            $basicDiscount = $cart->discount_amount;
            $campaignDiscount = $cart->campaign_discount;
            $subtotal = round($cart->service_cost * $cart['quantity'], 2);
            $applicableDiscount = ($campaignDiscount >= $basicDiscount) ? $campaignDiscount : $basicDiscount;
            $tax = round(((($cart->service_cost - $applicableDiscount) * $service['tax']) / 100) * $cart['quantity'], 2);

            //updated values
            $cart->tax_amount = $tax;
            $cart->total_cost = round($subtotal - $applicableDiscount + $tax, 2);
            $cart->coupon_discount = 0;
            $cart->coupon_code = null;
            $cart->save();
        }

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

}
