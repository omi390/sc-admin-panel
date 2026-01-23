<?php

namespace Modules\PromotionManagement\Http\Controllers\Api\V1\Customer;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Modules\PromotionManagement\Entities\PushNotification;

class NotificationController extends Controller
{
    private PushNotification $pushNotification;
    private mixed $customer_user_id;
    private bool $is_customer_logged_in;

    public function __construct(PushNotification $pushNotification, Request $request)
    {
        $this->pushNotification = $pushNotification;
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

        $pushNotification = $this->pushNotification->ofStatus(1)
            ->when(!is_null(Config::get('zone_id')), function ($query) {
                $query->whereJsonContains('zone_ids', Config::get('zone_id'));
            })
            ->whereDoesntHave('pushNotificationUser')
            ->orWhereHas('pushNotificationUser', function ($query) {
                $query->where('user_id', $this->customer_user_id);
            })
            ->latest()
            ->where('to_users', 'like', '%"customer"%')
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        return response()->json(response_formatter(DEFAULT_200, $pushNotification), 200);
    }
}
