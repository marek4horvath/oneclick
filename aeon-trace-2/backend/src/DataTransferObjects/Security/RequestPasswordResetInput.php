<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Security;

use Symfony\Component\Validator\Constraints as Assert;

class RequestPasswordResetInput
{
    public function __construct(
        #[Assert\Email]
        public string $email,
    ) {
    }
}
