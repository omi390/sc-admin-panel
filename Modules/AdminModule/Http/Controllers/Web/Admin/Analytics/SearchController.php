<?php

namespace Modules\AdminModule\Http\Controllers\Web\Admin\Analytics;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\CustomerModule\Entities\SearchedData;
use Modules\ServiceManagement\Entities\RecentSearch;
use Modules\UserManagement\Entities\User;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SearchController extends Controller
{
    protected RecentSearch $recentSearch;
    protected SearchedData $searchedData;
    protected User $customer;
    use AuthorizesRequests;

    public function __construct(RecentSearch $recentSearch, SearchedData $searchedData, User $customer)
    {
        $this->recentSearch = $recentSearch;
        $this->searchedData = $searchedData;
        $this->customer = $customer;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function getKeywordSearchAnalytics(Request $request): Renderable
    {
        $this->authorize('analytics_view');
        Validator::make($request->all(), [
            'date_range' => 'in:all_time,this_week,last_week,this_month,last_month,last_15_days,this_year,last_year,last_6_month,this_year_1st_quarter,this_year_2nd_quarter,this_year_3rd_quarter,this_year_4th_quarter',
            'date_range_2' => 'in:all_time,this_week,last_week,this_month,last_month,last_15_days,this_year,last_year,last_6_month,this_year_1st_quarter,this_year_2nd_quarter,this_year_3rd_quarter,this_year_4th_quarter',
        ]);

        $search = $request['search'];
        $queryParams = ['search' => $search];
        if ($request->has('date_range')) {
            $queryParams['date_range'] = $request['date_range'];
            $queryParams['date_range_2'] = $request['date_range_2'];
        }

        $recentSearchCount = $this->recentSearch->withTrashed()
            ->when($request->has('date_range'), function ($query) use ($request) {
                if ($request['date_range'] == 'this_week') {
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);

                } elseif ($request['date_range'] == 'last_week') {
                    $query->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);

                } elseif ($request['date_range'] == 'this_month') {
                    $query->whereMonth('created_at', Carbon::now()->month);

                } elseif ($request['date_range'] == 'last_month') {
                    $query->whereMonth('created_at', Carbon::now()->subMonth()->month);

                } elseif ($request['date_range'] == 'last_15_days') {
                    $query->whereBetween('created_at', [Carbon::now()->subDay(15), Carbon::now()]);

                } elseif ($request['date_range'] == 'this_year') {
                    $query->whereYear('created_at', Carbon::now()->year);

                } elseif ($request['date_range'] == 'last_year') {
                    $query->whereYear('created_at', Carbon::now()->subYear()->year);

                } elseif ($request['date_range'] == 'last_6_month') {
                    $query->whereBetween('created_at', [Carbon::now()->subMonth(6), Carbon::now()]);

                } elseif ($request['date_range'] == 'this_year_1st_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(1)->startOfQuarter(), Carbon::now()->month(1)->endOfQuarter()]);

                } elseif ($request['date_range'] == 'this_year_2nd_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(4)->startOfQuarter(), Carbon::now()->month(4)->endOfQuarter()]);

                } elseif ($request['date_range'] == 'this_year_3rd_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(7)->startOfQuarter(), Carbon::now()->month(7)->endOfQuarter()]);

                } elseif ($request['date_range'] == 'this_year_4th_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(10)->startOfQuarter(), Carbon::now()->month(10)->endOfQuarter()]);
                }
            })
            ->select('keyword', DB::raw('count(*) as count'))
            ->groupBy('keyword')
            ->orderBy('count', 'desc')
            ->take(5)
            ->get();

        $graph_data = ['keyword' => [], 'count' => []];
        foreach ($recentSearchCount as $item) {
            $graph_data['keyword'][] = Str::limit($item['keyword'], 30);
            $graph_data['count'][] = $item['count'];
        }

        $zoneWiseVolumes = $this->searchedData
            ->when($request->has('date_range_2'), function ($query) use ($request) {
                if ($request['date_range_2'] == 'this_week') {
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);

                } elseif ($request['date_range_2'] == 'last_week') {
                    $query->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);

                } elseif ($request['date_range_2'] == 'this_month') {
                    $query->whereMonth('created_at', Carbon::now()->month);

                } elseif ($request['date_range_2'] == 'last_month') {
                    $query->whereMonth('created_at', Carbon::now()->subMonth()->month);

                } elseif ($request['date_range_2'] == 'last_15_days') {
                    $query->whereBetween('created_at', [Carbon::now()->subDay(15), Carbon::now()]);

                } elseif ($request['date_range_2'] == 'this_year') {
                    $query->whereYear('created_at', Carbon::now()->year);

                } elseif ($request['date_range_2'] == 'last_year') {
                    $query->whereYear('created_at', Carbon::now()->subYear()->year);

                } elseif ($request['date_range_2'] == 'last_6_month') {
                    $query->whereBetween('created_at', [Carbon::now()->subMonth(6), Carbon::now()]);

                } elseif ($request['date_range_2'] == 'this_year_1st_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(1)->startOfQuarter(), Carbon::now()->month(1)->endOfQuarter()]);

                } elseif ($request['date_range_2'] == 'this_year_2nd_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(4)->startOfQuarter(), Carbon::now()->month(4)->endOfQuarter()]);

                } elseif ($request['date_range_2'] == 'this_year_3rd_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(7)->startOfQuarter(), Carbon::now()->month(7)->endOfQuarter()]);

                } elseif ($request['date_range_2'] == 'this_year_4th_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(10)->startOfQuarter(), Carbon::now()->month(10)->endOfQuarter()]);
                }
            })
            ->select(
                DB::raw('count(volume) as count'),
                DB::raw('zone_id')
            )
            ->with('zone')
            ->groupBy('zone_id')
            ->get();

        $total = 0;
        foreach ($zoneWiseVolumes as $item) {
            $total += $item['count'];
        }

        $searches = DB::table('searched_data')
            ->select('searched_data.attribute_id',
                DB::raw('SUM(searched_data.volume) AS total_volume'),
                DB::raw('SUM(searched_data.response_data_count) AS total_response_data_count'),
                'recent_searches.keyword')
            ->join('recent_searches', 'searched_data.attribute_id', '=', 'recent_searches.id')
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    return $query->where('recent_searches.keyword', 'like', '%' . $key . '%');
                }
            })
            ->groupBy('searched_data.attribute_id', 'recent_searches.keyword')
            ->paginate(pagination_limit())->appends($queryParams);

        return view('adminmodule::admin.analytics.search.keyword', compact('queryParams', 'graph_data', 'searches', 'search', 'zoneWiseVolumes', 'total'));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     * @throws ValidationException
     */
    public function getCustomerSearchAnalytics(Request $request): Renderable
    {
        $this->authorize('analytics_view');

        Validator::make($request->all(), [
            'date_range' => 'in:all_time,this_week,last_week,this_month,last_month,last_15_days,this_year,last_year,last_6_month,this_year_1st_quarter,this_year_2nd_quarter,this_year_3rd_quarter,this_year_4th_quarter',
            'date_range_2' => 'in:all_time,this_week,last_week,this_month,last_month,last_15_days,this_year,last_year,last_6_month,this_year_1st_quarter,this_year_2nd_quarter,this_year_3rd_quarter,this_year_4th_quarter',
        ])->validate();

        $search = $request['search'];
        $queryParams = ['search' => $search];
        if ($request->has('date_range')) {
            $queryParams['date_range'] = $request['date_range'];
        }
        if ($request->has('date_range_2')) {
            $queryParams['date_range_2'] = $request['date_range_2'];
        }


        $topCustomer = $this->searchedData
            ->when($request->has('date_range'), function ($query) use ($request) {
                if ($request['date_range'] == 'this_week') {
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);

                } elseif ($request['date_range'] == 'last_week') {
                    $query->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);

                } elseif ($request['date_range'] == 'this_month') {
                    $query->whereMonth('created_at', Carbon::now()->month);

                } elseif ($request['date_range'] == 'last_month') {
                    $query->whereMonth('created_at', Carbon::now()->subMonth()->month);

                } elseif ($request['date_range'] == 'last_15_days') {
                    $query->whereBetween('created_at', [Carbon::now()->subDay(15), Carbon::now()]);

                } elseif ($request['date_range'] == 'this_year') {
                    $query->whereYear('created_at', Carbon::now()->year);

                } elseif ($request['date_range'] == 'last_year') {
                    $query->whereYear('created_at', Carbon::now()->subYear()->year);

                } elseif ($request['date_range'] == 'last_6_month') {
                    $query->whereBetween('created_at', [Carbon::now()->subMonth(6), Carbon::now()]);

                } elseif ($request['date_range'] == 'this_year_1st_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(1)->startOfQuarter(), Carbon::now()->month(1)->endOfQuarter()]);

                } elseif ($request['date_range'] == 'this_year_2nd_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(4)->startOfQuarter(), Carbon::now()->month(4)->endOfQuarter()]);

                } elseif ($request['date_range'] == 'this_year_3rd_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(7)->startOfQuarter(), Carbon::now()->month(7)->endOfQuarter()]);

                } elseif ($request['date_range'] == 'this_year_4th_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(10)->startOfQuarter(), Carbon::now()->month(10)->endOfQuarter()]);
                }
            })
            ->select(
                DB::raw('count(volume) as count'),
                DB::raw('user_id')
            )
            ->with('user')
            ->groupBy('user_id')
            ->take(5)
            ->get();

        $graph_data = ['top_customers' => [], 'search_volume' => []];
        foreach ($topCustomer as $item) {
            if ($item->user) {
                $graph_data['top_customers'][] = $item->user->first_name . ' ' . $item->user->last_name;
                $graph_data['search_volume'][] = $item->count;
            }
        }


        $topServices = $this->searchedData
            ->when($request->has('date_range_2'), function ($query) use ($request) {
                if ($request['date_range_2'] == 'this_week') {
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);

                } elseif ($request['date_range_2'] == 'last_week') {
                    $query->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);

                } elseif ($request['date_range_2'] == 'this_month') {
                    $query->whereMonth('created_at', Carbon::now()->month);

                } elseif ($request['date_range_2'] == 'last_month') {
                    $query->whereMonth('created_at', Carbon::now()->subMonth()->month);

                } elseif ($request['date_range_2'] == 'last_15_days') {
                    $query->whereBetween('created_at', [Carbon::now()->subDay(15), Carbon::now()]);

                } elseif ($request['date_range_2'] == 'this_year') {
                    $query->whereYear('created_at', Carbon::now()->year);

                } elseif ($request['date_range_2'] == 'last_year') {
                    $query->whereYear('created_at', Carbon::now()->subYear()->year);

                } elseif ($request['date_range_2'] == 'last_6_month') {
                    $query->whereBetween('created_at', [Carbon::now()->subMonth(6), Carbon::now()]);

                } elseif ($request['date_range_2'] == 'this_year_1st_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(1)->startOfQuarter(), Carbon::now()->month(1)->endOfQuarter()]);

                } elseif ($request['date_range_2'] == 'this_year_2nd_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(4)->startOfQuarter(), Carbon::now()->month(4)->endOfQuarter()]);

                } elseif ($request['date_range_2'] == 'this_year_3rd_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(7)->startOfQuarter(), Carbon::now()->month(7)->endOfQuarter()]);

                } elseif ($request['date_range_2'] == 'this_year_4th_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(10)->startOfQuarter(), Carbon::now()->month(10)->endOfQuarter()]);
                }
            })
            ->select(
                DB::raw('sum(volume) as total_volume'),
                DB::raw('attribute_id')
            )
            ->groupBy('attribute_id')
            ->with(['service'])
            ->where('attribute', 'service')
            ->take(5)
            ->get();

        $total = 0;
        foreach ($topServices as $top_service) {
            $total += $top_service->total_volume;
        }


        $customers = $this->customer->ofType(['customer'])
            ->with(['visited_services', 'added_to_carts'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('first_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('last_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('phone', 'LIKE', '%' . $key . '%')
                            ->orWhere('email', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->leftJoin('searched_data', 'users.id', '=', 'searched_data.user_id')
            ->groupBy('users.id', 'users.user_type')
            ->selectRaw('users.id, users.user_type, sum(searched_data.response_data_count) as total_response_data_count, sum(searched_data.volume) as total_volume')
            ->withCount(['added_to_carts', 'visited_services', 'bookings'])
            ->orderByDesc('bookings_count')
            ->paginate(pagination_limit())->appends($queryParams);

       // dd($customers[0]);

        foreach ($customers as $customer) {
            $visited_count = 0;
            $cart_added_count = 0;
            foreach ($customer->visited_services as $visited_service) {
                $visited_count += $visited_service->count;
            }

            foreach ($customer->added_to_carts as $added_to_cart) {
                $cart_added_count += $added_to_cart->count;
            }

            $customer->total_visited_service_count = $visited_count;
            $customer->total_added_to_cart_count = $cart_added_count;

            $customer->profile_image = User::find($customer->id)?->profile_image;
            $customer->name = User::find($customer->id)?->first_name . ' ' . User::find($customer->id)?->last_name;
        }

        return view('adminmodule::admin.analytics.search.customer', compact('queryParams', 'topCustomer', 'topServices', 'graph_data', 'customers', 'search', 'total'));
    }

}
