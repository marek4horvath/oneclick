<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Logistics;

use App\Entity\Logistics\LogisticsTemplate;
use Symfony\Component\Validator\Constraints as Assert;

class CreateLogisticsInput
{
    public function __construct(
        #[Assert\NotBlank]
        public LogisticsTemplate $logisticsTemplate,
    ) {
    }
}
