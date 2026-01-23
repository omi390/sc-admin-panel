<?php

namespace Modules\PromotionManagement\Http\Controllers\Api\V1\Provider;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Modules\BusinessSettingsModule\Entities\Translation;
use Modules\PromotionManagement\Entities\Advertisement;
use Modules\PromotionManagement\Entities\AdvertisementAttachment;
use Modules\PromotionManagement\Entities\AdvertisementNote;
use Modules\PromotionManagement\Entities\AdvertisementSettings;
use Carbon\Carbon;

class AdvertisementsController extends Controller
{

    public function __construct(
        private Advertisement $advertisement,
        private AdvertisementAttachment $advertisementAttachment,
        private AdvertisementNote $advertisementNote,
        private AdvertisementSettings $advertisementSettings
    )
    {}

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function AdsList(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $advertisements = $this->advertisement->with(['attachments', 'attachment', 'note'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('title', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->where('provider_id', auth('api')->user()->provider->id)
            ->latest()
            ->when($request->has('status') && $request['status'] !== 'all', function ($query) use ($request) {
                return $query->when($request['status'] === 'running', function ($query) {
                    return $query->ofRunning();
                }, function ($query) use ($request) {
                    if ($request['status'] === 'expired') {
                        return $query->ofExpired();
                    }elseif($request['status'] === 'denied'){
                        return $query->whereIn('status', ['denied', 'canceled']);
                    }elseif($request['status'] === 'approved'){
                        return $query->where('status', 'approved')->where('end_date', '>', Carbon::today())->where('start_date', '>', Carbon::today());
                    } else {
                        return $query->where('status', $request['status']);
                    }
                });
            })
            ->withoutGlobalScope('translate')
            ->orderByRaw('ISNULL(priority), priority ASC')
            ->orderBy('created_at')
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        foreach($advertisements as $advertisement){
            foreach ($advertisement->attachments as $attachment){
                if($attachment->type == 'provider_cover_image') $advertisement->provider_cover_image_full_path = $attachment->provider_cover_image_full_path;
                if($attachment->type == 'provider_profile_image') $advertisement->provider_profile_image_full_path  = $attachment->provider_profile_image_full_path;
            }
            $advertisement->promotional_video_full_path = $advertisement?->attachment?->promotional_video_full_path;

            $advertisement->provider_review = $advertisement?->review?->value;
            $advertisement->provider_rating = $advertisement?->rating?->value;
            $advertisement->additional_note = $advertisement->note ? $advertisement->note->note : null;

            unset($advertisement->attachments, $advertisement->attachment, $advertisement->review, $advertisement->rating, $advertisement->note);
        }

        return response()->json(response_formatter(DEFAULT_200, $advertisements), 200);
    }

    public function AdsStore(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title.0' => 'required|string|max:255',
            'description.0' => 'required|string|max:100',
            'type' => 'required|in:video_promotion,profile_promotion',
            'video_attachment' => 'required_if:type,video_promotion',
            'start_date' => 'required|after_or_equal:today',
            'end_date' => 'required|after_or_equal:start_date',
            'profile_image' => 'required_if:type,profile_promotion',
            'cover_image' => 'required_if:type,profile_promotion',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        DB::transaction(function () use ($request) {
            $advertisement = $this->advertisement;
            $advertisement->readable_id = $this->generateReadableId();
            $advertisement->title = $request->title[array_search('default', $request->lang)];
            $advertisement->description = $request->description[array_search('default', $request->lang)];
            $advertisement->provider_id = auth('api')->user()->provider->id;
            $advertisement->priority = null;
            $advertisement->type = $request['type'];
            $advertisement->is_paid = 0;
            $advertisement->start_date = Carbon::parse($request->start_date)->startOfDay();
            $advertisement->end_date = Carbon::parse($request->end_date)->endOfDay();
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


            if ($request->type == 'profile_promotion'){
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

        return response()->json(response_formatter(DEFAULT_STORE_200), 200);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function edit($id): JsonResponse
    {
        $advertisement = $this->advertisement->with(['provider', 'attachments'])->withoutGlobalScope('translate')->find($id);
        if (!isset($advertisement)) {
            return response()->json(response_formatter(DEFAULT_204), 204);
        }

        foreach ($advertisement->attachments as $attachment){
            if($attachment->type == 'provider_cover_image') $advertisement->provider_cover_image_full_path = $attachment->provider_cover_image_full_path;
            if($attachment->type == 'provider_profile_image') $advertisement->provider_profile_image_full_path  = $attachment->provider_profile_image_full_path;
        }
        $advertisement->promotional_video_full_path = $advertisement?->attachment?->promotional_video_full_path;

        return response()->json(response_formatter(DEFAULT_200, $advertisement), 200);
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
     * Show the specified resource.
     * @param int $id
     * @return JsonResponse
     */
    public function details($id): JsonResponse
    {
        $advertisement = $this->advertisement->withoutGlobalScope('translate')->with(['attachments', 'attachment', 'provider:id,company_name,company_phone,company_address,company_email,logo', 'attachments', 'attachment', 'review', 'rating', 'note'])->find($id);

        if (isset($advertisement)) {
            foreach ($advertisement->attachments as $attachment){
                if($attachment->type == 'provider_cover_image') $advertisement->provider_cover_image_full_path = $attachment->provider_cover_image_full_path;
                if($attachment->type == 'provider_profile_image') $advertisement->provider_profile_image_full_path  = $attachment->provider_profile_image_full_path;
            }
            $advertisement->promotional_video_full_path = $advertisement?->attachment?->promotional_video_full_path;

            $advertisement->provider_review = $advertisement?->review?->value;
            $advertisement->provider_rating = $advertisement?->rating?->value;
            $advertisement->additional_note = $advertisement->note ? $advertisement->note->note : null;

            unset($advertisement->attachments, $advertisement->attachment, $advertisement->review, $advertisement->rating, $advertisement->note);

            return response()->json(response_formatter(DEFAULT_200, $advertisement), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }

    /**
     * @param Request $request
     * @param $sourceId
     * @return JsonResponse
     */
    public function storeReSubmit(Request $request, $sourceId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title.0' => 'required|string|max:255',
            'description.0' => 'required|string|max:100',
            'start_date' => 'required|after_or_equal:today',
            'end_date' => 'required|after_or_equal:start_date',
            'type' => 'required|in:video_promotion,profile_promotion',
            'video_attachment' => 'nullable|max:50000|mimetypes:video/mp4,video/avi,video/x-matroska,video/avchd',
            'profile_image' => 'nullable|image|max:10000|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'cover_image' => 'nullable|image|max:10000|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key'))
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        DB::transaction(function () use ($request, $sourceId) {

            $advertisement = $this->advertisement;
            $advertisement->readable_id = $this->generateReadableId();
            $advertisement->title = $request->title[array_search('default', $request->lang)];
            $advertisement->description = $request->description[array_search('default', $request->lang)];
            $advertisement->provider_id = auth('api')->user()->provider->id;
            $advertisement->priority = null;
            $advertisement->type = $request['type'];
            $advertisement->is_paid = 0;
            $advertisement->start_date = Carbon::parse($request->start_date)->startOfDay();
            $advertisement->end_date = Carbon::parse($request->end_date)->endOfDay();
            $advertisement->status = 'pending';
            $advertisement->save();

            $sourceAdvertisement = Advertisement::with(['attachments', 'attachment', 'review', 'rating'])->find($sourceId);

            if ($request->type == 'video_promotion') {
                if ($request->has('video_attachment')){
                    $file = $request->file('video_attachment');
                    $extension = $file->getClientOriginalExtension();

                    $this->advertisementAttachment->create([
                        'advertisement_id' => $advertisement->id,
                        'file_extension_type' => $extension,
                        'file_name' => file_uploader('advertisement/', $extension, $file),
                        'type' => 'promotional_video'
                    ]);
                }else{
                    $sourceVideoAttachment = $sourceAdvertisement?->attachment;
                    if ($sourceVideoAttachment){
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

            if ($request->type == 'profile_promotion'){
                if ($request->has('profile_image')) {
                    $file = $request->file('profile_image');
                    $extension = $file->getClientOriginalExtension();

                    $this->advertisementAttachment->create([
                        'advertisement_id' => $advertisement->id,
                        'file_extension_type' => $extension,
                        'file_name' => file_uploader('advertisement/', $extension, $file),
                        'type' => 'provider_profile_image'
                    ]);
                }else{
                    $sourceVideoAttachment = $sourceAdvertisement?->attachments->where('type', 'provider_profile_image')->first();
                    if ($sourceVideoAttachment){
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
                }else{
                    $sourceVideoAttachment = $sourceAdvertisement?->attachments->where('type', 'provider_cover_image')->first();

                    if ($sourceVideoAttachment){
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

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

    private function copyAttachment($attachment): string
    {
        $originalPath = 'advertisement/' .$attachment->file_name;
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
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title.0' => 'required|string|max:255',
            'description.0' => 'required|string|max:100',
            'start_date' => 'required|after_or_equal:today',
            'end_date' => 'required|after_or_equal:start_date',
            'type' => 'required|in:video_promotion,profile_promotion',
            'video_attachment' => 'nullable|max:50000|mimetypes:video/mp4,video/avi,video/x-matroska,video/avchd',
            'profile_image' => 'nullable|image|max:10000|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'cover_image' => 'nullable|image|max:10000|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key'))
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $advertisement = $this->advertisement->find($id);

        if (!$advertisement){
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        DB::transaction(function () use ($advertisement, $request) {

            $advertisement->title = $request->title[array_search('default', $request->lang)];
            $advertisement->description = $request->description[array_search('default', $request->lang)];
            $advertisement->type = $request['type'];
            $advertisement->start_date = Carbon::parse($request->start_date)->startOfDay();
            $advertisement->end_date = Carbon::parse($request->end_date)->endOfDay();
            $advertisement->is_updated = 1;
            $advertisement->status = 'pending';
            $advertisement->update();

            if ($request->has('video_attachment') || $request->has('profile_image') || $request->has('cover_image')){

                if($advertisement->attachments && $request->has('cover_image')){
                    $coverImage = $advertisement?->attachments->where('type', 'provider_cover_image')->first();
                    if($coverImage){
                        file_remover('advertisement/', $coverImage?->file_name);
                        $coverImage->delete();
                    }
                }

                if($advertisement->attachments && $request->has('profile_image')){
                    $profileImage = $advertisement?->attachments->where('type', 'provider_profile_image')->first();
                    if($profileImage){
                        file_remover('advertisement/', $profileImage?->file_name);
                        $profileImage->delete();
                    }
                }

                if($advertisement->attachment){
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

            if ($request->type == 'profile_promotion'){
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

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

    public function statusUpdate(Request $request, $id, $status): JsonResponse
    {
        $advertisement = $this->advertisement->find($id);
        if ($advertisement) {
            $advertisement->status = $status;
            $advertisement->save();
        }

        if ($request->has('note')){
            $this->advertisementNote->updateOrCreate([
                'advertisement_id' => $advertisement->id,
            ],[
                'type' => $status,
                'note' => $request->note,
            ]);
        }

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    public function destroy(Request $request, $id)
    {
        $advertisement = $this->advertisement->with(['attachments'])->where('id', $id)->first();
        if (isset($advertisement)){
            if($advertisement->attachments){
                foreach ($advertisement->attachments as $attachment){
                    file_remover('advertisement/', $attachment->file_name);
                }
            }
            $this->advertisement->where('id', $id)->delete();

            return response()->json(response_formatter(DEFAULT_DELETE_200), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }

}
