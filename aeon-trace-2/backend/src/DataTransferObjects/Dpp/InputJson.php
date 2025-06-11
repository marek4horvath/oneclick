<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Dpp;

use DateTime;
use Symfony\Component\Validator\Constraints as Assert;

class InputJson
{
    public function __construct(
        #[Assert\NotBlank]
        public string $inputId,
        public ?string $text = null,
        public ?string $textArea = null,
        public ?float $number = null,
        public ?DateTime $date = null,
        public ?DateTime $dateFrom = null,
        public ?DateTime $dateTo = null,
        public ?float $lat = null,
        public ?float $lng = null,
        public ?bool $updatable = null,
        public ?string $radio = null,

        /**
         * @var array<string>
         */
        public ?array $checkbox = null,
    ) {
    }
}
