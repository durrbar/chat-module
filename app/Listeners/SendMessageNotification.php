<?php

declare(strict_types=1);

namespace Modules\Chat\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;
use Modules\Chat\Enums\ChatParticipantType;
use Modules\Chat\Events\MessageSent;
use Modules\Chat\Models\Participant;
use Modules\Notification\Notifications\MessageReminder;
use Modules\User\Models\User;
use Modules\Vendor\Models\Shop;

class SendMessageNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public int $delay = 900;

    public function handle(MessageSent $event): void
    {
        $participant = Participant::where('message_id', $event->message->id)->first();

        if ($participant->last_read === null) {
            if ($participant->notify === 0) {
                if ($event->type === ChatParticipantType::User->value) {
                    $user = User::findOrFail($event->conversation->user_id);
                    $notification = $user->profile?->notifications;
                    if (empty($notification)) {
                        $notification['enable'] = 1;
                        $notification['email'] = $user->email;
                    }
                } else {
                    $shop = Shop::findOrFail($event->conversation->shop_id);
                    $notification = json_decode($shop->notifications, true);
                    if (empty($notification)) {
                        $notification['enable'] = 1;
                        $notification['email'] = $shop->owner->email;
                    }
                }
                if ($notification['enable'] === 1) {
                    Notification::route('mail', [
                        $notification['email'],
                    ])->notify(new MessageReminder($participant));

                    $participant->notify = 1;
                    $participant->save();
                }
            }
        }
    }
}
