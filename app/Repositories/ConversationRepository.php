<?php

declare(strict_types=1);

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
        return Conversation::class;
    }
}
