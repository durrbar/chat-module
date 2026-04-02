<?php

declare(strict_types=1);

namespace Modules\Chat\Enums;

enum ChatParticipantType: string
{
    case Shop = 'shop';
    case User = 'user';
}
