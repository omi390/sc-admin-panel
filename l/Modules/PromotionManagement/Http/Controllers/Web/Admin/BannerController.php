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
use Modules\CategoryManagement\Entities\Category;
use Modules\PromotionManagement\Entities\Banner;
use Modules\ServiceManagement\Entities\Service;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BannerController extends Controller
{
    private Banner $banner;
    private Category $category;
    private Service $service;

    use AuthorizesRequests;

    public function __construct(Banner $banner, Category $category, Service $service)
    {
        $this->banner = $banner;
        $this->category = $category;
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function create(Request $request): View|Factory|Application
    {
        $this->authorize('banner_view');
        $search = $request->has('search') ? $request['search'] : '';
        $resourceType = $request->has('resource_type') ? $request['resource_type'] : 'all';
        $queryParam = ['search' => $search, 'resource_type' => $resourceType];

        $categories = $this->category->ofStatus(1)->ofType('main')->latest()->get();
        $services = $this->service->active()->latest()->get();

        $banners = $this->banner->with(['service', 'category'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('banner_title', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request->has('resource_type') && $request['resource_type'] != 'all', function ($query) use ($request) {
                return $query->where(['resource_type' => $request['resource_type']]);
            })->latest()->paginate(pagination_limit())->appends($queryParam);

        return view('promotionmanagement::admin.promotional-banners.create', compact('banners', 'services', 'categories', 'resourceType', 'search'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('banner_add');
        $request->validate([
            'banner_title' => 'required',
            'service_id' => 'uuid',
            'category_id' => 'uuid',
            'resource_type' => 'required|in:service,category,link',
            'banner_image' => 'required|image|max:10000|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key'))
        ]);

        $banner = $this->banner;
        $banner->banner_title = $request['banner_title'];
        $banner->redirect_link = $request['redirect_link'];
        $banner->resource_type = $request['resource_type'];
        if ($request['resource_type'] != 'link') {
            $resourceId = $request['resource_type'] == 'service' ? $request['service_id'] : $request['category_id'];
        } else {
            $resourceId = null;
        }
        $banner->resource_id = $resourceId;
        $banner->banner_image = file_uploader('banner/', 'png', $request->file('banner_image'));
        $banner->is_active = 1;
        $banner->save();

        Toastr::success(translate(BANNER_CREATE_200['message']));
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
        $this->authorize('banner_update');
        $banner = $this->banner->with(['service', 'category'])->where('id', $id)->first();
        $categories = $this->category->ofStatus(1)->ofType('main')->latest()->get();
        $services = $this->service->active()->latest()->get();

        return view('promotionmanagement::admin.promotional-banners.edit', compact('categories', 'services', 'banner'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $this->authorize('banner_update');
        $request->validate([
            'banner_title' => 'required',
            'resource_type' => 'required|in:service,category,link',
            'service_id' => 'uuid',
            'category_id' => 'uuid',
            'banner_image' => 'image|max:10000|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key'))
        ]);

        $banner = $this->banner->where(['id' => $id])->first();
        $banner->banner_title = $request['banner_title'];
        $banner->redirect_link = $request['redirect_link'];
        $banner->resource_type = $request['resource_type'];
        if ($request['resource_type'] != 'link') {
            $resourceId = $request['resource_type'] == 'service' ? $request['service_id'] : $request['category_id'];
        } else {
            $resourceId = null;
        }
        $banner->resource_id = $resourceId;
        $banner->banner_image = file_uploader('banner/', 'png', $request->file('banner_image'), $banner->banner_image);
        $banner->save();

        Toastr::success(translate(BANNER_UPDATE_200['message']));
        return back();
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function destroy(Request $request, $id): RedirectResponse
    {
        $this->authorize('banner_delete');
        $banner = $this->banner->where('id', $id)->first();

        if (isset($banner)) {
            file_remover('banner/', $banner['banner_image']);
            $this->banner->where('id', $id)->delete();
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
        $this->authorize('banner_manage_status');
        $banner = $this->banner->where('id', $id)->first();
        $this->banner->where('id', $id)->update(['is_active' => !$banner->is_active]);

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return string|StreamedResponse
     */
    public function download(Request $request): string|StreamedResponse
    {
        $this->authorize('banner_export');
        $items = $this->banner->with(['service', 'category'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('banner_title', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request->has('resource_type') && $request['resource_type'] != 'all', function ($query) use ($request) {
                return $query->where(['resource_type' => $request['resource_type']]);
            })->latest()->get();

        return (new FastExcel($items))->download(time() . '-file.xlsx');
    }
}
