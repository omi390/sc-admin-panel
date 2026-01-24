<?php

namespace Modules\ServiceManagement\Http\Controllers\Api\V1\Customer;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Modules\ServiceManagement\Entities\FavoriteService;
use Modules\ServiceManagement\Entities\Service;

class FavoriteServiceController extends Controller
{
    private FavoriteService $favoriteService;
    private Service $service;

    public function __construct(FavoriteService $favoriteService, Service $service)
    {
        $this->favoriteService = $favoriteService;
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $services = $this->service->with(['category.zonesBasicInfo', 'variations'])
            ->whereHas('favorites', function($query){
                $query->where('customer_user_id', auth('api')->user()->id);
            })
            ->active()->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        return response()->json(response_formatter(DEFAULT_200, self::variationMapper($services)), 200);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $favorite = $this->favoriteService->where('customer_user_id',$request->user()->id)->where('service_id', $request->service_id)->first();

        if ($favorite){
            $favorite->delete();
            $status = 0;
        }else {
            $favorite = $this->favoriteService;
            $favorite->customer_user_id = $request->user()->id;
            $favorite->service_id = $request->service_id;
            $favorite->save();
            $status = 1;
        }

        if($status){
            return response()->json(response_formatter(SERVICE_ADD_TO_FAVORITE_200,  ['status' => $status]), 200);
        }else{
            return response()->json(response_formatter(SERVICE_REMOVE_FAVORITE_200,  ['status' => $status]), 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request ,$id): JsonResponse
    {
        $favorite = $this->favoriteService->where('customer_user_id',$request->user()->id)->where('service_id',$id)->first();

        if ($favorite){

            $favorite->delete();

            return response()->json(response_formatter(DEFAULT_DELETE_200), 200);
        }

        return response()->json(response_formatter(DEFAULT_404), 400);
    }

    private function variationMapper($services)
    {
        $services->map(function ($service) {
            $service['variations_app_format'] = self::variationsAppFormat($service);
            return $service;
        });
        return $services;
    }

    private function variationsAppFormat($service): array
    {
        $formatting = [];
        $filtered = $service['variations']->where('zone_id', Config::get('zone_id'));
        $formatting['zone_id'] = Config::get('zone_id');
        $formatting['default_price'] = $filtered->first() ? $filtered->first()->price : 0;
        foreach ($filtered as $data) {
            $formatting['zone_wise_variations'][] = [
                'variant_key' => $data['variant_key'],
                'variant_name' => $data['variant'],
                'price' => $data['price']
            ];
        }
        return $formatting;
    }
}
