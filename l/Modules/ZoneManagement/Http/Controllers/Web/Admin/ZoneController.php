<?php

namespace Modules\ZoneManagement\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use Modules\BusinessSettingsModule\Entities\Translation;
use Modules\ZoneManagement\Entities\Zone;
use Rap2hpoutre\FastExcel\FastExcel;
use Stevebauman\Location\Facades\Location;
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ZoneController extends Controller
{
    private Zone $zone;

    use AuthorizesRequests;

    public function __construct(Zone $zone)
    {
        $this->zone = $zone;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function create(Request $request): View|Factory|Application
    {
        $this->authorize('zone_view');
        if (!session()->has('location')) {
            $data = Location::get($request->ip());
            $location = [
                'lat' => $data ? $data->latitude : '23.757989',
                'lng' => $data ? $data->longitude : '90.360587'
            ];
            session()->put('location', $location);
        }
        $search = $request['search'];
        $queryParam = $search ? ['search' => $request['search']] : '';

        $zones = $this->zone
            ->withCount(['providers', 'categories'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('name', 'LIKE', '%' . $key . '%');
                }
            })
            ->withoutGlobalScope('translate')
            ->latest()->paginate(pagination_limit())->appends($queryParam);
        return view('zonemanagement::admin.create', compact('zones', 'search'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('zone_add');
        $request->validate([
            'name' => 'required|unique:zones|max:191',
            'name.0' => 'required',
            'coordinates' => 'required',
        ],
        [
            'name.0.required' => translate('default_name_is_required'),
        ]);

        $value = $request->coordinates;
        foreach (explode('),(', trim($value, '()')) as $index => $singleArray) {
            if ($index == 0) {
                $lastcord = explode(',', $singleArray);
            }
            $coords = explode(',', $singleArray);
            $polygon[] = new Point($coords[0], $coords[1]);
        }
        $polygon[] = new Point($lastcord[0], $lastcord[1]);

        DB::transaction(function () use ($polygon, $request) {
            $zone = $this->zone;
            $zone->name = $request->name[array_search('default', $request->lang)];
            $zone->coordinates = new Polygon([new LineString($polygon)]);
            $zone->save();

            $defaultLang = str_replace('_', '-', app()->getLocale());

            foreach ($request->lang as $index => $key) {
                if ($defaultLang == $key && !($request->name[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'Modules\ZoneManagement\Entities\Zone',
                                'translationable_id' => $zone->id,
                                'locale' => $key,
                                'key' => 'zone_name'
                            ],
                            ['value' => $zone->name]
                        );
                    }
                } else {

                    if ($request->name[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'Modules\ZoneManagement\Entities\Zone',
                                'translationable_id' => $zone->id,
                                'locale' => $key,
                                'key' => 'zone_name'
                            ],
                            ['value' => $request->name[$index]]
                        );
                    }
                }
            }

        });

        Toastr::success(translate(ZONE_STORE_200['message']));

        return back();
    }

    /**
     * Show the specified resource.
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $zone = $this->zone->withoutGlobalScope('translate')->where('id', $id)->first();
        if (isset($zone)) {
            return response()->json(response_formatter(DEFAULT_200, $zone), 200);
        }
        return response()->json(response_formatter(DEFAULT_204, $zone), 204);
    }


    public function edit(string $id)
    {
        $this->authorize('zone_update');
        $zone = Zone::selectRaw("*,ST_AsText(ST_Centroid(`coordinates`)) as center")->withoutGlobalScope('translate')->find($id);

        if (isset($zone)) {
            $currentZone = format_coordinates(json_decode($zone->coordinates[0]->toJson(),true));
            $centerLat = trim(explode(' ', $zone->center)[1], 'POINT()');
            $centerLng = trim(explode(' ', $zone->center)[0], 'POINT()');

            $area = json_decode($zone->coordinates[0]->toJson(),true);
            return view('zonemanagement::admin.edit', compact('zone', 'currentZone', 'centerLat', 'centerLng', 'area'));
        }

        Toastr::error(translate(DEFAULT_204['message']));
        return back();
    }

    public function getActiveZones($id): JsonResponse
    {
        $allZones = Zone::where('id', '<>', $id)->where('is_active', 1)->withoutGlobalScope('translate')->get();
        $allZoneData = [];

        foreach ($allZones as $item) {
            $data = [];
            foreach ($item->coordinates as $coordinate) {
                $data[] = (object)['lat' => $coordinate->lat, 'lng' => $coordinate->lng];
            }
            $allZoneData[] = $data;
        }
        return response()->json($allZoneData, 200);
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
        $this->authorize('zone_manage_status');
        $zone = $this->zone->where('id', $id)->withoutGlobalScope('translate')->first();
        $this->zone->where('id', $id)->withoutGlobalScope('translate')->update(['is_active' => !$zone->is_active]);

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param string $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $this->authorize('zone_update');
        $request->validate([
            'name' => 'required',
            'name.0' => 'required',
            'coordinates' => 'required',
        ],
        [
            'name.0.required' => translate('default_name_is_required'),
        ]);

        $value = $request->coordinates;
        foreach (explode('),(', trim($value, '()')) as $index => $singleArray) {
            if ($index == 0) {
                $lastcord = explode(',', $singleArray);
            }
            $coords = explode(',', $singleArray);
            $polygon[] = new Point($coords[0], $coords[1]);
        }
        $polygon[] = new Point($lastcord[0], $lastcord[1]);

        $zone = $this->zone->where('id', $id)->withoutGlobalScope('translate')->first();

        if (!isset($zone)) {
            Toastr::success(translate(ZONE_404['message']));
            return back();
        }

        $zone->name = $request->name[array_search('default', $request->lang)];
        $zone->coordinates = new Polygon([new LineString($polygon)]);
        $zone->save();

        $defaultLang = str_replace('_', '-', app()->getLocale());

        foreach ($request->lang as $index => $key) {
            if ($defaultLang == $key && !($request->name[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ZoneManagement\Entities\Zone',
                            'translationable_id' => $zone->id,
                            'locale' => $key,
                            'key' => 'zone_name'
                        ],
                        ['value' => $zone->name]
                    );
                }
            } else {

                if ($request->name[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ZoneManagement\Entities\Zone',
                            'translationable_id' => $zone->id,
                            'locale' => $key,
                            'key' => 'zone_name'
                        ],
                        ['value' => $request->name[$index]]
                    );
                }
            }
        }


        Toastr::success(translate(ZONE_UPDATE_200['message']));
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
        $this->authorize('zone_delete');
        $zone = $this->zone->where('id', $id)->withoutGlobalScope('translate')->first();
        $zone->translations()->delete();
        $zone->delete();
        Toastr::success(translate(ZONE_DESTROY_200['message']));
        return back();
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return string|StreamedResponse
     */
    public function download(Request $request): string|StreamedResponse
    {
        $this->authorize('zone_export');
        $items = $this->zone->withoutGlobalScope('translate')->withCount(['providers', 'categories'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('name', 'LIKE', '%' . $key . '%');
                }
            })
            ->latest()->get();
        return (new FastExcel($items))->download(time() . '-file.xlsx');
    }

}
