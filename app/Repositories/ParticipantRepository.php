<?php

namespace Modules\Chat\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Modules\Chat\Models\Participant;
use Modules\Core\Repositories\BaseRepository;
use Modules\Ecommerce\Exceptions\MarvelException;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;


class ParticipantRepository extends BaseRepository
{

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
        return Participant::class;
    }

}
