<?php

namespace Modules\ChattingModule\Traits;

use Ramsey\Uuid\Nonstandard\Uuid;

trait ChattingTrait
{
    public function createNewChannel($fromUser, $toUser, $referenceId = null, $referenceType = null)
    {
        $channelIds = $this->channelUser->where(['user_id' => $fromUser])->pluck('channel_id')->toArray();
        $findChannel = $this->channelList
            ->whereIn('id', $channelIds)
            ->whereHas('channelUsers', function ($query) use ($toUser) {
                $query->where(['user_id' => $toUser]);
            })->latest()->first();

        if (!isset($findChannel)) {
            $channel = $this->channelList;
            $channel->reference_id = $referenceId;
            $channel->reference_type = $referenceType;
            $channel->save();

            $this->channelUser->insert([
                [
                    'id' => Uuid::uuid4(),
                    'channel_id' => $channel->id,
                    'user_id' => $fromUser,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'id' => Uuid::uuid4(),
                    'channel_id' => $channel->id,
                    'user_id' => $toUser,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            return $channel;
        }

        return $findChannel;
    }

    function formatConversations($channelList): void
    {
        $channelList->each(function ($channel) {
            $lastConversation = $channel?->channelLastConversation;
            $lastConversationFiles = $lastConversation?->conversationLastFiles;
            $channel->last_message_sent_user = $lastConversation?->user->first_name . ' ' . $lastConversation?->user->last_name;
            $channel->last_sent_message = $lastConversation?->message;
            $channel->last_sent_attachment_type = $lastConversationFiles?->last()?->file_type;
            $channel->last_sent_files_count = (int)$lastConversationFiles?->count();
            unset($channel->channelLastConversation);
        });
    }
    function formatConversation($channel): void
    {
        $lastConversation = $channel?->channelLastConversation;
        $lastConversationFiles = $lastConversation?->conversationLastFiles;
        $channel->last_message_sent_user = $lastConversation?->user->first_name . ' ' . $lastConversation?->user->last_name;
        $channel->last_sent_message = $lastConversation?->message;
        $channel->last_sent_attachment_type = $lastConversationFiles?->last()?->file_type;
        $channel->last_sent_files_count = (int)$lastConversationFiles?->count();
        unset($channel->channelLastConversation);
    }
}
