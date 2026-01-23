<?php

namespace Modules\PromotionManagement\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Modules\BusinessSettingsModule\Entities\Translation;
use Modules\PromotionManagement\Entities\Advertisement;
use Modules\PromotionManagement\Entities\AdvertisementAttachment;
use Modules\PromotionManagement\Entities\AdvertisementNote;
use Modules\PromotionManagement\Entities\AdvertisementSettings;
use Modules\ProviderManagement\Entities\Provider;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AdvertisementsController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private Provider                $provider,
        private Advertisement           $advertisement,
        private AdvertisementAttachment $advertisementAttachment,
        private AdvertisementNote       $advertisementNote,
        private AdvertisementSettings   $advertisementSettings
    )
    {
    }

    public function AdsCreate(): Factory|\Illuminate\Foundation\Application|View|Application
    {
        $this->authorize('advertisement_add');
        $providers = $this->provider->ofApproval(1)->select('id', 'company_name', 'company_phone')->get();
        $maxPriority = $this->advertisement->where('priority', '!=', null)->count()+1;
        return view('promotionmanagement::admin.advertisements.ads-create', compact('providers', 'maxPriority'));
    }

    /**
     * @param Request $request
     * @return Factory|View|Application
     * @throws AuthorizationException
     */
    public function AdsList(Request $request): Factory|View|Application
    {
        $this->authorize('advertisement_view');
        $search = $request->has('search') ? $request['search'] : '';
        $status = $request->has('status') ? $request['status'] : 'all';
        $queryParams = ['search' => $search, 'status' => $status];

        $advertisements = $this->advertisement->with(['provider:id,company_name,company_phone,company_address,company_email,logo', 'attachments', 'attachment'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('title', 'LIKE', '%' . $key . '%')
                            ->orWhere('readable_id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request->has('status') && $request['status'] !== 'all', function ($query) use ($request) {
                return $query->when($request['status'] === 'running', function ($query) {
                    return $query->ofRunning();
                }, function ($query) use ($request) {
                    if ($request['status'] === 'expired') {
                        return $query->where('status', '!=', 'pending')->ofExpired();
                    }elseif($request['status'] === 'denied'){
                        return $query->whereIn('status', ['denied', 'canceled']);
                    }elseif($request['status'] === 'approved'){
                        return $query->where('status', 'approved')->where('end_date', '>', Carbon::now())->where('start_date', '>', Carbon::today());
                    } else {
                        return $query->where('status', $request['status']);
                    }
                });
            })
            ->when($request->has('status') && $request['status'] == 'all', function ($query) use ($request) {
                return $query->where('status', "!=", 'pending');
            })
            ->orderByRaw('ISNULL(priority), priority ASC')
            ->orderBy('created_at')
            ->paginate(pagination_limit())->appends($queryParams);

        return view('promotionmanagement::admin.advertisements.ads-list', compact('advertisements', 'queryParams'));
    }

    public function AdsStore(Request $request): RedirectResponse
    {
        $this->authorize('advertisement_add');

        $request->validate([
            'title.0' => 'required|string|max:255',
            'description.0' => 'required|string|max:100',
            'provider_id' => 'required',
            'priority' => 'required',
            'dates' => 'required',
            'type' => 'required|in:video_promotion,profile_promotion',
            'video_attachment' => 'required_if:type,video_promotion|max:50000|mimetypes:video/mp4,video/mkv,video/webm',
            'profile_image' => 'required_if:type,profile_promotion|image|max:10000|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'cover_image' => 'required_if:type,profile_promotion|image|max:10000|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
        ], [
            'video_attachment.required_if' => 'The video attachment field is required when type is video promotion.',
            'profile_image.required_if' => 'The profile image field is required when type is profile promotion.',
            'cover_image.required_if' => 'The cover image field is required when type is profile promotion.',
        ]);


        $dateRange = $request->dates;
        list($startDate, $endDate) = explode(' - ', $dateRange);
        $startDate = \Carbon\Carbon::createFromFormat('m/d/Y', trim($startDate));
        $endDate = \Carbon\Carbon::createFromFormat('m/d/Y', trim($endDate));

        $startDate = $startDate->startOfDay();
        $endDate = $endDate->endOfDay();

        if ($startDate < \Carbon\Carbon::today()) {
            return redirect()->back()->withErrors(['Start date must be greater than or equal to today']);
        }

        if ($endDate < $startDate) {
            return redirect()->back()->withErrors(['End date must be greater than start date']);
        }

        DB::transaction(function () use ($request, $startDate, $endDate) {
            $newPriority = $request['priority'];
            $this->advertisement->where('priority', '>=', $newPriority)->increment('priority');

            $advertisement = $this->advertisement;
            $advertisement->readable_id = $this->generateReadableId();
            $advertisement->title = $request->title[array_search('default', $request->lang)];
            $advertisement->description = $request->description[array_search('default', $request->lang)];
            $advertisement->provider_id = $request['provider_id'];
            $advertisement->priority = $newPriority;
            $advertisement->type = $request['type'];
            $advertisement->is_paid = 0;
            $advertisement->start_date = $startDate;
            $advertisement->end_date = $endDate;
            $advertisement->status = Auth::user()->user_type == 'super-admin' ? 'approved' : 'pending';
            $advertisement->save();

            if($advertisement->status == 'approved'){
                $provider = $advertisement?->provider?->owner;
                $title = get_push_notification_message('advertisement_created_by_admin', 'provider_notification', $provider->current_language_key);
                $notification = isNotificationActive($advertisement?->provider?->id, 'advertisement', 'notification', 'provider');
                if ($provider->fcm_token && $title && $notification) {
                    device_notification($provider->fcm_token, $title, null, null, null, 'advertisement', null, $provider->id, null, $advertisement->id);
                }
            }

            if ($request->type == 'video_promotion' && $request->has('video_attachment')) {
                $file = $request->file('video_attachment');
                $extension = $file->getClientOriginalExtension();

                $this->advertisementAttachment->create([
                    'advertisement_id' => $advertisement->id,
                    'file_extension_type' => $extension,
                    'file_name' => file_uploader('advertisement/', $extension, $file),
                    'type' => 'promotional_video'
                ]);
            }

            if ($request->type == 'profile_promotion') {
                if ($request->has('profile_image')) {
                    $file = $request->file('profile_image');
                    $extension = $file->getClientOriginalExtension();

                    $this->advertisementAttachment->create([
                        'advertisement_id' => $advertisement->id,
                        'file_extension_type' => $extension,
                        'file_name' => file_uploader('advertisement/', $extension, $file),
                        'type' => 'provider_profile_image'
                    ]);
                }

                if ($request->has('cover_image')) {
                    $file = $request->file('cover_image');
                    $extension = $file->getClientOriginalExtension();

                    $this->advertisementAttachment->create([
                        'advertisement_id' => $advertisement->id,
                        'file_extension_type' => $extension,
                        'file_name' => file_uploader('advertisement/', $extension, $file),
                        'type' => 'provider_cover_image'
                    ]);
                }

                $this->advertisementSettings->create([
                    'advertisement_id' => $advertisement->id,
                    'key' => 'review',
                    'value' => $request->has('review') ? 1 : 0
                ]);

                $this->advertisementSettings->create([
                    'advertisement_id' => $advertisement->id,
                    'key' => 'rating',
                    'value' => $request->has('rating') ? 1 : 0
                ]);
            }

            $defaultLang = str_replace('_', '-', app()->getLocale());

            foreach ($request->lang as $index => $key) {
                if ($defaultLang == $key && !($request->title[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'Modules\PromotionManagement\Entities\Advertisement',
                                'translationable_id' => $advertisement->id,
                                'locale' => $key,
                                'key' => 'title'],
                            ['value' => $advertisement->title]
                        );
                    }
                } else {

                    if ($request->title[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'Modules\PromotionManagement\Entities\Advertisement',
                                'translationable_id' => $advertisement->id,
                                'locale' => $key,
                                'key' => 'title'],
                            ['value' => $request->title[$index]]
                        );
                    }
                }

                if ($defaultLang == $key && !($request->description[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'Modules\PromotionManagement\Entities\Advertisement',
                                'translationable_id' => $advertisement->id,
                                'locale' => $key,
                                'key' => 'description'],
                            ['value' => $advertisement->description]
                        );
                    }
                } else {

                    if ($request->description[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'Modules\PromotionManagement\Entities\Advertisement',
                                'translationable_id' => $advertisement->id,
                                'locale' => $key,
                                'key' => 'description'],
                            ['value' => $request->description[$index]]
                        );
                    }
                }
            }
        });

        return redirect()->route('admin.advertisements.ads-list', ['status' => 'all'])->with('ads-store', true);
    }

    /**
     * @return int
     */
    private function generateReadableId(): int
    {
        do {
            $readableId = rand(1000000000, 9999999999);
            $exists = $this->advertisement->where('readable_id', $readableId)->exists();
        } while ($exists);

        return $readableId;
    }

    /**
     * @param Request $request
     * @return Factory|View|Application
     * @throws AuthorizationException
     */
    public function newAdsRequest(Request $request): Factory|View|Application
    {
        $this->authorize('advertisement_view');
        $search = $request->has('search') ? $request['search'] : '';
        $status = $request->has('status') ? $request['status'] : '';
        $queryParams ['search'] = $search;
        $queryParams['status'] = $status;

        $advertisements = $this->advertisement->with(['provider:id,company_name,company_phone,company_address,company_email,logo', 'attachments', 'attachment'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('title', 'LIKE', '%' . $key . '%')
                            ->orWhere('readable_id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request->status == 'update_request', function($query) use ($request) {
                return $query->where('is_updated', 1);
            })
            ->when($request->status == 'new', function($query) use ($request) {
                return $query->where('is_updated', 0);
            })
            ->when($request->status == 'expired_request', function($query) use ($request) {
                return $query->ofExpired();
            })
            ->where(['status' => 'pending'])
            ->orderBy('priority', 'ASC')
            ->paginate(pagination_limit())->appends($queryParams);

        $advertisementsUpdateCount = $this->advertisement
            ->where('is_updated', 1)
            ->where(['status' => 'pending'])
            ->orderByRaw('ISNULL(priority), priority ASC')
            ->orderBy('created_at')
            ->count();

        $advertisementsNewCount = $this->advertisement
            ->where('is_updated', 0)
            ->where(['status' => 'pending'])
            ->orderByRaw('ISNULL(priority), priority ASC')
            ->orderBy('created_at')
            ->count();

        $advertisementsExpiredCount = $this->advertisement
            ->where(['status' => 'pending'])
            ->ofExpired()
            ->orderBy('priority', 'ASC')
            ->count();

        return view('promotionmanagement::admin.advertisements.new-ads-request', compact('advertisements', 'queryParams', 'advertisementsUpdateCount', 'advertisementsNewCount', 'advertisementsExpiredCount'));
    }


    public function edit($id, Request $request)
    {
        $this->authorize('advertisement_update');
        $advertisement = $this->advertisement->with(['provider', 'attachments', 'attachment', 'review', 'rating'])->withoutGlobalScope('translate')->find($id);

        if (!$advertisement) {
            return redirect()->back()->withErrors(['The advertisement information is not found']);
        }

        foreach ($advertisement->attachments as $attachment){
            if($attachment->type == 'provider_cover_image') $advertisement->provider_cover_image_full_path = $attachment->provider_cover_image_full_path;
            if($attachment->type == 'provider_profile_image') $advertisement->provider_profile_image_full_path  = $attachment->provider_profile_image_full_path;
        }
        $advertisement->promotional_video_full_path = $advertisement?->attachment?->promotional_video_full_path;
        unset($advertisement->attachments, $advertisement->attachment);

        $maxPriority = $this->advertisement->where('priority', '!=', null)->count()+1;
        $providers = $this->provider->ofApproval(1)->select('id', 'company_name', 'company_phone')->get();

        return view('promotionmanagement::admin.advertisements.ads-edit', compact('advertisement', 'maxPriority', 'providers'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, $id)
    {
        $this->authorize('advertisement_update');
        $request->validate([
            'title.0' => 'required|string|max:255',
            'description.0' => 'required|string|max:100',
            'priority' => 'required',
            'dates' => 'required',
            'type' => 'required|in:video_promotion,profile_promotion',
            'video_attachment' => 'max:50000|mimetypes:video/mp4,video/mkv,video/webm',
            'profile_image' => 'image|max:10000|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'cover_image' => 'image|max:10000|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key'))
        ]);

        $advertisement = $this->advertisement->with(['attachment'])->find($id);

        if ($advertisement->type != $request->type) {
            $errorText = []; // Initialize as an empty array correctly

            if ($request->type != 'video_promotion') {
                if (!$request->has('cover_image')) {
                    $errorText[] = translate('The cover image is required');
                }
                if (!$request->has('profile_image')) {
                    $errorText[] = translate('The profile image is required');
                }
            } else {
                if (!$request->has('video_attachment')) {
                    $errorText[] = translate('The video attachment is required');
                }
            }

            // Redirect back with error messages if there are any
            if (!empty($errorText)) {
                return redirect()->back()->withErrors($errorText);
            }
        }

        $dateRange = $request->dates;
        list($startDate, $endDate) = explode(' - ', $dateRange);
        $startDate = \Carbon\Carbon::createFromFormat('m/d/Y', trim($startDate));
        $endDate = \Carbon\Carbon::createFromFormat('m/d/Y', trim($endDate));

        $startDate = $startDate->startOfDay();
        $endDate = $endDate->endOfDay();

        if ($startDate < \Carbon\Carbon::today()) {
            return redirect()->back()->withErrors(['Start date must be greater than or equal to today']);
        }

        if ($endDate < $startDate) {
            return redirect()->back()->withErrors(['End date must be greater than start date']);
        }

        DB::transaction(function () use ($advertisement, $request, $startDate, $endDate) {
            $oldPriority = $advertisement['priority'];
            $newPriority = $request['priority'];
            if ($oldPriority != $newPriority) {

                if ($oldPriority === null) {
                    $adsToShift = Advertisement::where('priority', '>=', $newPriority)
                        ->lockForUpdate() // Lock rows for update
                        ->get();

                    foreach ($adsToShift as $ad) {
                        $ad->priority += 1;
                        $ad->save();
                    }

                } else if ($newPriority !== null && $newPriority != $oldPriority) {
                    if ($newPriority < $oldPriority) {
                        $adsToShift = Advertisement::whereBetween('priority', [$newPriority, $oldPriority - 1])
                            ->lockForUpdate()
                            ->get();

                        foreach ($adsToShift as $ad) {
                            $ad->priority += 1;
                            $ad->save();
                        }
                    } else if ($newPriority > $oldPriority) {
                        $adsToShift = Advertisement::whereBetween('priority', [$oldPriority + 1, $newPriority])
                            ->lockForUpdate()
                            ->get();

                        foreach ($adsToShift as $ad) {
                            $ad->priority -= 1;
                            $ad->save();
                        }
                    }
                }
            }

            $advertisement->title = $request->title[array_search('default', $request->lang)];
            $advertisement->description = $request->description[array_search('default', $request->lang)];
            $advertisement->priority = $newPriority;
            $advertisement->type = $request['type'];
            $advertisement->start_date = $startDate;
            $advertisement->end_date = $endDate;
            $advertisement->status = $advertisement->status == 'pending' ? 'approved' : $advertisement->status;
            $advertisement->update();

            if ($request->has('video_attachment') || $request->has('profile_image') || $request->has('cover_image')) {
                if ($advertisement->attachments && $request->has('cover_image')) {
                    $coverImage = $advertisement?->attachments->where('type', 'provider_cover_image')->first();
                    if ($coverImage) {
                        file_remover('advertisement/', $coverImage?->file_name);
                        $coverImage->delete();
                    }
                }

                if ($advertisement->attachments && $request->has('profile_image')) {
                    $profileImage = $advertisement?->attachments->where('type', 'provider_profile_image')->first();
                    if ($profileImage) {
                        file_remover('advertisement/', $profileImage?->file_name);
                        $profileImage->delete();
                    }
                }

                if ($advertisement->attachment) {
                    file_remover('advertisement/', $advertisement->attachment?->file_name);
                    $advertisement->attachment->delete();
                }

                $advertisement->rating?->delete();
                $advertisement->review?->delete();
            }

            if ($request->type == 'video_promotion' && $request->has('video_attachment')) {
                $file = $request->file('video_attachment');
                $extension = $file->getClientOriginalExtension();

                $this->advertisementAttachment->create([
                    'advertisement_id' => $advertisement->id,
                    'file_extension_type' => $extension,
                    'file_name' => file_uploader('advertisement/', $extension, $file),
                    'type' => 'promotional_video'
                ]);
            }

            if ($request->type == 'profile_promotion') {

                if ($request->has('profile_image')) {
                    $file = $request->file('profile_image');
                    $extension = $file->getClientOriginalExtension();

                    $this->advertisementAttachment->create([
                        'advertisement_id' => $advertisement->id,
                        'file_extension_type' => $extension,
                        'file_name' => file_uploader('advertisement/', $extension, $file),
                        'type' => 'provider_profile_image'
                    ]);
                }

                if ($request->has('cover_image')) {
                    $file = $request->file('cover_image');
                    $extension = $file->getClientOriginalExtension();

                    $this->advertisementAttachment->create([
                        'advertisement_id' => $advertisement->id,
                        'file_extension_type' => $extension,
                        'file_name' => file_uploader('advertisement/', $extension, $file),
                        'type' => 'provider_cover_image'
                    ]);
                }

                $advertisement->rating?->delete();
                $advertisement->review?->delete();

                $this->advertisementSettings->create([
                    'advertisement_id' => $advertisement->id,
                    'key' => 'review',
                    'value' => $request->review == 'on' ? 1 : 0
                ]);

                $this->advertisementSettings->create([
                    'advertisement_id' => $advertisement->id,
                    'key' => 'rating',
                    'value' => $request->rating == 'on' ? 1 : 0
                ]);
            }

            $defaultLang = str_replace('_', '-', app()->getLocale());

            foreach ($request->lang as $index => $key) {
                if ($defaultLang == $key && !($request->title[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'Modules\PromotionManagement\Entities\Advertisement',
                                'translationable_id' => $advertisement->id,
                                'locale' => $key,
                                'key' => 'title'],
                            ['value' => $advertisement->title]
                        );
                    }
                } else {

                    if ($request->title[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'Modules\PromotionManagement\Entities\Advertisement',
                                'translationable_id' => $advertisement->id,
                                'locale' => $key,
                                'key' => 'title'],
                            ['value' => $request->title[$index]]
                        );
                    }
                }

                if ($defaultLang == $key && !($request->description[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'Modules\PromotionManagement\Entities\Advertisement',
                                'translationable_id' => $advertisement->id,
                                'locale' => $key,
                                'key' => 'description'],
                            ['value' => $advertisement->description]
                        );
                    }
                } else {

                    if ($request->description[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'Modules\PromotionManagement\Entities\Advertisement',
                                'translationable_id' => $advertisement->id,
                                'locale' => $key,
                                'key' => 'description'],
                            ['value' => $request->description[$index]]
                        );
                    }
                }
            }

        });

        Toastr::success(translate(DEFAULT_STORE_200['message']));
        return back();
    }

    /**
     * Display a listing of the resource.
     * @param $id
     * @param Request $request
     * @return Renderable|RedirectResponse
     * @throws AuthorizationException
     */
    public function details($id, Request $request): Renderable|RedirectResponse
    {
        $this->authorize('advertisement_view');
        $advertisement = $this->advertisement->with(['provider', 'attachment', 'attachments'])->withoutGlobalScope('translate')->find($id);

        foreach ($advertisement->attachments as $attachment){
            if($attachment->type == 'provider_cover_image') $advertisement->provider_cover_image_full_path = $attachment->provider_cover_image_full_path;
            if($attachment->type == 'provider_profile_image') $advertisement->provider_profile_image_full_path  = $attachment->provider_profile_image_full_path;
        }
        $advertisement->promotional_video_full_path = $advertisement?->attachment?->promotional_video_full_path;

        unset($advertisement->attachments, $advertisement->attachment);

        return view('promotionmanagement::admin.advertisements.details', compact('advertisement'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function destroy(Request $request, $id): RedirectResponse
    {
        $this->authorize('advertisement_delete');
        $advertisement = $this->advertisement->with(['attachments'])->where('id', $id)->first();
        if (isset($advertisement)) {
            if ($advertisement->attachments) {
                foreach ($advertisement->attachments as $attachment) {
                    file_remover('advertisement/', $attachment->file_name);
                    $attachment->delete();
                }
            }
            if ($advertisement->attachment) {
                file_remover('advertisement/', $advertisement->attachment?->file_name);
                $advertisement->attachment->delete();
            }

            $advertisement->rating?->delete();
            $advertisement->review?->delete();
            $this->advertisement->where('id', $id)->delete();
        }
        Toastr::success(translate(DEFAULT_DELETE_200['message']));
        return back();
    }

    /**
     * @param Request $request
     * @param $id
     * @param $status
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function statusUpdate(Request $request, $id, $status): RedirectResponse
    {
        $this->authorize('advertisement_manage_status');
        $advertisement = $this->advertisement->find($id);
        if ($advertisement) {
            $advertisement->status = $status;
            $advertisement->save();

            $provider = $advertisement?->provider?->owner;
            $title = '';

            if ($provider) {
                switch ($advertisement->status) {
                    case 'approved':
                        $title = get_push_notification_message('advertisement_approved', 'provider_notification', $provider->current_language_key);
                        break;
                    case 'denied':
                        $title = get_push_notification_message('advertisement_denied', 'provider_notification', $provider->current_language_key);
                        break;
                    case 'resumed':
                        $title = get_push_notification_message('advertisement_resumed', 'provider_notification', $provider->current_language_key);
                        break;
                    case 'paused':
                        $title = get_push_notification_message('advertisement_paused', 'provider_notification', $provider->current_language_key);
                        break;
                }

                $notification = isNotificationActive($advertisement?->provider?->id, 'advertisement', 'notification', 'provider');
                if ($provider->fcm_token && $title && $notification) {
                    device_notification($provider->fcm_token, $title, null, null, null, 'advertisement', null, $provider->id, null, $advertisement->id);
                }
            }

        }

        if ($request->has('note')) {
            $this->advertisementNote->updateOrCreate([
                'advertisement_id' => $advertisement->id,
            ], [
                'type' => $status,
                'note' => $request->note,
            ]);
        }

        Toastr::success(translate(DEFAULT_STATUS_UPDATE_200['message']));
        return back();
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function paymentUpdate(Request $request, $id): RedirectResponse
    {
        $this->authorize('advertisement_manage_status');
        Validator::make($request->all(), [
            'payment_status' => 'required|in:1,0',
        ]);

        $advertisement = $this->advertisement->find($id);
        if ($advertisement) {
            $advertisement->is_paid = $request->payment_status;
            $advertisement->save();
        }
        Toastr::success(translate(DEFAULT_STATUS_UPDATE_200['message']));
        return back();
    }

    public function datesUpdate(Request $request, $id): RedirectResponse
    {
        $this->authorize('advertisement_update');
        Validator::make($request->all(), [
            'dates' => 'required',
        ]);

        $dateRange = $request->dates;
        list($startDate, $endDate) = explode(' - ', $dateRange);
        $startDate = \Carbon\Carbon::createFromFormat('m/d/Y', trim($startDate));
        $endDate = \Carbon\Carbon::createFromFormat('m/d/Y', trim($endDate));

        $startDate = $startDate->startOfDay();
        $endDate = $endDate->endOfDay();

        if ($startDate < \Carbon\Carbon::today()) {
            return redirect()->back()->withErrors(['Start date must be greater than or equal to today']);
        }

        if ($endDate < $startDate) {
            return redirect()->back()->withErrors(['End date must be greater than start date']);
        }

        $advertisement = $this->advertisement->find($id);

        if ($advertisement) {
            $advertisement->start_date = $startDate;
            $advertisement->end_date = $endDate;
            $advertisement->save();
            Toastr::success(translate(DEFAULT_UPDATE_200['message']));
            return back();
        }

        Toastr::error(translate(DEFAULT_204['message']));
        return back();
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function setPriority(Request $request, $id): RedirectResponse
    {
        $this->authorize('advertisement_manage_status');
        Validator::make($request->all(), [
            'priority' => 'required',
        ]);

        $selectedAdvertisement = $this->advertisement->findOrFail($id);
        $newPriority = $request->priority;
        $oldPriority = $selectedAdvertisement->priority;

        DB::transaction(function () use ($selectedAdvertisement, $newPriority, $oldPriority) {
            if ($oldPriority === null) {
                $adsToShift = $this->advertisement
                    ->where('priority', '>=', $newPriority)
                    ->lockForUpdate()
                    ->get();

                foreach ($adsToShift as $ad) {
                    $ad->priority += 1;
                    $ad->save();
                }

                $selectedAdvertisement->priority = $newPriority;
            } else if ($newPriority !== null && $newPriority != $oldPriority) {
                if ($newPriority < $oldPriority) {
                    $adsToShift = $this->advertisement
                        ->whereBetween('priority', [$newPriority, $oldPriority - 1])
                        ->lockForUpdate()
                        ->get();

                    foreach ($adsToShift as $ad) {
                        $ad->priority += 1;
                        $ad->save();
                    }
                } else if ($newPriority > $oldPriority) {
                    $adsToShift = $this->advertisement
                        ->whereBetween('priority', [$oldPriority + 1, $newPriority])
                        ->lockForUpdate() // Lock rows for update
                        ->get();

                    foreach ($adsToShift as $ad) {
                        $ad->priority -= 1;
                        $ad->save();
                    }
                }
                $selectedAdvertisement->priority = $newPriority;
            }

            $selectedAdvertisement->save();
        });

        Toastr::success(translate(DEFAULT_STATUS_UPDATE_200['message']));
        return back();
    }

    /**
     * @param $id
     * @param Request $request
     * @return Factory|\Illuminate\Foundation\Application|View|Application
     * @throws AuthorizationException
     */
    public function reSubmit($id, Request $request): Factory|\Illuminate\Foundation\Application|View|Application
    {
        $this->authorize('advertisement_update');
        $advertisement = $this->advertisement->with(['provider', 'attachments', 'attachment', 'review', 'rating'])->withoutGlobalScope('translate')->find($id);
        $maxPriority = $this->advertisement->where('priority', '!=', null)->count()+1;
        $providers = $this->provider->ofApproval(1)->select('id', 'company_name', 'company_phone')->get();

        foreach ($advertisement->attachments as $attachment){
            if($attachment->type == 'provider_cover_image') $advertisement->provider_cover_image_full_path = $attachment->provider_cover_image_full_path;
            if($attachment->type == 'provider_profile_image') $advertisement->provider_profile_image_full_path  = $attachment->provider_profile_image_full_path;
        }
        $advertisement->promotional_video_full_path = $advertisement?->attachment?->promotional_video_full_path;
        unset($advertisement->attachments, $advertisement->attachment);

        return view('promotionmanagement::admin.advertisements.ads-re-submit', compact('advertisement', 'maxPriority', 'providers'));
    }

    /**
     * @param Request $request
     * @param $sourceId
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function storeReSubmit(Request $request, $sourceId): RedirectResponse
    {
        $this->authorize('advertisement_update');
        $request->validate([
            'title.0' => 'required|string|max:255',
            'description.0' => 'required|string|max:100',
            'provider_id' => 'required',
            'priority' => 'required',
            'dates' => 'required',
            'type' => 'required|in:video_promotion,profile_promotion',
            'video_attachment' => 'max:50000|mimetypes:video/mp4,video/mkv,video/webm',
            'profile_image' => 'image|max:10000|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'cover_image' => 'image|max:10000|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key'))
        ]);

        $dateRange = $request->dates;
        list($startDate, $endDate) = explode(' - ', $dateRange);
        $startDate = \Carbon\Carbon::createFromFormat('m/d/Y', trim($startDate));
        $endDate = \Carbon\Carbon::createFromFormat('m/d/Y', trim($endDate));

        $startDate = $startDate->startOfDay();
        $endDate = $endDate->endOfDay();

        if ($startDate < \Carbon\Carbon::today()) {
            return redirect()->back()->withErrors(['Start date must be greater than or equal to today']);
        }

        if ($endDate < $startDate) {
            return redirect()->back()->withErrors(['End date must be greater than start date']);
        }

        DB::transaction(function () use ($request, $startDate, $endDate, $sourceId) {
            $newPriority = $request['priority'];
            $this->advertisement->where('priority', '>=', $newPriority)->increment('priority');

            $advertisement = $this->advertisement;
            $advertisement->readable_id = $this->generateReadableId();
            $advertisement->title = $request->title[array_search('default', $request->lang)];
            $advertisement->description = $request->description[array_search('default', $request->lang)];
            $advertisement->provider_id = $request['provider_id'];
            $advertisement->priority = $newPriority;
            $advertisement->type = $request['type'];
            $advertisement->is_paid = 0;
            $advertisement->start_date = $startDate;
            $advertisement->end_date = $endDate;
            $advertisement->status = Auth::user()->user_type == 'super-admin' ? 'approved' : 'pending';
            $advertisement->save();

            $sourceAdvertisement = Advertisement::with(['attachments', 'attachment', 'review', 'rating'])->find($sourceId);

            if ($request->type == 'video_promotion') {
                if ($request->has('video_attachment')) {
                    $file = $request->file('video_attachment');
                    $extension = $file->getClientOriginalExtension();

                    $this->advertisementAttachment->create([
                        'advertisement_id' => $advertisement->id,
                        'file_extension_type' => $extension,
                        'file_name' => file_uploader('advertisement/', $extension, $file),
                        'type' => 'promotional_video'
                    ]);
                } else {
                    $sourceVideoAttachment = $sourceAdvertisement?->attachment;
                    if ($sourceVideoAttachment) {
                        $newFileName = $this->copyAttachment($sourceVideoAttachment);

                        $this->advertisementAttachment->create([
                            'advertisement_id' => $advertisement->id,
                            'file_extension_type' => $sourceVideoAttachment->file_extension_type,
                            'file_name' => $newFileName,
                            'type' => 'promotional_video'
                        ]);
                    }
                }
            }

            if ($request->type == 'profile_promotion') {
                if ($request->has('profile_image')) {
                    $file = $request->file('profile_image');
                    $extension = $file->getClientOriginalExtension();

                    $this->advertisementAttachment->create([
                        'advertisement_id' => $advertisement->id,
                        'file_extension_type' => $extension,
                        'file_name' => file_uploader('advertisement/', $extension, $file),
                        'type' => 'provider_profile_image'
                    ]);
                } else {
                    $sourceVideoAttachment = $sourceAdvertisement?->attachments->where('type', 'provider_profile_image')->first();
                    if ($sourceVideoAttachment) {
                        $newFileName = $this->copyAttachment($sourceVideoAttachment);

                        $this->advertisementAttachment->create([
                            'advertisement_id' => $advertisement->id,
                            'file_extension_type' => $sourceVideoAttachment->file_extension_type,
                            'file_name' => $newFileName,
                            'type' => 'provider_profile_image'
                        ]);
                    }
                }

                if ($request->has('cover_image')) {
                    $file = $request->file('cover_image');
                    $extension = $file->getClientOriginalExtension();

                    $this->advertisementAttachment->create([
                        'advertisement_id' => $advertisement->id,
                        'file_extension_type' => $extension,
                        'file_name' => file_uploader('advertisement/', $extension, $file),
                        'type' => 'provider_cover_image'
                    ]);
                } else {
                    $sourceVideoAttachment = $sourceAdvertisement?->attachments->where('type', 'provider_cover_image')->first();

                    if ($sourceVideoAttachment) {
                        $newFileName = $this->copyAttachment($sourceVideoAttachment);

                        $this->advertisementAttachment->create([
                            'advertisement_id' => $advertisement->id,
                            'file_extension_type' => $sourceVideoAttachment->file_extension_type,
                            'file_name' => $newFileName,
                            'type' => 'provider_cover_image'
                        ]);
                    }
                }

                $this->advertisementSettings->create([
                    'advertisement_id' => $advertisement->id,
                    'key' => 'review',
                    'value' => $request->has('review') ? 1 : 0
                ]);

                $this->advertisementSettings->create([
                    'advertisement_id' => $advertisement->id,
                    'key' => 'rating',
                    'value' => $request->has('rating') ? 1 : 0
                ]);
            }

            $defaultLang = str_replace('_', '-', app()->getLocale());

            foreach ($request->lang as $index => $key) {
                if ($defaultLang == $key && !($request->title[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'Modules\PromotionManagement\Entities\Advertisement',
                                'translationable_id' => $advertisement->id,
                                'locale' => $key,
                                'key' => 'title'],
                            ['value' => $advertisement->title]
                        );
                    }
                } else {

                    if ($request->title[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'Modules\PromotionManagement\Entities\Advertisement',
                                'translationable_id' => $advertisement->id,
                                'locale' => $key,
                                'key' => 'title'],
                            ['value' => $request->title[$index]]
                        );
                    }
                }

                if ($defaultLang == $key && !($request->description[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'Modules\PromotionManagement\Entities\Advertisement',
                                'translationable_id' => $advertisement->id,
                                'locale' => $key,
                                'key' => 'description'],
                            ['value' => $advertisement->description]
                        );
                    }
                } else {

                    if ($request->description[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'Modules\PromotionManagement\Entities\Advertisement',
                                'translationable_id' => $advertisement->id,
                                'locale' => $key,
                                'key' => 'description'],
                            ['value' => $request->description[$index]]
                        );
                    }
                }
            }
        });

        Toastr::success(translate(DEFAULT_STORE_200['message']));
        return redirect()->route('admin.advertisements.ads-list');
    }

    private function copyAttachment($attachment)
    {
        $originalPath = 'advertisement/' . $attachment->file_name;
        $newFileName = Carbon::now()->toDateString() . "-" . uniqid() . "." . $attachment->file_extension_type;
        $newPath = 'advertisement/' . $newFileName;

        if (Storage::disk('public')->exists($originalPath)) {
            Storage::disk('public')->copy($originalPath, $newPath);
        }
        return $newFileName;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return string|StreamedResponse
     */
    public function download(Request $request): string|StreamedResponse
    {
        $this->authorize('advertisement_export');
        $search = $request->has('search') ? $request['search'] : '';
        $status = $request->has('status') ? $request['status'] : 'all';

        $advertisements = $this->advertisement->with(['provider:id,company_name,company_phone,company_address,company_email,logo', 'attachments'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('title', 'LIKE', '%' . $key . '%')
                            ->orWhere('readable_id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request->has('status') && $request['status'] !== 'all', function ($query) use ($request) {
                return $query->when($request['status'] === 'running', function ($query) {
                    return $query->ofRunning();
                }, function ($query) use ($request) {
                    if ($request['status'] === 'expired') {
                        return $query->where('status', '!=', 'pending')->ofExpired();
                    }elseif($request['status'] === 'new'){
                        return $query->where('status', 'pending')->where('is_updated', 0);
                    }elseif($request['status'] === 'update_request'){
                        return $query->where('status', 'pending')->where('is_updated', 1);
                    }elseif($request['status'] === 'expired_request'){
                        return $query->where('status', 'pending')->ofExpired();
                    }elseif($request['status'] === 'approved'){
                        return $query->where('status', 'approved')->where('end_date', '>', Carbon::now())->where('start_date', '>', Carbon::today());
                    } else {
                        return $query->where('status', $request['status']);
                    }
                });
            })
            ->when($request->has('status') && $request['status'] == 'all', function ($query) use ($request) {
                return $query->where('status', "!=", 'pending');
            })
            ->orderBy('priority', 'ASC')
            ->get();

        return (new FastExcel($advertisements))->download(time() . '-file.xlsx');
    }

}
