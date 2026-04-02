<?php

declare(strict_types=1);

namespace Modules\Chat\Repositories;

use Modules\Chat\Models\Participant;
use Modules\Core\Repositories\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

class ParticipantRepository extends BaseRepository
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
        return Participant::class;
    }
}
