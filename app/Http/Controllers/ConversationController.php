<?php

declare(strict_types=1);

namespace Modules\Chat\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Chat\Http\Requests\ConversationCreateRequest;
use Modules\Chat\Repositories\ConversationRepository;
use Modules\Core\Exceptions\DurrbarException;
use Modules\Core\Http\Controllers\CoreController;
use Modules\Vendor\Models\Shop;
use Prettus\Validator\Exceptions\ValidatorException;

class ConversationController extends CoreController
{
    public $repository;

    public function __construct(ConversationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Collection|Conversation[]
     */
    public function index(Request $request)
    {
        $limit = $request->limit ? $request->limit : 15;
        $conversation = $this->fetchConversations($request);

        return $conversation->paginate($limit);
    }

    public function show($conversation_id)
    {
        $user = Auth::user();
        $shopIds = $user->shops()->pluck('id')->toArray();
        $conversation = $this->repository->with(['shop', 'user.profile'])->findOrFail($conversation_id);
        abort_unless($user->shop_id === $conversation->shop_id || in_array($conversation->shop_id, $shopIds) || $user->id === $conversation->user_id, 404, 'Unauthorized');

        return $conversation;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Query|Conversation[]
     */
    public function fetchConversations(Request $request)
    {
        $with = ['user.profile', 'shop'];

        if (str_contains((string) $request->include, 'conversation.messages')) {
            $with[] = 'messages';
        }

        return $this->repository->where(function ($query): void {
            $user = Auth::user();
            $shopIds = $user->shops()->pluck('id');
            $query->where('user_id', $user->id);
            $query->orWhereIn('shop_id', $shopIds);
            $query->orWhere('shop_id', $user->shop_id);
            $query->orderBy('updated_at', 'desc');
        })->with($with);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return mixed
     *
     * @throws ValidatorException
     */
    public function store(ConversationCreateRequest $request)
    {
        $user = $request->user();
        if (empty($user)) {
            throw new DurrbarException(NOT_AUTHORIZED);
        }

        $shop = Shop::findOrFail($request->shop_id);
        if ($shop->owner_id === $request->user()->id) {
            throw new DurrbarException(YOU_CAN_NOT_SEND_MESSAGE_TO_YOUR_OWN_SHOP);
        }
        if ($request->shop_id === $request->user()->shop_id) {
            throw new DurrbarException(YOU_CAN_NOT_SEND_MESSAGE_TO_YOUR_OWN_SHOP);
        }

        return $this->repository->firstOrCreate([
            'user_id' => $user->id,
            'shop_id' => $request->shop_id,
        ]);
    }
}
