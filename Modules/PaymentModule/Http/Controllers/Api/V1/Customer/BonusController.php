<?php

namespace Modules\PaymentModule\Http\Controllers\Api\V1\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\PaymentModule\Entities\Bonus;

class BonusController extends Controller
{
    protected Bonus $bonus;

    public function __construct(Bonus $bonus)
    {
        $this->bonus = $bonus;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function getBonuses(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $currentDate = now()->toDateString();

        $bonuses = $this->bonus->ofStatus(1)
            ->whereDate('start_date', '<=', $currentDate)
            ->whereDate('end_date', '>=', $currentDate)
            ->paginate($request['limit'], ['*'], 'offset', $request['offset']);

        return response()->json(response_formatter(DEFAULT_200, $bonuses), 200);
    }

}
