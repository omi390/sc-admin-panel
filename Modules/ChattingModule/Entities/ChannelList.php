<?php

namespace Modules\ChattingModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChannelList extends Model
{
    use HasFactory, SoftDeletes, HasUuid;

    protected $fillable = [
        'reference_id',
        'reference_type',
    ];

    //relation
    public function scopeFilterByType($query, $type = null)
    {
        return $query->when($type, function ($query, $type) {
            return $query->whereHas('channelUsers.user', function ($query) use ($type) {
                $query->where(function ($query) use ($type) {
                    if ($type == 'customer') {
                        $query->where('user_type', 'customer');
                    } elseif ($type == 'provider') {
                        $query->where('user_type', 'provider-admin');
                    } elseif ($type == 'serviceman') {
                        $query->where('user_type', 'provider-serviceman');
                    }
                });
            });
        });
    }

    public function scopeSearchByUser($query, array $types, $keywords, array $searchColumns): mixed
    {
        return $query->when(!empty($keywords), function ($query) use ($types, $keywords, $searchColumns) {
            return $query->where(function ($query) use ($types, $keywords, $searchColumns) {
                foreach ($keywords as $key) {
                    $query->when(in_array('customer' || in_array('serviceman', $types), $types), function ($query) use ($key, $searchColumns) {
                        $query->whereHas('channelUsers.user', function ($query) use ($key, $searchColumns) {
                            $query->where(function ($query) use ($key, $searchColumns) {
                                foreach ($searchColumns as $column) {
                                    $query->orWhere($column, 'LIKE', '%' . $key . '%');
                                }
                            });
                        });
                    })->when(in_array('provider', $types), function ($query) use ($key, $searchColumns) {
                        $query->whereHas('channelUsers.user.provider', function ($query) use ($key, $searchColumns) {
                            $query->where(function ($query) use ($key, $searchColumns) {
                                foreach ($searchColumns as $column) {
                                    $query->orWhere($column, 'LIKE', '%' . $key . '%');
                                }
                            });
                        });
                    });
                }
            });
        });
    }


    public function channelUsers(): HasMany
    {
        return $this->hasMany(ChannelUser::class, 'channel_id', 'id');
    }

    public function channelConversations(): HasMany
    {
        return $this->hasMany(ChannelConversation::class, 'channel_id', 'id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ConversationFile::class, 'channel_id', 'id');
    }

    public function channelLastConversation(): HasOne
    {
        return $this->hasOne(ChannelConversation::class, 'channel_id', 'id')->with(['user', 'conversationLastFiles'])->latest();
    }

    protected static function newFactory()
    {
        return \Modules\ChattingModule\Database\factories\ChannelListFactory::new();
    }
}
