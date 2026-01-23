<?php

namespace Modules\ChattingModule\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\ChattingModule\Entities\ChannelConversation;
use Modules\ChattingModule\Entities\ChannelList;
use Modules\ChattingModule\Entities\ChannelUser;
use Modules\ChattingModule\Entities\ConversationFile;
use Modules\UserManagement\Entities\User;
use Ramsey\Uuid\Nonstandard\Uuid;

class ChattingController extends Controller
{
    protected ChannelList $channelList;
    protected ChannelUser $channelUser;
    protected ChannelConversation $channelConversation;
    protected ConversationFile $conversationFile;
    protected User $user;

    public function __construct(User $user, ChannelList $channelList, ChannelUser $channelUser, ChannelConversation $channelConversation, ConversationFile $conversationFile)
    {
        $this->channelList = $channelList;
        $this->channelUser = $channelUser;
        $this->channelConversation = $channelConversation;
        $this->conversationFile = $conversationFile;
        $this->user = $user;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Factory|View|Application
     */
    public function index(Request $request): Factory|View|Application
    {
        $request->validate([
            'user_type' => 'nullable|in:customer,provider_admin,provider_serviceman'
        ]);

        $chatList = $this->channelList->withCount(['channelUsers'])
            ->with(['channelUsers.user.provider'])
            ->whereHas('channelUsers', function ($query) use ($request) {
                $query->where(['user_id' => $request->user()->id]);
            })
            ->when($request->has('user_type'), function ($query) use ($request) {
                $type = $request['user_type'];
                $query->whereHas('channelUsers.user', function ($query) use ($type) {
                    $query->where(function ($query) use ($type) {
                        if ($type == 'customer') {
                            $query->where('user_type', 'customer');
                        } elseif ($type == 'provider_admin') {
                            $query->where('user_type', 'provider-admin');
                        } elseif ($type == 'provider_serviceman') {
                            $query->where('user_type', 'provider-serviceman');
                        }
                    });
                });
            })
            ->orderBy('updated_at', 'DESC')->get();

        $chatList->map(function ($chat) use ($request) {
            $chat['is_read'] = $chat->channelUsers->where('user_id', $request->user()->id)->first()->is_read;
        });

        $type = $request['user_type'];
        $customers = $this->user->ofStatus(1)->where(['user_type' => 'customer'])->get();
        $providers = $this->user->ofStatus(1)->where(['user_type' => 'provider-admin'])->with(['provider'])->get();
        $servicemen = $this->user->ofStatus(1)->where(['user_type' => 'provider-serviceman'])->get();

        return view('chattingmodule::admin.index', compact('chatList', 'customers', 'providers', 'servicemen', 'type'));
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
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $chatList = $this->channelList->withCount(['channelUsers'])
            ->with(['channelUsers.user'])
            ->whereHas('channelUsers', function ($query) use ($request) {
                $query->where(['user_id' => $request->user()->id]);
            })->orderBy('updated_at', 'DESC')
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

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
     * @return RedirectResponse
     */
    public function createChannel(Request $request): RedirectResponse
    {
        $request->validate([
            'reference_id' => '',
            'reference_type' => 'in:booking_id',
            'user_type' => 'in:customer,provider-admin,provider-serviceman',
        ]);

        if ($request['user_type'] == 'customer') {
            $request['to_user'] = $request['customer_id'];
        } elseif ($request['user_type'] == 'provider-admin') {
            $request['to_user'] = $request['provider_id'];
        } elseif ($request['user_type'] == 'provider-serviceman') {
            $request['to_user'] = $request['serviceman_id'];
        }

        if (!$this->user->where('id', $request['to_user'])->exists()) {
            Toastr::error(translate('Receiver not found'));
            return back();
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
        }

        Toastr::success(translate('you_can_start_conversation_now'));

        $userTypeRoutes = [
            'customer' => 'customer',
            'provider-admin' => 'provider_admin',
            'provider-serviceman' => 'provider_serviceman',
        ];

        $userType = $request['user_type'];

        if (array_key_exists($userType, $userTypeRoutes)) {
            return redirect()->route('admin.chat.index', ['user_type' => $userTypeRoutes[$userType]]);
        } else {
            return back();
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => is_null($request['files']) ? 'required' : '',
            'channel_id' => 'required|uuid',
            'files' => is_null($request['message']) ? 'required|array' : 'array',
            'files.*' => 'max:10240|mimes:' . implode(',', array_column(FILE_TYPE, 'key'))
        ], [
            'files.required' => 'The files field is required when message is not provided.',
            'files.array' => 'The files must be an array.',
            'files.*.max' => 'The maximum file size allowed is :max kilobytes.',
            'files.*.mimes' => 'Invalid file type. Allowed types are: ' . implode(', ', array_column(FILE_TYPE, 'key'))
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

        $conversation = $this->channelConversation->where(['channel_id' => $request['channel_id']])
            ->with(['user', 'conversationFiles'])->whereHas('channel.channelUsers', function ($query) use ($request) {
                $query->where(['user_id' => $request->user()->id]);
            })->latest()->paginate(100, ['*'], 'offset', $request['offset']);

        return response()->json([
            'template' => view('chattingmodule::admin.partials._conversation-messages-only', compact('conversation'))->render()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function conversation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'channel_id' => 'required|uuid',
            'offset' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $this->channelUser->where('channel_id', $request['channel_id'])->where('user_id', $request->user()->id)
            ->update([
                'is_read' => 1
            ]);

        $conversation = $this->channelConversation->where(['channel_id' => $request['channel_id']])
            ->with(['user', 'conversationFiles'])->whereHas('channel.channelUsers', function ($query) use ($request) {
                $query->where(['user_id' => $request->user()->id]);
            })->latest()->paginate(100, ['*'], 'offset', $request['offset']);

        $fromUser = $this->channelUser->where('channel_id', $request['channel_id'])->where('user_id', '!=', $request->user()->id)->first();

        $channelId = $request['channel_id'];

        return response()->json([
            'template' => view('chattingmodule::admin.partials._conversations', compact('fromUser', 'conversation', 'channelId'))->render()
        ]);
    }
}
