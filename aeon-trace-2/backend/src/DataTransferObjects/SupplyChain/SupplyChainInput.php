<?php declare(strict_types=1);

namespace App\DataTransferObjects\SupplyChain;

use App\Entity\SupplyChain\SupplyChainTemplate;
use Symfony\Component\Validator\Constraints as Assert;

class SupplyChainInput
{
    public function __construct(
        #[Assert\NotBlank]
        public SupplyChainTemplate $supplyChainTemplate,
    ) {
    }
}
