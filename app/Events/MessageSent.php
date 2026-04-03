<?php

declare(strict_types=1);

namespace Modules\Chat\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Chat\Enums\ChatParticipantType;
use Modules\Chat\Models\Conversation;
use Modules\Chat\Models\Message;
use Modules\Core\Exceptions\DurrbarException;
use Modules\Settings\Models\Settings;
use Modules\User\Models\User;
use Modules\User\Traits\UsersTrait;
use Modules\Vendor\Models\Shop;

// why it does not implement queue ? is it for instant delivery or there are another reasons ?
class MessageSent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;
    use UsersTrait;

    public function __construct(
        public Message $message,
        public Conversation $conversation,
        public mixed $type,
        public User $user
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        switch ($this->type) {
            case ChatParticipantType::Shop->value:
                $shopOwner = Shop::findOrFail($this->conversation->shop_id);

                return [
                    new PrivateChannel('message.created.'.$shopOwner->owner_id),
                ];

            case ChatParticipantType::User->value:
                $eventChannels = [];
                foreach ($this->getAdminUsers() as $user) {
                    $eventChannels[] = new PrivateChannel('message.created.'.$user->id);
                }

                return $eventChannels;
        }

        return [];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'message' => '1 new message',
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.event';
    }

    /**
     * Determine if this event should broadcast.
     */
    public function broadcastWhen(): bool
    {
        try {
            $settings = Settings::first();
            $enableBroadCast = false;

            if (! config('shop.pusher.enabled')) {
                return false;
            }

            if (isset($settings->options['pushNotification']['all']['message'])) {
                if ($settings->options['pushNotification']['all']['message'] === true) {
                    $enableBroadCast = true;
                }
            }

            return $enableBroadCast;
        } catch (DurrbarException $th) {
            throw new DurrbarException(SOMETHING_WENT_WRONG, $th->getMessage());
        }
    }
}
