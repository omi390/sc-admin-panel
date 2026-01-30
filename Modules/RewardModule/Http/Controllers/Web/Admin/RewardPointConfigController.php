<?php

namespace Modules\RewardModule\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\CategoryManagement\Entities\Category;
use Modules\RewardModule\Entities\RewardPointConfig;
use Modules\RewardModule\Entities\RewardPointUsage;
use Modules\ServiceManagement\Entities\Variation;

class RewardPointConfigController extends Controller
{
    public function __construct(
        protected RewardPointConfig $rewardPointConfig,
        protected RewardPointUsage $rewardPointUsage
    ) {}

    /**
     * Display list of reward point configurations.
     */
    public function index(Request $request): Renderable
    {
        $search = $request->get('search', '');
        $isActive = $request->get('is_active', 'all');
        $queryParams = ['search' => $search, 'is_active' => $isActive];

        $configs = $this->rewardPointConfig->with(['serviceVariant.provider:id,company_name', 'serviceVariant.service'])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('serviceVariant', function ($q) use ($search) {
                    $q->where('variant', 'LIKE', '%' . $search . '%')
                        ->orWhereHas('provider', function ($q2) use ($search) {
                            $q2->where('company_name', 'LIKE', '%' . $search . '%');
                        });
                });
            })
            ->when($isActive !== 'all', function ($query) use ($isActive) {
                $query->where('is_active', $isActive === 'active');
            })
            ->whereNotNull('service_variant_id')
            ->latest()
            ->paginate(pagination_limit())
            ->appends($queryParams);

        return view('rewardmodule::admin.config.list', compact('configs', 'search', 'isActive'));
    }

    /**
     * Show form to create / bulk configure reward point configs.
     */
    public function create(): Renderable
    {
        // Fetch all variations with provider and service info
        $variations = Variation::with(['provider:id,company_name', 'service:id,name'])
            ->whereHas('provider')
            ->whereHas('service')
            ->orderBy('variant')
            ->get();
        
        $existingConfigIds = $this->rewardPointConfig->whereNotNull('service_variant_id')
            ->pluck('service_variant_id')
            ->toArray();

        return view('rewardmodule::admin.config.create', compact('variations', 'existingConfigIds'));
    }

    /**
     * Store or update reward point configs for multiple service variants.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'service_variant_ids' => 'required|array',
            'service_variant_ids.*' => 'required|uuid|exists:variations,id',
            'reward_points' => 'required|numeric|min:0',
            'minimum_order_amount' => 'required|numeric|min:0',
            'max_uses' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ], [
            'service_variant_ids.required' => translate('select_at_least_one_service_variant'),
        ]);

        $rewardPoints = (float) $request->reward_points;
        $minimumOrderAmount = (float) $request->minimum_order_amount;
        $maxUses = (int) $request->max_uses;
        $isActive = $request->boolean('is_active', true);
        $serviceVariantIds = $request->service_variant_ids;
        $processed = 0;

        DB::beginTransaction();
        try {
            foreach ($serviceVariantIds as $serviceVariantId) {
                $variation = Variation::find($serviceVariantId);
                if (!$variation) {
                    continue;
                }

                $config = $this->rewardPointConfig->firstOrNew(['service_variant_id' => $serviceVariantId]);
                $config->service_variant_id = $serviceVariantId;
                $config->provider_id = $variation->provider_id;
                $config->sub_category_id = $variation->service?->sub_category_id;
                $config->reward_points = $rewardPoints;
                $config->minimum_order_amount = $minimumOrderAmount;
                $config->max_uses = $maxUses;
                $config->is_active = $isActive;
                if (!$config->exists) {
                    $config->current_uses = 0;
                }
                $config->save();
                $processed++;
            }
            DB::commit();
            Toastr::success(translate('reward_point_configuration_saved_successfully'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Toastr::error(translate('something_went_wrong'));
            return back()->withInput();
        }

        return redirect()->route('admin.reward-point.config.list')->with('processed', $processed);
    }

    /**
     * Show edit form for a single config.
     */
    public function edit(string $id): Renderable|RedirectResponse
    {
        $config = $this->rewardPointConfig->with(['serviceVariant.provider:id,company_name', 'serviceVariant.service'])->find($id);
        if (!$config) {
            Toastr::error(translate('config_not_found'));
            return redirect()->route('admin.reward-point.config.list');
        }

        return view('rewardmodule::admin.config.edit', compact('config'));
    }

    /**
     * Update a single reward point config.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $config = $this->rewardPointConfig->find($id);
        if (!$config) {
            Toastr::error(translate('config_not_found'));
            return redirect()->route('admin.reward-point.config.list');
        }

        $request->validate([
            'reward_points' => 'required|numeric|min:0',
            'minimum_order_amount' => 'required|numeric|min:0',
            'max_uses' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'reset_current_uses' => 'boolean',
        ]);

        $config->reward_points = (float) $request->reward_points;
        $config->minimum_order_amount = (float) $request->minimum_order_amount;
        $config->max_uses = (int) $request->max_uses;
        $config->is_active = $request->boolean('is_active', true);
        if ($request->boolean('reset_current_uses')) {
            $config->current_uses = 0;
        }
        $config->save();

        Toastr::success(translate('reward_point_configuration_updated_successfully'));
        return redirect()->route('admin.reward-point.config.list');
    }

    /**
     * Delete a reward point config.
     */
    public function destroy(string $id): RedirectResponse
    {
        $config = $this->rewardPointConfig->find($id);
        if (!$config) {
            Toastr::error(translate('config_not_found'));
            return redirect()->route('admin.reward-point.config.list');
        }

        $config->delete();
        Toastr::success(translate('reward_point_configuration_deleted_successfully'));
        return redirect()->route('admin.reward-point.config.list');
    }

    /**
     * Reward point usage history.
     */
    public function usage(Request $request): Renderable
    {
        $userId = $request->get('user_id', '');
        $subCategoryId = $request->get('sub_category_id', '');
        $queryParams = ['user_id' => $userId, 'sub_category_id' => $subCategoryId];

        $usages = $this->rewardPointUsage->with(['user', 'serviceVariant.provider:id,company_name', 'serviceVariant.service', 'booking', 'rewardConfig'])
            ->when($userId !== '', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->when($subCategoryId !== '', function ($query) use ($subCategoryId) {
                $query->where('sub_category_id', $subCategoryId);
            })
            ->latest()
            ->paginate(pagination_limit())
            ->appends($queryParams);

        return view('rewardmodule::admin.usage.list', compact('usages', 'userId', 'subCategoryId'));
    }
}
