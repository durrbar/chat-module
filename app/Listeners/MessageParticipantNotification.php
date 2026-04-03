<?php

declare(strict_types=1);

namespace Modules\Chat\Listeners;

use Modules\Chat\Events\MessageSent;
use Modules\Chat\Models\Participant;

class MessageParticipantNotification
{
    /**
     * Handle the event.
     *
     */
    public function handle(MessageSent $event): void
    {
        // set participant
        Participant::create([
            'type' => $event->type,
            'conversation_id' => $event->conversation->id,
            'shop_id' => $event->conversation->shop->id,
            'user_id' => $event->conversation->user_id,
            'message_id' => $event->message->id,
        ]);
    }
}
