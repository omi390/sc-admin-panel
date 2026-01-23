<?php

namespace Modules\ChattingModule\Http\Controllers\Api\V1\Serviceman;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\ChattingModule\Entities\ChannelConversation;
use Modules\ChattingModule\Entities\ChannelList;
use Modules\ChattingModule\Entities\ChannelUser;
use Modules\ChattingModule\Entities\ConversationFile;
use Ramsey\Uuid\Nonstandard\Uuid;
use Modules\ChattingModule\Traits\ChattingTrait;
use function file_uploader;
use function response;
use function response_formatter;

class ChattingController extends Controller
{
    protected ChannelList $channelList;
    protected ChannelUser $channelUser;
    protected ChannelConversation $channelConversation;
    protected ConversationFile $conversationFile;
    use ChattingTrait;

    public function __construct(ChannelList $channelList, ChannelUser $channelUser, ChannelConversation $channelConversation, ConversationFile $conversationFile)
    {
        $this->channelList = $channelList;
        $this->channelUser = $channelUser;
        $this->channelConversation = $channelConversation;
        $this->conversationFile = $conversationFile;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function channelList(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'type' => 'nullable|in:customer,provider,serviceman'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        //admin channel
        $channel = $this->createNewChannel(
            fromUser: $request->user()->id,
            toUser: getSuperAdminId(),
            referenceId : '',
            referenceType : 'support',
        );

        $adminChannel = $this->channelList
            ->withCount('channelUsers')
            ->with([
                'channelLastConversation',
                'channelUsers.user',
            ])
            ->where('id', $channel->id)
            ->first();

        $this->formatConversation($adminChannel);

        //channels except admin
        $channelList = $this->channelList->withCount(['channelUsers'])
            ->with(['channelUsers.user.provider'])
            ->filterByType($request['type'])
            ->whereHas('channelUsers', function ($query) use ($request) {
                $query->where(['user_id' => $request->user()->id]);
            })
            ->where('id', '!=', $adminChannel->id)
            ->orderBy('updated_at', 'DESC')
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        $this->formatConversations($channelList);

        return response()->json(response_formatter(DEFAULT_200, [
            'adminChannel' => $adminChannel,
            'channelList' => $channelList,
        ]), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function channelListSearch(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'search' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $chatList = $this->channelList
            ->withCount(['channelUsers', 'channelUsers as unread_count' => function ($query) {
                $query->where('is_read', 0);
            }])
            ->with([
                'channelLastConversation',
                'channelUsers.user.provider',
            ])
            ->searchByUser(['customer', 'provider'], explode(' ', $request->input('search')), ['first_name', 'last_name'])
            ->whereHas('channelUsers', function ($query) use ($request) {
                $query->where(['user_id' => $request->user()->id]);
            })
            ->orderBy('updated_at', 'DESC')
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        $chatList->each(function ($channel) {
            $lastConversation = $channel?->channelLastConversation;
            $lastFile = $lastConversation?->conversationLastFile?->first();
            $channel->last_sent_message = $lastConversation?->message;
            $channel->last_sent_attachment_type = $lastFile?->file_type;
            $channel->last_sent_files_count = (int)$lastConversation?->conversationLastFile?->count();
            $channel->last_message_sent_user = $lastConversation?->user->first_name . ' ' . $lastConversation?->user->last_name;
            unset($channel->channelLastConversation);
        });

        return response()->json(response_formatter(DEFAULT_200, $chatList), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function referencedChannelList(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'reference_id' => 'required',
            'reference_type' => 'required|in:booking_id',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $chatList = $this->channelList->withCount(['channelUsers'])->with(['channelUsers.user'])
            ->where(['reference_id' => $request['reference_id'], 'reference_type' => $request['reference_type']])
            ->whereHas('channelUsers', function ($query) use ($request) {
                $query->where(['user_id' => $request->user()->id]);
            })->orderBy('updated_at', 'DESC')
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        return response()->json(response_formatter(DEFAULT_200, $chatList), 200);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function createChannel(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reference_id' => '',
            'reference_type' => 'in:booking_id',
            'to_user' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $channelIds = $this->channelUser->where(['user_id' => $request->user()->id])->pluck('channel_id')->toArray();
        $findChannel = $this->channelList->whereIn('id', $channelIds)->whereHas('channelUsers', function ($query) use ($request) {
            $query->where(['user_id' => $request['to_user']]);
        })->latest()->first();

        if (!isset($findChannel)) {
            $channel = $this->channelList;
            $channel->reference_id = $request['reference_id'] ?? null;
            $channel->reference_type = $request['reference_type'] ?? null;
            $channel->save();

            $this->channelUser->insert([
                [
                    'id' => Uuid::uuid4(),
                    'channel_id' => $channel->id,
                    'user_id' => $request->user()->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'id' => Uuid::uuid4(),
                    'channel_id' => $channel->id,
                    'user_id' => $request['to_user'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            return response()->json(response_formatter(DEFAULT_STORE_200, $channel), 200);
        }

        return response()->json(response_formatter(DEFAULT_200, $findChannel), 200);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => '',
            'channel_id' => 'required|uuid',
            'files' => is_null($request['message']) ? 'required|array' : 'array',
            'files.*' => 'max:10240|mimes:' . implode(',', array_column(FILE_TYPE, 'key')),
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        DB::transaction(function () use ($request) {
            $this->channelList->where('id', $request['channel_id'])->update([
                'updated_at' => now()
            ]);
            $this->channelUser->where('channel_id', $request['channel_id'])->where('user_id', '!=', $request->user()->id)
                ->update([
                    'is_read' => 0
                ]);

            $channelConversation = $this->channelConversation;
            $channelConversation->channel_id = $request->channel_id;
            $channelConversation->message = $request['message'];
            $channelConversation->user_id = $request->user()->id;
            $channelConversation->save();

            if ($request->has('files')) {
                foreach ($request->file('files') as $file) {
                    $extension = $file->getClientOriginalExtension();
                    $originalName = $file->getClientOriginalName();

                    $this->conversationFile->create([
                        'conversation_id' => $channelConversation->id,
                        'original_file_name' => $originalName,
                        'stored_file_name' => file_uploader('conversation/', $extension, $file),
                        'file_type' => $extension,
                    ]);
                }
            }
        });

        return response()->json(response_formatter(DEFAULT_STORE_200), 200);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function conversation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'channel_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $this->channelUser->where('channel_id', $request['channel_id'])->where('user_id', $request->user()->id)
            ->update([
                'is_read' => 1
            ]);

        $conversation = $this->channelConversation->where(['channel_id' => $request['channel_id']])
            ->with(['user.provider', 'conversationFiles'])->whereHas('channel.channelUsers', function ($query) use ($request) {
                $query->where(['user_id' => $request->user()->id]);
            })->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        return response()->json(response_formatter(DEFAULT_STORE_200, $conversation), 200);
    }
}
