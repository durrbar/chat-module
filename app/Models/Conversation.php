<?php

namespace Modules\Chat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Modules\User\Models\User;
use Modules\Vendor\Models\Shop;

class Conversation extends Model
{
    public $guarded = [];

    protected $appends = [
        'latest_message',
        'unseen',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    /**
     * Returns the latest message from a conversation.
     *
     * @return Message
     */
    public function getLatestMessageAttribute()
    {
        return $this->messages()->latest()->first();
    }

    /**
     * Get all of the conversations participants.
     */
    public function participants()
    {
        return $this->hasMany(Participant::class, 'conversation_id');
    }

    public function getUnseenAttribute()
    {
        if (Auth::check()) {
            $instance = $this->participants()->whereNull('last_read')->where('user_id', auth()->user()->id)->where('type', 'user')->count();

            if ($instance == 0) {
                $instance = $this->participants()->whereNull('last_read')->whereIn('shop_id', auth()->user()->shops()->pluck('id'))->where('type', 'shop')->count();
            }

            return $instance;
        } else {
            return '0';
        }
    }
}
