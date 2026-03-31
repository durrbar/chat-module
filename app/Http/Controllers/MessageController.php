<?php

namespace Modules\Chat\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Chat\Models\Conversation;
use Modules\Chat\Models\Participant;
use Modules\Chat\Repositories\ConversationRepository;
use Modules\Chat\Repositories\MessageRepository;
use Modules\Core\Exceptions\DurrbarException;
use Modules\Core\Http\Controllers\CoreController;
use Modules\Ecommerce\Http\Requests\MessageCreateRequest;
use Prettus\Validator\Exceptions\ValidatorException;

class MessageController extends CoreController
{
    public $repository;

    public $conversationRepository;

    public function __construct(MessageRepository $repository, ConversationRepository $conversationRepository)
    {
        $this->repository = $repository;
        $this->conversationRepository = $conversationRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Collection|Message[]
     */
    public function index(Request $request, $conversation_id)
    {
        $request->conversation_id = $conversation_id;

        $user = Auth::user();
        $shopIds = $user->shops()->pluck('id')->toArray();
        $conversation = $this->conversationRepository->findOrFail($conversation_id);
        abort_unless($user->shop_id === $conversation->shop_id || in_array($conversation->shop_id, $shopIds) || $user->id === $conversation->user_id, 404, 'Unauthorized');

        $messages = $this->fetchMessages($request);

        $limit = $request->limit ? $request->limit : 15;

        return $messages->paginate($limit);

    }

    public function seenMessage(Request $request)
    {
        return $this->seen($request->conversation_id);
    }

    public function seen($conversation_id)
    {
        $participant = Participant::where('conversation_id', $conversation_id)
            ->whereNull('last_read')
            ->where(function ($query): void {
                $query->where('user_id', auth()->user()->id);
                $query->where('type', 'user');
            })
            ->update(['last_read' => new Carbon()]);

        if ($participant === 0) {
            $shopIds = auth()->user()->shops()->pluck('id');
            $participant = Participant::where('conversation_id', $conversation_id)
                ->whereNull('last_read')
                ->where(function ($query) use ($shopIds): void {
                    $query->whereIn('shop_id', $shopIds);
                    $query->orWhere('shop_id', auth()->user()->shop_id);
                    $query->where('type', 'shop');
                })
                ->update(['last_read' => new Carbon()]);
        }

        return $participant;
    }

    public function fetchMessages(Request $request)
    {

        $user = $request->user();
        $conversation_id = $request->conversation_id;

        try {
            $conversation = Conversation::where('id', $conversation_id)
                ->where('user_id', $user->id)
                ->orWhereIn('shop_id', $user->shops()->pluck('id'))
                ->orWhere('shop_id', $user->shop_id)
                ->with(['user', 'shop'])->first();

            if (empty($conversation)) {
                throw new DurrbarException(NOT_AUTHORIZED);
            }

            $with = ['conversation.shop', 'conversation.user.profile'];

            if (str_contains((string) $request->include, 'message.user')) {
                $with[] = 'user';
            }

            if (str_contains((string) $request->include, 'message.conversation')) {
                $with[] = 'conversation';
            }

            return $this->repository->where('conversation_id', $conversation_id)
                ->with($with)
                ->orderBy('id', 'DESC');
        } catch (\Exception $e) {
            throw new DurrbarException(NOT_AUTHORIZED);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return mixed
     *
     * @throws ValidatorException
     */
    public function store(MessageCreateRequest $request, $conversation_id)
    {
        $request->conversation_id = $conversation_id;

        return $this->storeMessage($request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return mixed
     *
     * @throws ValidatorException
     */
    public function storeMessage(Request $request)
    {
        return $this->repository->storeMessage($request);
    }
}
