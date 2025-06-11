<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Security;

use Symfony\Component\Validator\Constraints as Assert;

class PasswordInput
{
    public function __construct(
        #[Assert\NotBlank]
        public string $token,
        #[Assert\Length(min: 6)]
        public string $password,
    ) {
    }
}
