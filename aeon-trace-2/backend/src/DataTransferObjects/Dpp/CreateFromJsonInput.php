<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Dpp;

class CreateFromJsonInput
{
    public function __construct(
        public string $json,
    ) {
    }
}
