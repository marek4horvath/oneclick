<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Registration;

use Symfony\Component\Validator\Constraints as Assert;

class EmailInput
{
    public function __construct(
        #[Assert\Email]
        public string $email,
    ) {
    }
}
