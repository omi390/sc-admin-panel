<?php

namespace Modules\ServiceManagement\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\CategoryManagement\Entities\Category;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ServiceManagement\Entities\ServiceRequest;
use Modules\UserManagement\Entities\User;

class ServiceRequestController extends Controller
{
    private ServiceRequest $serviceRequest;
    public function __construct(ServiceRequest $serviceRequest)
    {
       $this->serviceRequest = $serviceRequest;
    }


    /**
     * Display a listing of the resource.
     * @return Application|Factory|View
     */
    public function requestList(Request $request): View|Factory|Application
    {
        $search = $request['search'];
        $requests =$this->serviceRequest->with(['category', 'user.provider'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->whereHas('category', function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->where('name', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->latest()
            ->paginate(pagination_limit());

        return view('servicemanagement::admin.service.request-list', compact('requests', 'search'));
    }

    /**
     * Display a listing of the resource.
     * @return RedirectResponse
     */
    public function updateStatus($id, Request $request): RedirectResponse
    {
        $serviceRequest =$this->serviceRequest->find($id);
        $serviceRequest->status = $request['review_status'] == 1 ? 'approved' : 'denied';
        $serviceRequest->admin_feedback = $request['admin_feedback'];
        $serviceRequest->save();

        if ($serviceRequest->user && $serviceRequest->user->provider) {
            $userInfo = $serviceRequest?->user?->provider;
            $languageKey = $userInfo->owner?->current_language_key;
            if (!is_null($userInfo->owner?->fcm_token)) {
                if ($serviceRequest->status == 'approved') {
                    $dataInfo = [
                        'provider_name' => $userInfo?->company_name
                    ];
                    $title = get_push_notification_message('service_request_approve', 'provider_notification', $languageKey);
                    device_notification($userInfo->owner?->fcm_token, $title, null, null, null, 'service_request', null,null, $dataInfo);
                } elseif ($serviceRequest->status == 'denied') {
                    $dataInfo = [
                        'provider_name' => $userInfo?->company_name
                    ];
                    $title = get_push_notification_message('service_request_deny', 'provider_notification', $languageKey);
                    device_notification($userInfo?->owner?->fcm_token, $title, null, null, null, 'service_request', null, null, $dataInfo);
                }
            }
        }

        Toastr::success(translate(DEFAULT_STORE_200['message']));
        return back();
    }

}
