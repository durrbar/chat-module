<?php

namespace Modules\Chat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\User\Models\User;

class Message extends Model
{
    public $timestamps = true;

    public $guarded = [];

    // protected $appends = ['content'];

    // public function getContentAttribute() {
    //     return preg_replace('/\\\\(.?)/', "", $this->body);
    // }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    /**
     * @return belongsTo
     */
    public function participant(): HasOne
    {
        return $this->hasOne(Participant::class, 'message_id');
    }
}
