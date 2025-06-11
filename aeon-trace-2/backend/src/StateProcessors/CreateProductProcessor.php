<?php declare(strict_types=1);

namespace App\StateProcessors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\DataTransferObjects\Product\CreateProductInput;
use App\Entity\Product\Product;
use App\Entity\Product\ProductTemplate;
use App\Entity\Step\ProductStep;
use App\Entity\Step\Step;
use App\Message\DppCreateProcessMessage;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @template T1 of object
 * @template T2 of object
 * @implements ProcessorInterface<T1, T2>
 */
readonly class CreateProductProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $bus,
    ) {
    }

    /** @param CreateProductInput $data */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Product
    {
        $template = $data->productTemplate;
        $templateSteps = $this->fetchSteps($data->steps, $template);

        $product = new Product();
        $product->setName($template->getName());
        $product->setDescription($template->getDescription());
        $product->setProductTemplate($template);

        $this->entityManager->persist($product);

        $map = [];

        /** @var Step $templateStep */
        foreach ($templateSteps as $templateStep) {
            $quantity = $templateStep->getQuantity();
            $isBatch = $quantity === null;

            // Correct loop for when quantity is implemented: $i = $isBatch ? 0 : 1; $i <= $templateStep->getQuantity(); $i++
            for ($i = 0; $i < 1; $i++) {
                $productStep = new ProductStep();
                $productStep->setName($templateStep->getName());
                $productStep->setStepTemplateReference($templateStep);
                $productStep->setProduct($product);
                $productStep->setQuantityIndex($isBatch ? 0 : $quantity);
                $productStep->setNode($data->node);
                $productStep->setCompany($data->company);

                if ($data->user !== null) {
                    $productStep->setUser($data->user);
                }

                if ($data->companySite !== null) {
                    $productStep->setCompanySite($data->companySite);
                }

                if ($data->materialsReceivedFrom !== null) {
                    $productStep->addMaterialsReceivedFrom($data->materialsReceivedFrom);
                }

                $map[$templateStep->getId()->jsonSerialize()] = $productStep;
                $parentSteps = $templateStep->getParentSteps();

                if ($parentSteps && !$parentSteps->isEmpty()) {
                    foreach ($parentSteps as $parentStep) {
                        $parentId = $parentStep->getId()->jsonSerialize();

                        if (isset($map[$parentId])) {
                            $productStep->setParentStep($map[$parentId]);
                        }
                    }
                }

                $this->entityManager->persist($productStep);
                $productStep->setDppName($productStep->getName() . ':' . $productStep->getId()->toRfc4122());
                $this->entityManager->persist($productStep);
                $this->entityManager->flush();

                $this->bus->dispatch(new DppCreateProcessMessage($productStep->getId(), ProductStep::class));
            }
        }

        $this->entityManager->flush();

        return $product;
    }

    /**
     * @param array<int, string> $ids
     * @return array<Step>
     */
    private function fetchSteps(array $ids, ProductTemplate $productTemplate): array
    {
        $ids = array_map(fn (string $id): string => Uuid::fromString($id)->toBinary(), $ids);
        $steps = $this->entityManager->getRepository(Step::class)
            ->findBy(['id' => $ids]);

        if (count($ids) !== count($steps)) {
            throw new EntityNotFoundException('Some Steps IDs are invalid or not found.');
        }

        foreach ($steps as $step) {
            if ($step->getProductTemplate()?->getId()->toRfc4122() !== $productTemplate->getId()->toRfc4122()) {
                throw new EntityNotFoundException('Some steps don\'t belong to the given ProductTemplate.');
            }
        }

        return $steps;
    }
}
