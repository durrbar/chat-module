<?php

declare(strict_types=1);

namespace Modules\Chat\Repositories;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Modules\Chat\Enums\ChatParticipantType;
use Modules\Chat\Events\MessageSent;
use Modules\Chat\Models\Conversation;
use Modules\Chat\Models\Message;
use Modules\Core\Exceptions\DurrbarException;
use Modules\Core\Repositories\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

class MessageRepository extends BaseRepository
{
    public function boot(): void
    {
        try {
            $this->pushCriteria(app(RequestCriteria::class));
        } catch (RepositoryException $e) {
        }
    }

    /**
     * Configure the Model
     **/
    public function model(): string
    {
        return Message::class;
    }

    /**
     * @return LengthAwarePaginator|JsonResponse|Collection|mixed
     */
    public function storeMessage($request)
    {
        $type = '';
        $conversation_id = $request->conversation_id;
        try {
            $conversation = Conversation::findOrFail($conversation_id);
            $authorize = [
                'user' => false,
                'shop' => false,
            ];
            if ($request->user()->id === $conversation->user_id) {
                $authorize['user'] = true;
                $type = ChatParticipantType::Shop->value;
            }
            if (
                in_array($conversation->shop_id, $request->user()->shops()->pluck('id')->toArray()) ||
                $conversation->shop_id === $request->user()->shop_id
            ) {
                $authorize['shop'] = true;
                $type = ChatParticipantType::User->value;
            }
            if ($authorize['user'] === false && $authorize['shop'] === false) {
                throw new DurrbarException(NOT_AUTHORIZED);
            }

            $message = $this->create([
                'body' => $request->message,
                'conversation_id' => $conversation_id,
                'user_id' => $request->user()->id,
            ]);

            $message->conversation->update(['updated_at' => now()]);

            event(new MessageSent($message, $conversation, $type, $request->user()));

            return $message;
        } catch (Exception $e) {
            throw new DurrbarException(NOT_AUTHORIZED);
        }
    }
}
