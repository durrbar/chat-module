<?php

namespace Modules\Chat\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Modules\Chat\Models\Conversation;
use Modules\Core\Repositories\BaseRepository;
use Modules\Ecommerce\Models\AbusiveReport;
use Modules\Ecommerce\Exceptions\MarvelException;
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
