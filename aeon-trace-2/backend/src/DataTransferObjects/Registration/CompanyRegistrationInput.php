<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Registration;

use Symfony\Component\Validator\Constraints as Assert;

class CompanyRegistrationInput
{
    public function __construct(
        #[Assert\NotBlank]
        public string $name,
        #[Assert\NotBlank]
        public string $street,
        public string $houseNo,
        #[Assert\NotBlank]
        public string $city,
        #[Assert\NotBlank]
        public string $postcode,
        #[Assert\NotBlank]
        public string $country,
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,
        #[Assert\NotBlank]
        public string $phone,
        #[Assert\NotBlank]
        public string $password,
        #[Assert\NotBlank]
        public string $token,
    ) {
    }
}
