<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Security;

class ChangeCurrentPasswordInput
{
    public function __construct(
        public string $currentPassword,
        public string $newPassword
    ) {
    }
}
