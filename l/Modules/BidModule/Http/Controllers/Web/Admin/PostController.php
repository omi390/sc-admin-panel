<?php

namespace Modules\BidModule\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\BidModule\Entities\Post;
use Modules\BidModule\Entities\PostAdditionalInformation;
use Modules\CategoryManagement\Entities\Category;
use Modules\PromotionManagement\Entities\PushNotification;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PostController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        private Post $post,
        private PostAdditionalInformation $postAdditionalInformation,
        private PushNotification $pushNotification,
        private Category $category
    )
    {
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return RedirectResponse|Renderable
     * @throws ValidationException
     */
    public function index(Request $request): Renderable|RedirectResponse
    {
        $this->authorize('booking_view');

        Validator::make($request->all(), [
            'type' => 'in:all,new_booking_request,placed_offer',
            'category_id' => 'nullable',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'select_date' => 'nullable|string|in:all,today,this_week,this_month,custom_range',
        ])->validate();

        $queryParams = $request->only([
            'search',
            'category_id',
            'start_date',
            'end_date',
            'select_date',
        ]);

        $filterCounter = collect($queryParams)->filter(function ($value) {
            return !is_null($value) && $value !== '';
        })->count();

        $posts = $this->post
            ->with(['bids.provider', 'addition_instructions', 'service', 'category', 'sub_category', 'booking', 'customer'])
            ->where('is_booked', 0)
            ->when($request->has('type') && $request->input('type') != 'new_booking_request' && $request->input('type') != 'all', function ($query) use ($request) {
                $query->whereHas('bids', function ($query) use ($request) {
                    if ($request->input('type') == 'placed_offer') {
                        $query->where('status', 'pending');
                    } elseif ($request->input('type') == 'booking_placed') {
                        $query->where('status', 'accepted');
                    }
                });
            })
            ->when($request->has('type') && $request->input('type') == 'new_booking_request', function ($query) {
                $query->whereDoesntHave('bids');
            })
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request->input('search'));
                return $query->whereHas('customer', function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->where('first_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('last_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('phone', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request->has('category_id') && $request->input('category_id') != null, function ($query) use ($request) {
                $query->where('category_id', $request->input('category_id'));
            })
            ->when($request->input('select_date') == 'custom_range' && $request->has('start_date') && $request->input('start_date') != null && $request->has('end_date') && $request->input('end_date') != null, function ($query) use ($request) {
                $query->whereBetween('created_at', [
                    $request->input('start_date') . ' 00:00:00',
                    $request->input('end_date') . ' 23:59:59'
                ]);
            })
            ->when($request->input('select_date') == 'today', function ($query) {
                $query->whereDate('created_at', today());
            })
            ->when($request->input('select_date') == 'this_week', function ($query) {
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            })
            ->when($request->input('select_date') == 'this_month', function ($query) {
                $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
            })
            ->latest()
            ->paginate(pagination_limit())
            ->appends($queryParams);

        $type = $request->input('type');
        $search = $request->input('search');
        $category_id = $request->input('category_id');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $select_date = $request->input('select_date');

        $categories = $this->category->select('id', 'parent_id', 'name')->where('position', 1)->get();

        $this->post->where('is_checked', 0)->update(['is_checked' => 1]);

        return view('bidmodule::admin.customize-list', compact('posts', 'type', 'search', 'category_id', 'start_date', 'end_date', 'select_date', 'queryParams', 'categories','filterCounter'));
    }

    public function export(Request $request): StreamedResponse|string
    {
        $this->authorize('booking_export');
        Validator::make($request->all(), [
            'type' => 'in:all,new_booking_request,placed_offer',
            'search' => 'max:255'
        ])->validate();

        $posts = $this->post
            ->with(['bids.provider', 'addition_instructions', 'service', 'category', 'sub_category', 'booking', 'customer'])
            ->where('is_booked', 0)
            ->when($request->has('type') && $request->input('type') != 'new_booking_request' && $request->input('type') != 'all', function ($query) use ($request) {
                $query->whereHas('bids', function ($query) use ($request) {
                    if ($request->input('type') == 'placed_offer') {
                        $query->where('status', 'pending');
                    } elseif ($request->input('type') == 'booking_placed') {
                        $query->where('status', 'accepted');
                    }
                });
            })
            ->when($request->has('type') && $request->input('type') == 'new_booking_request', function ($query) {
                $query->whereDoesntHave('bids');
            })
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request->input('search'));
                return $query->whereHas('customer', function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->where('first_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('last_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('phone', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request->has('category_id') && $request->input('category_id') != null, function ($query) use ($request) {
                $query->where('category_id', $request->input('category_id'));
            })
            ->when($request->input('select_date') == 'custom_range' && $request->has('start_date') && $request->input('start_date') != null && $request->has('end_date') && $request->input('end_date') != null, function ($query) use ($request) {
                $query->whereBetween('created_at', [
                    $request->input('start_date') . ' 00:00:00',
                    $request->input('end_date') . ' 23:59:59'
                ]);
            })
            ->when($request->input('select_date') == 'today', function ($query) {
                $query->whereDate('created_at', today());
            })
            ->when($request->input('select_date') == 'this_week', function ($query) {
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            })
            ->when($request->input('select_date') == 'this_month', function ($query) {
                $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
            })
            ->latest()
            ->get();

        return (new FastExcel($posts))->download(time() . '-file.xlsx');
    }

    /**
     * Display a listing of the resource.
     * @param $post_id
     * @return RedirectResponse|Renderable
     */
    public function details($post_id): Renderable|RedirectResponse
    {
        $post = $this->post
            ->with(['bids', 'addition_instructions', 'service', 'category', 'sub_category', 'booking', 'customer'])
            ->where('id', $post_id)
            ->first();

        $coordinates = auth()->user()->provider->coordinates ?? null;
        $distance = null;
        if (!is_null($coordinates) && $post->service_address) {
            $distance = get_distance(
                [$coordinates['latitude'] ?? null, $coordinates['longitude'] ?? null],
                [$post->service_address?->lat, $post->service_address?->lon]
            );
            $distance = ($distance) ? number_format($distance, 2) . ' km' : null;
        }

        if (!isset($post)) {
            Toastr::success(translate(DEFAULT_404['message']));
            return back();
        }

        return view('bidmodule::admin.details', compact('post', 'distance'));
    }

    /**
     * Display a listing of the resource.
     * @param $post_id
     * @param Request $request
     * @return RedirectResponse
     */
    public function delete($post_id, Request $request): RedirectResponse
    {
        $this->authorize('booking_delete');
        $request->validate([
            'post_delete_note' => 'required|string',
        ]);

        $post = $this->post->where('id', $post_id)->first();

        if (!isset($post)) {
            Toastr::success(translate(DEFAULT_404['message']));
            return redirect()->route('admin.booking.post.list');
        }

        $additionalInfo = new $this->postAdditionalInformation;
        $additionalInfo->post_id = $post->id;
        $additionalInfo->key = 'post_delete_note';
        $additionalInfo->value = $request->post_delete_note;
        $additionalInfo->save();

        $pushNotification = $this->pushNotification;
        $pushNotification->title = translate('Your post has been deleted');
        $pushNotification->description = $additionalInfo->value;
        $pushNotification->to_users = ['customer'];
        $pushNotification->zone_ids = [];
        $pushNotification->is_active = 1;
        $pushNotification->save();

        $customer = $post?->customer;
        $fcmToken = $customer?->fcm_token ?? null;
        $languageKey = $customer?->current_language_key;
        $permission = isNotificationActive(null, 'booking', 'notification', 'user');
        if (!is_null($fcmToken) && $permission) {
            $title = get_push_notification_message('customized_booking_request_delete', 'customer_notification', $languageKey);
            device_notification($fcmToken, $title, null, null, null, 'general');
        }

        $post->delete();

        Toastr::success(translate(DEFAULT_DELETE_200['message']));
        return redirect()->route('admin.booking.post.list');
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function multiDelete(Request $request): JsonResponse
    {
        Validator::make($request->all(), [
            'post_ids' => 'required|array',
            'post_ids.*' => 'uuid',
        ])->validate();

        $deletedPosts = $this->post->whereIn('id', $request['post_ids'])->get();

        foreach ($deletedPosts as $post) {
            $customer = $post?->customer;
            $fcmToken = $customer?->fcm_token ?? null;

            if (!is_null($fcmToken)) {
                $languageKey = $customer?->current_language_key;
                $title = get_push_notification_message('customized_booking_request_delete', 'customer_notification', $languageKey);
                device_notification($fcmToken, $title, null, null, null, 'bidding');
            }
        }

        $this->post->whereIn('id', $request['post_ids'])->delete();

        return response()->json(response_formatter(DEFAULT_DELETE_200), 200);
    }

}
