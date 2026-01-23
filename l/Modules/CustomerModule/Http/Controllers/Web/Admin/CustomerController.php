<?php

namespace Modules\CustomerModule\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Modules\BookingModule\Entities\Booking;
use Modules\CustomerModule\Emails\CustomerRegistrationMail;
use Modules\ReviewModule\Entities\Review;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Entities\UserAddress;
use Modules\UserManagement\Entities\UserVerification;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CustomerController extends Controller
{
    protected User $user;
    private Booking $booking;
    private Review $review;
    private UserAddress $address;
    private UserVerification $userVerification;

    use AuthorizesRequests;

    public function __construct(Booking $booking, User $user, Review $review, UserAddress $address, UserVerification $userVerification)
    {
        $this->booking = $booking;
        $this->user = $user;
        $this->review = $review;
        $this->address = $address;
        $this->userVerification = $userVerification;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function create(Request $request): View|Factory|Application
    {
        $this->authorize('customer_add');
        return view('customermodule::admin.create');
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function index(Request $request): View|Factory|Application
    {
        $this->authorize('customer_view');
        $search = $request->has('search') ? $request['search'] : '';
        $status = $request->has('status') ? $request['status'] : 'all';
        $queryParam = ['search' => $search, 'status' => $status];

        $customers = $this->user->withCount(['bookings'])->whereIn('user_type', CUSTOMER_USER_TYPES)
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
            ->when($status != 'all', function ($query) use ($request) {
                return $query->ofStatus(($request['status'] == 'active') ? 1 : 0);
            })->latest()->paginate(pagination_limit())->appends($queryParam);

        return view('customermodule::admin.list', compact('customers', 'search', 'status'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('customer_add');
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'password' => 'required|min:6',
            'confirm_password' => 'same:password',
            'gender' => 'in:male,female,others',
            'profile_image' => 'image|mimes:jpeg,jpg,png,gif|max:10000',
        ]);

        if (User::where('email', $request['email'])->exists()) {
            Toastr::error(translate('Email already taken'));
            return back();
        }
        if (User::where('phone', $request['phone'])->exists()) {
            Toastr::error(translate('Phone already taken'));
            return back();
        }

        $password = $request->password;

        $user = $this->user;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->profile_image = $request->has('profile_image') ? file_uploader('user/profile_image/', 'png', $request->profile_image) : 'default.png';
        $user->date_of_birth = $request->date_of_birth;
        $user->gender = $request->gender ?? 'male';
        $user->password = bcrypt($request->password);
        $user->user_type = 'customer';
        $user->is_active = 1;
        $user->save();

        try {
            $otp = env('APP_ENV') != 'live' ? '1234' : rand(1000, 9999);

            $webUrl = business_config('web_url', 'landing_button_and_links');
            $token = base64_encode(json_encode(["identity" => $user->email, "identity_type" => "email", "otp" => $otp, "from_url" => 1]));

            if (str_ends_with($webUrl->live_values, '/')) {
                $url = $webUrl->live_values . 'change-password?token=' . urlencode($token);
            } else {
                $url = $webUrl->live_values . '/change-password?token=' . urlencode($token);
            }
            $regByAdmin = isNotificationActive(null, 'registration', 'email', 'user');
            if ($regByAdmin) {
                Mail::to($user->email)->send(new CustomerRegistrationMail($user, $password, $otp, $url));
            }

            $this->userVerification->updateOrCreate([
                'identity' => $user->email,
                'identity_type' => "email"
            ], [
                'identity' => $user->email,
                'identity_type' => 'email',
                'user_id' => null,
                'otp' => $otp,
                'expires_at' => now()->addMinute(60),
            ]);


        } catch (\Exception $exception) {
            info($exception);
        }

        Toastr::success(translate(REGISTRATION_200['message']));
        return back();
    }

    public function overview(Request $request, string $id): JsonResponse
    {
        $search = $request->has('search') ? $request['search'] : '';
        $webPage = $request->has('web_page') ? 'review' : 'general';
        $queryParam = ['search' => $search, 'web_page' => $webPage];

        $customer = $this->user->where(['id' => $id])->with(['bookings', 'addresses', 'reviews'])->first();
        $totalBookingPlaced = $this->booking->where(['customer_id' => $id])->count();
        $totalBookingAmount = $this->booking->where(['customer_id' => $id])->sum('total_booking_amount');
        $completeBookings = $this->booking->where(['customer_id' => $id, 'booking_status' => 'completed'])->count();
        $canceledBookings = $this->booking->where(['customer_id' => $id, 'booking_status' => 'canceled'])->count();
        $ongoingBookings = $this->booking->where(['customer_id' => $id, 'booking_status' => 'ongoing'])->count();

        $data = [
            'total_booking_placed' => $totalBookingPlaced,
            'total_booking_amount' => $totalBookingAmount,
            'complete_bookings' => $completeBookings,
            'canceled_bookings' => $canceledBookings,
            'ongoing_bookings' => $ongoingBookings,
            'customer_details' => $customer
        ];

        return response()->json(response_formatter(DEFAULT_200, $data), 200);
    }

    public function bookings(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $bookings = $this->booking->with(['provider.owner'])->where(['customer_id' => $id])
            ->when($request->has('string'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $keys = explode(' ', base64_decode($request['string']));
                    foreach ($keys as $key) {
                        $query->orWhere('readable_id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->orderBy('created_at', 'desc')->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        return response()->json(response_formatter(DEFAULT_200, $bookings), 200);
    }

    public function reviews(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $reviews = $this->review->where(['customer_id' => $id])->orderBy('created_at', 'desc')->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        return response()->json(response_formatter(DEFAULT_200, $reviews), 200);
    }

    /**
     * Show the form for editing the specified resource.
     * @param string $id
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function edit(string $id): Application|Factory|View
    {
        $this->authorize('customer_update');
        $customer = $this->user->whereIn('user_type', CUSTOMER_USER_TYPES)->find($id);
        return view('customermodule::admin.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param string $id
     * @return Application|Redirector|RedirectResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, string $id): Redirector|RedirectResponse|Application
    {
        $this->authorize('customer_update');

        $customer = $this->user->whereIn('user_type', CUSTOMER_USER_TYPES)->find($id);

        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'confirm_password' => !is_null($request->password) ? 'required|same:password' : '',
            'gender' => 'in:male,female,others',
            'profile_image' => 'image|mimes:jpeg,jpg,png,gif|max:10000',
        ]);

        if (User::where('email', $request['email'])->where('id', '!=', $customer->id)->exists()) {
            Toastr::error(translate('Email already taken'));
            return back();
        }
        if (User::where('phone', $request['phone'])->where('id', '!=', $customer->id)->exists()) {
            Toastr::error(translate('Phone already taken'));
            return back();
        }

        $customer->first_name = $request->first_name;
        $customer->last_name = $request->last_name;
        $customer->email = $request->email;
        $customer->phone = $request->phone;
        $customer->profile_image = $request->has('profile_image') ? file_uploader('user/profile_image/', 'png', $request->profile_image) : $customer->profile_image;
        $customer->date_of_birth = $request->date_of_birth;
        $customer->gender = $request->has('gender') ? $request->gender : $customer->gender;
        $customer->save();

        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return redirect('admin/customer/list');
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
        $this->authorize('customer_delete');
        $user = $this->user->where('id', $id)->first();
        if (isset($user)) {
            file_remover('user/profile_image/', $user->profile_image);
            foreach ($user->identification_image as $image_name) {
                file_remover('user/identity/', $image_name);
            }
            $user->delete();

            Toastr::success(translate(DEFAULT_DELETE_200['message']));
            return back();
        }
        Toastr::success(translate(DEFAULT_204['message']));
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
        $this->authorize('customer_manage_status');
        $user = $this->user->where('id', $id)->first();
        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function storeAddress(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lat' => '',
            'lon' => '',
            'city' => 'required',
            'street' => '',
            'zip_code' => 'required',
            'country' => 'required',
            'address' => 'required',
            'address_type' => 'required|in:service,billing',
            'contact_person_name' => 'required',
            'contact_person_number' => 'required',
            'address_label' => 'required',
            'customer_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $address = $this->address;
        $address->user_id = $request['customer_id'];
        $address->lat = $request->lat;
        $address->lon = $request->lon;
        $address->city = $request->city;
        $address->street = $request->street ?? '';
        $address->zip_code = $request->zip_code;
        $address->country = $request->country;
        $address->address = $request->address;
        $address->address_type = $request->address_type;
        $address->contact_person_name = $request->contact_person_name;
        $address->contact_person_number = $request->contact_person_number;
        $address->address_label = $request->address_label;
        $address->save();

        return response()->json(response_formatter(DEFAULT_STORE_200), 200);
    }

    /**
     * Show the form for editing the specified resource.
     * @param string $id
     * @return JsonResponse
     */
    public function editAddress(string $id): JsonResponse
    {
        $address = $this->address->where(['user_id' => $id])->where('id', $id)->first();
        if (isset($address)) {
            return response()->json(response_formatter(DEFAULT_200, $address), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function updateAddress(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lat' => '',
            'lon' => '',
            'city' => 'required',
            'street' => '',
            'zip_code' => 'required',
            'country' => 'required',
            'address' => 'required',
            'address_type' => 'required|in:service,billing',
            'contact_person_name' => 'required',
            'contact_person_number' => 'required',
            'address_label' => 'required',
            'customer_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $address = $this->address->where(['user_id' => $request['customer_id']])->where('id', $id)->first();
        if (!isset($address)) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        $address->lat = $request->lat ?? "";
        $address->lon = $request->lon ?? "";
        $address->city = $request->city;
        $address->street = $request->has('street') ? $request->street : $address->street;
        $address->zip_code = $request->zip_code;
        $address->country = $request->country;
        $address->address = $request->address;
        $address->address_type = $request->address_type;
        $address->contact_person_name = $request->contact_person_name;
        $address->contact_person_number = $request->contact_person_number;
        $address->address_label = $request->address_label;
        $address->save();

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function destroyAddress(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $address = $this->address->where(['user_id' => $request['customer_id']])->where('id', $id)->first();
        if (!isset($address)) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }
        $address->delete();
        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return string|StreamedResponse
     */
    public function download(Request $request): string|StreamedResponse
    {
        $this->authorize('customer_export');
        $items = $this->user->withCount(['bookings'])->whereIn('user_type', CUSTOMER_USER_TYPES)
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
            ->latest()->get();
        return (new FastExcel($items))->download(time() . '-file.xlsx');
    }

    public function show($id, Request $request)
    {
        $this->authorize('customer_view');
        $request->validate([
            'web_page' => 'in:overview,bookings,reviews',
        ]);

        $webPage = $request->has('web_page') ? $request['web_page'] : 'overview';

        if ($request->web_page == 'overview') {
            $customer = $this->user->with(['account', 'addresses'])->withCount(['bookings'])->find($id);
            $totalBookingAmount = $this->booking->where('customer_id', $id)->sum('total_booking_amount');

            $booking_overview = DB::table('bookings')->where('customer_id', $id)
                ->select('booking_status', DB::raw('count(*) as total'))
                ->groupBy('booking_status')
                ->get();

            $status = ['pending', 'accepted', 'ongoing', 'completed', 'canceled'];
            $total = [];
            foreach ($status as $item) {
                if ($booking_overview->where('booking_status', $item)->first() !== null) {
                    $total[] = $booking_overview->where('booking_status', $item)->first()->total;
                } else {
                    $total[] = 0;
                }
            }

            return view('customermodule::admin.detail.overview', compact('customer', 'totalBookingAmount', 'webPage', 'total'));

        } elseif ($request->web_page == 'bookings') {

            $search = $request->has('search') ? $request['search'] : '';
            $queryParam = ['web_page' => $webPage, 'search' => $search];

            $bookings = $this->booking->with(['provider.owner'])
                ->where('customer_id', $id)
                ->where(function ($query) use ($request) {
                    $keys = explode(' ', $request['search']);
                    foreach ($keys as $key) {
                        $query->where('readable_id', 'LIKE', '%' . $key . '%');
                    }
                })
                ->latest()
                ->paginate(pagination_limit())->appends($queryParam);

            $customer = $this->user->whereIn('user_type', CUSTOMER_USER_TYPES)->find($id);

            return view('customermodule::admin.detail.bookings', compact('bookings', 'webPage', 'customer', 'search'));

        } elseif ($request->web_page == 'reviews') {
            $search = $request->has('search') ? $request['search'] : '';
            $queryParam = ['web_page' => $webPage];
            $bookingIds = $this->booking->where('customer_id', $id)->pluck('id')->toArray();
            $reviews = $this->review->with(['booking'])
                ->whereIn('booking_id', $bookingIds)
                ->latest()
                ->paginate(pagination_limit())->appends($queryParam);
            $customer = $this->user->whereIn('user_type', CUSTOMER_USER_TYPES)->find($id);
            return view('customermodule::admin.detail.reviews', compact('reviews', 'webPage', 'customer', 'search'));

        }


    }

}
