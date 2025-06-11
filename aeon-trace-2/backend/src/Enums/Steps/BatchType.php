<?php

declare(strict_types=1);

namespace App\Enums\Steps;

enum BatchType: string
{
    case BATCH = 'BATCH';
    case DISCRETE_SINGLE = 'DISCRETE_SINGLE';
    case DISCRETE_MULTIPLE = 'DISCRETE_MULTIPLE';
}
