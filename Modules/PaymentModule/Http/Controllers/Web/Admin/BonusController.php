<?php

namespace Modules\PaymentModule\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BusinessSettingsModule\Entities\Translation;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Modules\PaymentModule\Entities\Bonus;
use Rap2hpoutre\FastExcel\FastExcel;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BonusController extends Controller
{
    protected Bonus $bonus;
    use AuthorizesRequests;

    public function __construct(Bonus $bonus)
    {
        $this->bonus = $bonus;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     * @throws AuthorizationException
     */
    public function list(Request $request): Renderable
    {
        $this->authorize('bonus_view');
        $request->validate([
            'status' => 'in:active,inactive,all',
        ]);
        $search = $request->has('search') ? $request['search'] : '';
        $status = $request->has('status') ? $request['status'] : 'all';
        $queryParams = ['search' => $search, 'status' => $status];

        $bonuses = $this->bonus->withoutGlobalScope('translate')
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->where('bonus_title', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($status != 'all', function ($query) use ($request) {
                return $query->ofStatus(($request['status'] == 'active') ? 1 : 0);
            })
            ->latest()->paginate(pagination_limit())->appends($queryParams);

        return View('paymentmodule::admin.bonus.list', compact('bonuses', 'status', 'search'));
    }

    /**
     * Create resource.
     * @return Renderable
     * @throws AuthorizationException
     */
    public function create(): Renderable
    {
        $this->authorize('bonus_add');
        $type = 'offline_payment';
        return View('paymentmodule::admin.bonus.create', compact('type'));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('bonus_add');
        $request->validate([
            'bonus_title' => 'required',
            'bonus_title.0' => 'required',
            'short_description' => 'required',
            'short_description.0' => 'required',
            'bonus_amount_type' => 'required|in:percent,amount',
            'bonus_amount' => [
                'required', 'gt:0',
                function ($attribute, $value, $fail) use ($request) {
                    $amountType = $request->input('amount_type');
                    if ($amountType === 'percent' && $value > 100) {
                        $fail('The bonus amount percent value must be less than or equal 100 ');
                    }
                },
            ],
            'minimum_add_amount' => 'required|numeric|gt:0',
            'maximum_bonus_amount' => $request['bonus_amount_type'] == 'percent' ? 'required|numeric|gt:0' : '',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
        ], [
            'bonus_title.0.required' => translate('default_bonus_title_is_required'),
            'short_description.0.required' => translate('default_short_description_is_required'),
        ]);

        $bonus = $this->bonus;
        $bonus->bonus_title = $request->bonus_title[array_search('default', $request->lang)];
        $bonus->short_description = $request->short_description[array_search('default', $request->lang)];
        $bonus->bonus_amount_type = $request['bonus_amount_type'];
        $bonus->bonus_amount = $request['bonus_amount'];
        $bonus->minimum_add_amount = $request['minimum_add_amount'];
        $bonus->maximum_bonus_amount = $request['bonus_amount_type'] == 'percent' ? $request['maximum_bonus_amount'] : 0;
        $bonus->start_date = $request['start_date'];
        $bonus->end_date = $request['end_date'];
        $bonus->save();

        $defaultLanguage = str_replace('_', '-', app()->getLocale());

        foreach ($request->lang as $index => $key) {
            if ($defaultLanguage == $key && !($request->bonus_title[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\PaymentModule\Entities\Bonus',
                            'translationable_id' => $bonus->id,
                            'locale' => $key,
                            'key' => 'bonus_title'],
                        ['value' => $bonus->bonus_title]
                    );
                }
            } else {

                if ($request->bonus_title[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\PaymentModule\Entities\Bonus',
                            'translationable_id' => $bonus->id,
                            'locale' => $key,
                            'key' => 'bonus_title'],
                        ['value' => $request->bonus_title[$index]]
                    );
                }
            }

            if ($defaultLanguage == $key && !($request->short_description[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\PaymentModule\Entities\Bonus',
                            'translationable_id' => $bonus->id,
                            'locale' => $key,
                            'key' => 'short_description'],
                        ['value' => $bonus->short_description]
                    );
                }
            } else {

                if ($request->short_description[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\PaymentModule\Entities\Bonus',
                            'translationable_id' => $bonus->id,
                            'locale' => $key,
                            'key' => 'short_description'],
                        ['value' => $request->short_description[$index]]
                    );
                }
            }
        }

        Toastr::success(translate(DEFAULT_STORE_200['message']));
        return redirect()->route('admin.bonus.list');
    }

    /**
     * Edit resource.
     * @param $id
     * @return Renderable
     * @throws AuthorizationException
     */
    public function edit($id): Renderable
    {
        $this->authorize('bonus_update');
        $bonus = $this->bonus->withoutGlobalScope('translate')->find($id);
        return View('paymentmodule::admin.bonus.edit', compact('bonus'));
    }

    /**
     * Update resource.
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, $id)
    {
        $this->authorize('bonus_update');
        $request->validate([
            'bonus_title' => 'required',
            'bonus_title.0' => 'required',
            'short_description' => 'required',
            'short_description.0' => 'required',
            'bonus_amount_type' => 'required|in:percent,amount',
            'bonus_amount' => [
                'required', 'gt:0',
                function ($attribute, $value, $fail) use ($request) {
                    $amountType = $request->input('amount_type');
                    if ($amountType === 'percent' && $value > 100) {
                        $fail('The bonus amount percent value must be less than or equal 100 ');
                    }
                },
            ],
            'minimum_add_amount' => 'required|numeric|gt:0',
            'maximum_bonus_amount' => $request['bonus_amount_type'] == 'percent' ? 'required|numeric|gt:0' : '',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
        ], [
            'bonus_title.0.required' => translate('default_bonus_title_is_required'),
            'short_description.0.required' => translate('default_short_description_is_required'),
        ]);

        $bonus = $this->bonus->withoutGlobalScope('translate')->find($id);

        if (!isset($bonus)) {
            Toastr::error(translate(DEFAULT_404['message']));
            return back();
        }

        $bonus->bonus_title = $request->bonus_title[array_search('default', $request->lang)];
        $bonus->short_description = $request->short_description[array_search('default', $request->lang)];
        $bonus->bonus_amount_type = $request['bonus_amount_type'];
        $bonus->bonus_amount = $request['bonus_amount'];
        $bonus->minimum_add_amount = $request['minimum_add_amount'];
        $bonus->maximum_bonus_amount = $request['bonus_amount_type'] == 'percent' ? $request['maximum_bonus_amount'] : 0;
        $bonus->start_date = $request['start_date'];
        $bonus->end_date = $request['end_date'];
        $bonus->save();

        $defaultLanguage = str_replace('_', '-', app()->getLocale());

        foreach ($request->lang as $index => $key) {
            if ($defaultLanguage == $key && !($request->bonus_title[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\PaymentModule\Entities\Bonus',
                            'translationable_id' => $bonus->id,
                            'locale' => $key,
                            'key' => 'bonus_title'],
                        ['value' => $bonus->bonus_title]
                    );
                }
            } else {

                if ($request->bonus_title[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\PaymentModule\Entities\Bonus',
                            'translationable_id' => $bonus->id,
                            'locale' => $key,
                            'key' => 'bonus_title'],
                        ['value' => $request->bonus_title[$index]]
                    );
                }
            }

            if ($defaultLanguage == $key && !($request->short_description[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\PaymentModule\Entities\Bonus',
                            'translationable_id' => $bonus->id,
                            'locale' => $key,
                            'key' => 'short_description'],
                        ['value' => $bonus->short_description]
                    );
                }
            } else {

                if ($request->short_description[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\PaymentModule\Entities\Bonus',
                            'translationable_id' => $bonus->id,
                            'locale' => $key,
                            'key' => 'short_description'],
                        ['value' => $request->short_description[$index]]
                    );
                }
            }
        }

        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();
    }

    /**
     * Destroy resource.
     * @param $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function destroy($id): RedirectResponse
    {
        $this->authorize('bonus_delete');
        $bonus = $this->bonus->where('id', $id)->withoutGlobalScope('translate')->first();
        $bonus->delete();
        $this->bonus->where('id', $id)->withoutGlobalScope('translate')->delete();
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
        $this->authorize('bonus_manage_status');
        $bonus = $this->bonus->where('id', $id)->withoutGlobalScope('translate')->first();
        $this->bonus->where('id', $id)->withoutGlobalScope('translate')->update(['is_active' => !$bonus->is_active]);
        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return string|StreamedResponse
     */
    public function download(Request $request): string|StreamedResponse
    {
        $this->authorize('bonus_export');
        $items = $this->bonus->withoutGlobalScope('translate')
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->where('bonus_title', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->latest()->get();

        return (new FastExcel($items))->download(time() . '-file.xlsx');
    }

}
