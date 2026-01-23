<?php

namespace Modules\PromotionManagement\Http\Controllers\Web\Provider;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\BusinessSettingsModule\Entities\Translation;
use Modules\PromotionManagement\Entities\Advertisement;
use Modules\PromotionManagement\Entities\AdvertisementAttachment;
use Modules\PromotionManagement\Entities\AdvertisementNote;
use Modules\PromotionManagement\Entities\AdvertisementSettings;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class AdvertisementsController extends Controller
{
    public function __construct(
        private Advertisement           $advertisement,
        private AdvertisementAttachment $advertisementAttachment,
        private AdvertisementNote       $advertisementNote,
        private AdvertisementSettings   $advertisementSettings
    )
    {
    }

    public function AdsCreate(): Factory|\Illuminate\Foundation\Application|View|Application
    {
        return view('promotionmanagement::provider.advertisements.ads-create');
    }

    /**
     * @param Request $request
     * @return Factory|View|Application
     */
    public function AdsList(Request $request): Factory|View|Application
    {
        $search = $request->has('search') ? $request['search'] : '';
        $status = $request->has('status') ? $request['status'] : 'all';
        $queryParam = ['search' => $search, 'status' => $status];

        $advertisements = $this->advertisement->with(['attachments'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('title', 'LIKE', '%' . $key . '%');
                        $query->orWhere('readable_id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->where('provider_id', Auth::user()->provider->id)
            ->when($request->has('status') && $request['status'] !== 'all', function ($query) use ($request) {
                return $query->when($request['status'] === 'running', function ($query) {
                    return $query->ofRunning();
                }, function ($query) use ($request) {
                    if ($request['status'] === 'expired') {
                        return $query->ofExpired();
                    } elseif ($request['status'] === 'denied') {
                        return $query->whereIn('status', ['denied', 'canceled']);
                    } elseif ($request['status'] === 'approved') {
                        return $query->where('status', 'approved')->where('end_date', '>', Carbon::now())->where('start_date', '>', Carbon::today());
                    } else {
                        return $query->where('status', $request['status']);
                    }
                });
            })
            ->latest()
            ->paginate(pagination_limit())->appends($queryParam);

        return view('promotionmanagement::provider.advertisements.ads-list', compact('advertisements', 'queryParam'));
    }

    public function AdsStore(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validate = $request->validate([
            'title.0' => 'required|string|max:255',
            'description.0' => 'required|string|max:100',
            'type' => 'required|in:video_promotion,profile_promotion',
            'video_attachment' => 'required_if:type,video_promotion|max:50000|mimetypes:video/mp4,video/mkv,video/webm',
            'dates' => 'required',
            'profile_image' => 'required_if:type,profile_promotion|image|max:10000|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'cover_image' => 'required_if:type,profile_promotion|image|max:10000|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key'))
        ], [
            'video_attachment.required_if' => 'The video attachment field is required.',
            'video_attachment.max' => 'The video attachment must not exceed 50MB.',
            'video_attachment.mimetypes' => 'The video attachment must be in MP4, MKV, or WEBM format.',
            'profile_image.required_if' => 'The profile image field is required.',
            'profile_image.image' => 'The profile image must be an image file.',
            'profile_image.max' => 'The profile image must not exceed 10MB.',
            'profile_image.mimes' => 'The profile image must be in one of the following formats: ' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'cover_image.required_if' => 'The cover image field is required.',
            'cover_image.image' => 'The cover image must be an image file.',
            'cover_image.max' => 'The cover image must not exceed 10MB.',
            'cover_image.mimes' => 'The cover image must be in one of the following formats: ' . implode(',', array_column(IMAGEEXTENSION, 'key')),
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
            $advertisement = $this->advertisement;
            $advertisement->readable_id = $this->generateReadableId();
            $advertisement->title = $request->title[array_search('default', $request->lang)];
            $advertisement->description = $request->description[array_search('default', $request->lang)];
            $advertisement->provider_id = Auth::user()->provider->id;
            $advertisement->priority = null;
            $advertisement->type = $request['type'];
            $advertisement->is_paid = 0;
            $advertisement->start_date = $startDate;
            $advertisement->end_date = $endDate;
            $advertisement->status = 'pending';
            $advertisement->save();

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

        Toastr::success(translate(DEFAULT_STORE_200['message']));
        return redirect()->route('provider.advertisements.ads-list', ['status' => 'all'])->with('newItemAdded', true);
    }


    /**
     * @param $id
     * @param Request $request
     * @return Application|Factory|View|\Illuminate\Foundation\Application
     */
    public function details($id, Request $request): Factory|\Illuminate\Foundation\Application|View|Application
    {
        $advertisement = $this->advertisement->with(['provider', 'attachments', 'attachment', 'note', 'translations'])->find($id);

        foreach ($advertisement->attachments as $attachment){
            if($attachment->type == 'provider_cover_image') $advertisement->provider_cover_image_full_path = $attachment->provider_cover_image_full_path;
            if($attachment->type == 'provider_profile_image') $advertisement->provider_profile_image_full_path  = $attachment->provider_profile_image_full_path;
        }
        $advertisement->promotional_video_full_path = $advertisement?->attachment?->promotional_video_full_path;

        unset($advertisement->attachments, $advertisement->attachment);

        return view('promotionmanagement::provider.advertisements.details', compact('advertisement'));
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
     * @param $id
     * @param Request $request
     * @return Application|Factory|\Illuminate\Foundation\Application|View
     */
    public function edit($id, Request $request): Factory|\Illuminate\Foundation\Application|View|Application
    {
        $advertisement = $this->advertisement->with(['provider', 'attachments', 'attachment'])->withoutGlobalScope('translate')->find($id);
        foreach ($advertisement->attachments as $attachment){
            if($attachment->type == 'provider_cover_image') $advertisement->provider_cover_image_full_path = $attachment->provider_cover_image_full_path;
            if($attachment->type == 'provider_profile_image') $advertisement->provider_profile_image_full_path  = $attachment->provider_profile_image_full_path;
        }
        $advertisement->promotional_video_full_path = $advertisement?->attachment?->promotional_video_full_path;
        unset($advertisement->attachments, $advertisement->attachment);

        return view('promotionmanagement::provider.advertisements.edit', compact('advertisement'));
    }

    /**
     * @param $id
     * @param Request $request
     * @return Application|Factory|View|\Illuminate\Foundation\Application
     */
    public function reSubmit($id, Request $request): Factory|View|\Illuminate\Foundation\Application|Application
    {
        $advertisement = $this->advertisement->with(['provider', 'attachments', 'attachment', 'review', 'rating'])->withoutGlobalScope('translate')->find($id);
        foreach ($advertisement->attachments as $attachment){
            if($attachment->type == 'provider_cover_image') $advertisement->provider_cover_image_full_path = $attachment->provider_cover_image_full_path;
            if($attachment->type == 'provider_profile_image') $advertisement->provider_profile_image_full_path  = $attachment->provider_profile_image_full_path;
        }
        $advertisement->promotional_video_full_path = $advertisement?->attachment?->promotional_video_full_path;
        unset($advertisement->attachments, $advertisement->attachment);

        return view('promotionmanagement::provider.advertisements.ads-re-submit', compact('advertisement'));
    }

    /**
     * @param Request $request
     * @param $sourceId
     * @return RedirectResponse
     */
    public function storeReSubmit(Request $request, $sourceId): RedirectResponse
    {
        $request->validate([
            'title.0' => 'required|string|max:255',
            'description.0' => 'required|string|max:100',
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

            $advertisement = $this->advertisement;
            $advertisement->readable_id = $this->generateReadableId();
            $advertisement->title = $request->title[array_search('default', $request->lang)];
            $advertisement->description = $request->description[array_search('default', $request->lang)];
            $advertisement->provider_id = Auth::user()->provider->id;
            $advertisement->priority = null;
            $advertisement->type = $request['type'];
            $advertisement->is_paid = 0;
            $advertisement->start_date = $startDate;
            $advertisement->end_date = $endDate;
            $advertisement->status = 'pending';
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
        return redirect()->route('provider.advertisements.ads-list', ['status' => 'all']);
    }

    private function copyAttachment($attachment): string
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
     * Store a newly created resource in storage.
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'title.0' => 'required|string|max:255',
            'description.0' => 'required|string|max:100',
            'type' => 'required|in:video_promotion,profile_promotion',
            'video_attachment' => 'max:50000|mimetypes:video/mp4,video/mkv,video/webm',
            'dates' => 'required',
            'profile_image' => 'image|max:10000|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'cover_image' => 'image|max:10000|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key'))
        ]);

        $advertisement = $this->advertisement->find($id);

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

            $advertisement->title = $request->title[array_search('default', $request->lang)];
            $advertisement->description = $request->description[array_search('default', $request->lang)];
            $advertisement->type = $request['type'];
            $advertisement->start_date = $startDate;
            $advertisement->end_date = $endDate;
            $advertisement->is_updated = 1;
            $advertisement->status = 'pending';
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

        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return redirect()->route('provider.advertisements.ads-list', ['status' => 'all']);
    }

    public function statusUpdate(Request $request, $id, $status): \Illuminate\Http\RedirectResponse
    {
        $advertisement = $this->advertisement->find($id);
        if ($advertisement) {
            $advertisement->status = $status;
            $advertisement->save();
        }

        if ($request->has('note')) {
            $advertisementNote = $this->advertisementNote;
            $advertisementNote->advertisement_id = $advertisement->id;
            $advertisementNote->type = $status;
            $advertisementNote->note = $request->note;
            $advertisementNote->save();
        }

        Toastr::success(translate(DEFAULT_STATUS_UPDATE_200['message']));
        return back();
    }

    public function destroy(Request $request, $id): \Illuminate\Http\RedirectResponse
    {
        $advertisement = $this->advertisement->with(['attachments'])->where('id', $id)->first();
        if (isset($advertisement)) {
            if ($advertisement->attachments) {
                foreach ($advertisement->attachments as $attachment) {
                    file_remover('advertisement/', $attachment->file_name);
                }
            }
            $this->advertisement->where('id', $id)->delete();
        }
        Toastr::success(translate(DEFAULT_DELETE_200['message']));
        return back();
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return string|StreamedResponse
     */
    public function download(Request $request): string|StreamedResponse
    {
        $items = $this->advertisement->with(['attachments'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('title', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->where('provider_id', Auth::user()->provider->id)
            ->when($request->has('status') && $request['status'] !== 'all', function ($query) use ($request) {
                return $query->when($request['status'] === 'running', function ($query) {
                    return $query->ofRunning();
                }, function ($query) use ($request) {
                    if ($request['status'] === 'expired') {
                        return $query->ofExpired();
                    } elseif ($request['status'] === 'denied') {
                        return $query->whereIn('status', ['denied', 'canceled']);
                    } elseif ($request['status'] === 'approved') {
                        return $query->where('status', 'approved')->where('end_date', '>', Carbon::now())->where('start_date', '>', Carbon::today());
                    } else {
                        return $query->where('status', $request['status']);
                    }
                });
            })
            ->latest()->get();

        return (new FastExcel($items))->download(time() . '-file.xlsx');
    }

}
