<?php

namespace Modules\ProviderManagement\Http\Controllers\Api\V1\Provider;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\BusinessSettingsModule\Entities\PackageSubscriber;
use Modules\BusinessSettingsModule\Entities\PackageSubscriberLimit;
use Modules\CategoryManagement\Entities\Category;
use Modules\ProviderManagement\Entities\SubscribedService;

class ServiceController extends Controller
{
    private $subscribedService, $category;
    private PackageSubscriber $packageSubscriber;
    private PackageSubscriberLimit $packageSubscriberLimit;

    public function __construct(SubscribedService $subscribedService, Category $category, PackageSubscriber $packageSubscriber, PackageSubscriberLimit $packageSubscriberLimit)
    {
        $this->subscribedService = $subscribedService;
        $this->packageSubscriber = $packageSubscriber;
        $this->packageSubscriberLimit = $packageSubscriberLimit;
        $this->category = $category;
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSubscription(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sub_category_id' => 'required|array',
            'sub_category_id.*' => 'uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
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

        $categoryCount = $this->subscribedService->where('provider_id', $providerId)->where('is_subscribed', 1)
            ->count();

        foreach ($request['sub_category_id'] as $id) {
            $subscribedService = $this->subscribedService::where('sub_category_id', $id)->where('provider_id', $request->user()->provider->id)->first();
            if (!$subscribedService) {
                if ($packageSubscriberLimit <= $categoryCount && $packageSubscriber && $isLimit && $isPackageEnded) {
                    return response()->json(response_formatter(CATEGORY_LIMIT_END), 400);
                }

                $subscribedService = new $this->subscribedService;
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
            $subscribedService->sub_category_id = $id;

            $parent = $this->category->where('id', $id)->first();
            if ($parent) {
                $subscribedService->category_id = $parent->parent_id;
            }

            $subscribedService->save();
        }

        return response()->json(response_formatter(DEFAULT_200), 200);
    }
}
