<?php

namespace Modules\PaymentModule\Http\Controllers\Api\V1\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\PaymentModule\Entities\OfflinePayment;

class OfflinePaymentController extends Controller
{
    protected OfflinePayment $offlinePayment;

    public function __construct(OfflinePayment $offlinePayment)
    {
        $this->offlinePayment = $offlinePayment;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function getMethods(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $methods = $this->offlinePayment->ofStatus(1)
            ->paginate($request['limit'], ['*'], 'offset', $request['offset']);

        return response()->json(response_formatter(DEFAULT_200, $methods), 200);
    }
}
