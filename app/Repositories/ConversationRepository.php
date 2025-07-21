<?php

namespace Modules\Chat\Repositories;

use Modules\Chat\Models\Conversation;
use Modules\Core\Repositories\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

class ConversationRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'shop.name' => 'like',
        'user.name' => 'like',
    ];

    public function boot()
    {
        try {
            $this->pushCriteria(app(RequestCriteria::class));
        } catch (RepositoryException $e) {
        }
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Conversation::class;
    }
}
