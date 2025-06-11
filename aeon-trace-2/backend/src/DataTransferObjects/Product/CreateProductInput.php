<?php declare(strict_types=1);

namespace App\DataTransferObjects\Product;

use App\Entity\Company\Company;
use App\Entity\Company\CompanySite;
use App\Entity\Logistics\Logistics;
use App\Entity\Product\ProductTemplate;
use App\Entity\SupplyChain\Node;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class CreateProductInput
{
    public function __construct(
        #[Assert\NotBlank]
        public ProductTemplate $productTemplate,

        /**
         * @var array<int,string> $steps
         */
        #[Assert\NotBlank]
        public array $steps,
        #[Assert\NotBlank]
        public Node $node,
        #[Assert\NotBlank]
        public Company $company,
        public ?User $user = null,
        public ?CompanySite $companySite = null,
        public ?Logistics $materialsReceivedFrom = null,
    ) {
    }
}
