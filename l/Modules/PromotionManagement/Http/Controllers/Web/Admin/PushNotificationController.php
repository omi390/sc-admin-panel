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
use Illuminate\Support\Facades\Validator;
use Modules\PromotionManagement\Entities\PushNotification;
use Modules\ZoneManagement\Entities\Zone;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PushNotificationController extends Controller
{
    private PushNotification $pushNotification;
    private Zone $zone;

    use AuthorizesRequests;

    public function __construct(PushNotification $pushNotification, Zone $zone)
    {
        $this->pushNotification = $pushNotification;
        $this->zone = $zone;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'string' => 'string',
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'status' => 'required|in:active,inactive,all',
            'to_user_type' => 'required|in:customer,provider,serviceman,all',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $pushNotification = $this->pushNotification
            ->when($request->has('string'), function ($query) use ($request) {
                $keys = explode(' ', base64_decode($request['string']));
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('title', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request->has('status') && $request['status'] != 'all', function ($query) use ($request) {
                return $query->ofStatus(($request['status'] == 'active') ? 1 : 0);
            })->when($request->has('to_user_type') && $request['to_user_type'] != 'all', function ($query) use ($request) {
                return $query->whereJsonContains('to_users', $request['to_user_type']);
            })->orderBy('created_at', 'desc')->paginate(pagination_limit(), ['*'], 'offset', $request['offset'])->withPath('');

        $pushNotification->map(function ($query) {
            $query->zone_ids = $this->zone->select('id', 'name')->whereIn('id', $query->zone_ids)->get();
        });

        return response()->json(response_formatter(DEFAULT_200, $pushNotification), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function create(Request $request): View|Factory|Application
    {
        $this->authorize('push_notification_view');
        $search = $request->has('search') ? $request['search'] : '';
        $toUserType = $request->has('to_user_type') ? $request['to_user_type'] : 'all';
        $queryParam = ['search' => $search, 'to_user_type' => $toUserType];

        $zones = $this->zone->ofStatus(1)->latest()->get();

        $pushNotification = $this->pushNotification
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('title', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request->has('to_user_type') && $request['to_user_type'] != 'all', function ($query) use ($request) {
                return $query->whereJsonContains('to_users', $request['to_user_type']);
            })->orderBy('created_at', 'desc')->paginate(pagination_limit())->appends($queryParam);

        $pushNotification->map(function ($query) {
            $query->zone_ids = $this->zone->select('id', 'name')->whereIn('id', $query->zone_ids)->get();
        });

        return view('promotionmanagement::admin.push-notification.create', compact('zones', 'pushNotification', 'toUserType', 'search'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('push_notification_add');

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'to_users' => 'required|array',
            'to_users.*' => 'in:customer,provider-admin,provider-serviceman,all',
            'zone_ids' => 'required|array',
            'zone_ids.*' => 'uuid',
            'cover_image' => 'required|image|mimes:jpeg,jpg,png,gif|max:10000'
        ]);

        $image_name = file_uploader('push-notification/', 'png', $request->file('cover_image'));

        $permissionsMap = [
            'customer' => true,
            'provider-admin' => true,
            'provider-serviceman' => true
        ];

        $filteredUsers = array_filter(array_map(function($user) use ($permissionsMap) {
            return $permissionsMap[$user] ? $user : null;
        }, $validatedData['to_users']));

        if (in_array('all', $validatedData['to_users'], true)) {
            $filteredUsers = array_keys(array_filter($permissionsMap));
        }

        $this->pushNotification->create([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'to_users' => $filteredUsers,
            'zone_ids' => $validatedData['zone_ids'] ?? [],
            'cover_image' => $image_name,
            'is_active' => 1,
        ]);

        foreach ($filteredUsers as $user_type) {
            foreach ($validatedData['zone_ids'] as $zone_id) {
                topic_notification("{$user_type}-{$zone_id}", $validatedData['title'], $validatedData['description'], $image_name, null, 'general');
            }
        }

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
        $this->authorize('push_notification_update');
        $pushNotification = $this->pushNotification->where('id', $id)->first();
        $zones = $this->zone->ofStatus(1)->latest()->get();
        $zoneArray = [];
        if ($pushNotification->zone_ids != null) {
            foreach ($pushNotification->zone_ids as $id) {
                $zone = $this->zone::select('id', 'name')->find($id);
                if (!is_null($zone)) {
                    $zoneArray[] = $zone;
                }
            }
            $pushNotification->zone_ids = $zoneArray;
        }
        return view('promotionmanagement::admin.push-notification.edit', compact('pushNotification','zones'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $this->authorize('push_notification_update');
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'to_users' => 'required|array',
            'to_users.*' => 'in:customer,provider-admin,provider-serviceman,all',
            'zone_ids' => 'required|array',
            'zone_ids.*' => 'uuid',
            'cover_image' => 'image|mimes:jpeg,jpg,png,gif|max:10000'
        ]);

        $pushNotification = $this->pushNotification->where(['id' => $id])->first();
        $pushNotification->title = $request['title'];
        $pushNotification->description = $request['description'];
        $pushNotification->to_users = $request['to_users'];
        $pushNotification->zone_ids = $request['zone_ids'];

        if ($request->has('cover_image')) {
            $pushNotification->cover_image = file_uploader('push-notification/', 'png', $request->file('cover_image'), $pushNotification->cover_image);
        }

        $pushNotification->save();

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
        $this->authorize('push_notification_delete');
        $pushNotification = $this->pushNotification->where('id', $id)->withoutGlobalScope('translate')->first();
        if (isset($pushNotification)){
            file_remover('push-notification/', $pushNotification['cover_image']);
            $this->pushNotification->where('id', $id)->withoutGlobalScope('translate')->delete();
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
        $this->authorize('push_notification_manage_status');
        $pushNotification = $this->pushNotification->where('id', $id)->first();
        $this->pushNotification->where('id', $id)->update(['is_active' => !$pushNotification->is_active]);

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return string|StreamedResponse
     */
    public function download(Request $request): string|StreamedResponse
    {
        $this->authorize('push_notification_delete');
        $items = $this->pushNotification
            ->when($request->has('string'), function ($query) use ($request) {
                $keys = explode(' ', base64_decode($request['string']));
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('title', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request->has('status') && $request['status'] != 'all', function ($query) use ($request) {
                return $query->ofStatus(($request['status'] == 'active') ? 1 : 0);
            })->when($request->has('to_user_type') && $request['to_user_type'] != 'all', function ($query) use ($request) {
                return $query->whereJsonContains('to_users', $request['to_user_type']);
            })->latest()->get();

        return (new FastExcel($items))->download(time().'-file.xlsx');
    }
}
