<?php

namespace Modules\CategoryManagement\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BusinessSettingsModule\Entities\Translation;
use Modules\CategoryManagement\Entities\Category;
use Modules\ProviderManagement\Entities\SubscribedService;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SubCategoryController extends Controller
{

    private Category $category;
    private SubscribedService $subscribedService;

    use AuthorizesRequests;

    public function __construct(Category $category, SubscribedService $subscribedService)
    {
        $this->category = $category;
        $this->subscribedService = $subscribedService;
    }

    /**
     * Show the form for creating a new resource.
     * @param Request $request
     * @return Renderable
     * @throws AuthorizationException
     */
    public function create(Request $request): Renderable
    {
        $this->authorize('category_view');
        $search = $request->has('search') ? $request['search'] : '';
        $status = $request->has('status') ? $request['status'] : 'all';
        $queryParams = ['search' => $search, 'status' => $status];

        $subCategories = $this->category->withCount('services')->with(['parent'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('name', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($status != 'all', function ($query) use ($request) {
                return $query->ofStatus(($request['status'] == 'active') ? 1 : 0);
            })
            ->ofType('sub')->latest()->paginate(pagination_limit())->appends($queryParams);

        $mainCategories = $this->category->ofType('main')->orderBy('name')->get(['id', 'name']);

        return view('categorymanagement::admin.sub-category.create', compact('subCategories', 'mainCategories', 'status', 'search'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('category_add');
        $request->validate([
            'name' => 'required|unique:categories',
            'name.0' => 'required',
            'parent_id' => 'required|uuid',
            'short_description' => 'required',
            'short_description.0' => 'required',
            'image' => 'required|image|mimes:jpeg,jpg,png,gif|max:10240',
        ],
            [
                'name.0.required' => translate('default_name_is_required'),
                'short_description.0.required' => translate('default_short_description_is_required'),
            ]);

        $category = $this->category;
        $category->name = $request->name[array_search('default', $request->lang)];
        $category->image = file_uploader('category/', 'png', $request->file('image'));
        $category->parent_id = $request['parent_id'];
        $category->position = 2;
        $category->description = $request->short_description[array_search('default', $request->lang)];
        $category->save();

        $defaultLanguage = str_replace('_', '-', app()->getLocale());

        foreach ($request->lang as $index => $key) {
            if ($defaultLanguage == $key && !($request->name[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\CategoryManagement\Entities\Category',
                            'translationable_id' => $category->id,
                            'locale' => $key,
                            'key' => 'name'],
                        ['value' => $category->name]
                    );
                }
            } else {

                if ($request->name[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\CategoryManagement\Entities\Category',
                            'translationable_id' => $category->id,
                            'locale' => $key,
                            'key' => 'name'],
                        ['value' => $request->name[$index]]
                    );
                }
            }

            if ($defaultLanguage == $key && !($request->short_description[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\CategoryManagement\Entities\Category',
                            'translationable_id' => $category->id,
                            'locale' => $key,
                            'key' => 'description'],
                        ['value' => $category->description]
                    );
                }
            } else {

                if ($request->short_description[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\CategoryManagement\Entities\Category',
                            'translationable_id' => $category->id,
                            'locale' => $key,
                            'key' => 'description'],
                        ['value' => $request->short_description[$index]]
                    );
                }
            }
        }

        Toastr::success(translate(CATEGORY_STORE_200['message']));
        return back();
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id): Renderable
    {
        return view('categorymanagement::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param string $id
     * @return Application|Factory|View|RedirectResponse
     * @throws AuthorizationException
     */
    public function edit(string $id): View|Factory|RedirectResponse|Application
    {
        $this->authorize('category_update');
        $subCategory = $this->category->withoutGlobalScope('translate')->ofType('sub')->where('id', $id)->first();
        if (isset($subCategory)) {
            $mainCategories = $this->category->ofType('main')->orderBy('name')->get(['id', 'name']);
            return view('categorymanagement::admin.sub-category.edit', compact('subCategory', 'mainCategories'));
        }
        Toastr::error(translate(DEFAULT_204['message']));
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
        $this->authorize('category_update');
        $request->validate([
            'name' => 'required|unique:categories,name,' . $id,
            'name.0' => 'required',
            'parent_id' => 'required|uuid',
            'short_description' => 'required',
            'short_description.0' => 'required',
        ],
            [
                'name.0.required' => translate('default_name_is_required'),
                'short_description.0.required' => translate('default_short_description_is_required'),
            ]
        );

        $category = $this->category->ofType('sub')->where('id', $id)->first();
        if (!$category) {
            return response()->json(response_formatter(CATEGORY_204), 204);
        }
        $category->name = $request->name[array_search('default', $request->lang)];
        if ($request->has('image')) {
            $category->image = file_uploader('category/', 'png', $request->file('image'), $category->image);
        }
        $category->parent_id = $request['parent_id'];
        $category->position = 2;
        $category->description = $request->short_description[array_search('default', $request->lang)];;
        $category->save();

        $defaultLanguage = str_replace('_', '-', app()->getLocale());

        foreach ($request->lang as $index => $key) {
            if ($defaultLanguage == $key && !($request->name[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\CategoryManagement\Entities\Category',
                            'translationable_id' => $category->id,
                            'locale' => $key,
                            'key' => 'name'],
                        ['value' => $category->name]
                    );
                }
            } else {

                if ($request->name[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\CategoryManagement\Entities\Category',
                            'translationable_id' => $category->id,
                            'locale' => $key,
                            'key' => 'name'],
                        ['value' => $request->name[$index]]
                    );
                }
            }

            if ($defaultLanguage == $key && !($request->short_description[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\CategoryManagement\Entities\Category',
                            'translationable_id' => $category->id,
                            'locale' => $key,
                            'key' => 'description'],
                        ['value' => $category->description]
                    );
                }
            } else {

                if ($request->short_description[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\CategoryManagement\Entities\Category',
                            'translationable_id' => $category->id,
                            'locale' => $key,
                            'key' => 'description'],
                        ['value' => $request->short_description[$index]]
                    );
                }
            }
        }

        Toastr::success(translate(CATEGORY_UPDATE_200['message']));
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
        $this->authorize('category_delete');
        $category = $this->category->where('id', $id)->ofType($this)->first();
        if ($category) {
            file_remover('category/', $category->image);
            DB::transaction(function () use ($category, $id) {
                $category->translations()->delete();
                $category->delete();
                $this->subscribedService->where('sub_category_id', $id)->delete();
            });

            Toastr::success(translate(CATEGORY_DESTROY_200['message']));
            return back();
        }
        Toastr::success(translate(CATEGORY_204['message']));
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
        $this->authorize('category_manage_status');
        $category = $this->category->ofType('sub')->where('id', $id)->first();
        $this->category->where('id', $id)->update(['is_active' => !$category->is_active]);

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return string|StreamedResponse
     */
    public function download(Request $request): string|StreamedResponse
    {
        $this->authorize('category_delete');
        $items = $this->category->withCount('services')->with(['parent'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('name', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->ofType('sub')->latest()->get();

        return (new FastExcel($items))->download(time() . '-file.xlsx');
    }

}
