<?php

declare(strict_types=1);

namespace Modules\Chat\Models;

use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Modules\User\Models\User;
use Modules\Vendor\Models\Shop;

#[Unguarded]
#[Appends([
    'latest_message',
    'unseen',
])]
class Conversation extends Model
{
    use HasUuids;

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
     */
    public function getLatestMessageAttribute(): ?Message
    {
        return $this->messages()->latest()->first();
    }

    /**
     * Get all of the conversations participants.
     */
    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class, 'conversation_id');
    }

    public function getUnseenAttribute(): int|string
    {
        if (Auth::check()) {
            $instance = $this->participants()->whereNull('last_read')->where('user_id', auth()->user()->id)->where('type', 'user')->count();

            if ($instance === 0) {
                $instance = $this->participants()->whereNull('last_read')->whereIn('shop_id', auth()->user()->shops()->pluck('id'))->where('type', 'shop')->count();
            }

            return $instance;
        }

        return '0';

    }
}
