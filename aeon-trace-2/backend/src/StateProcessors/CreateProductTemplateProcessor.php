<?php

declare(strict_types=1);

namespace App\StateProcessors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Product\ProductTemplate;
use App\Entity\Step\Step;
use App\Entity\Step\StepsTemplate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @template T1 of object
 * @template T2 of object
 * @implements ProcessorInterface<T1, T2>
 */
readonly class CreateProductTemplateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack,
        private DenormalizerInterface $denormalizer,
    ) {
    }

    /**
     * @param ProductTemplate $data
     * @throws ExceptionInterface
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ProductTemplate
    {
        if (!$operation instanceof Post) {
            throw new BadRequestException();
        }

        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            throw new BadRequestException();
        }

        if (filter_var($request->get('createStep'), FILTER_VALIDATE_BOOLEAN)) {
            $stepsTemplate = $data->getStepsTemplate();
            if ($stepsTemplate === null) {

                /** @var StepsTemplate $stepsTemplate */
                $stepsTemplate = $this->denormalizer->denormalize([
                    'name' => '',
                ], StepsTemplate::class);

                $this->entityManager->persist($stepsTemplate);
                $data->setStepsTemplate($stepsTemplate);
            }

            /** @var Step $step */
            $step = $this->denormalizer->denormalize([
                'name' => $data->getName(),
            ], Step::class);
            $step->setStepsTemplate($stepsTemplate);
            $this->entityManager->persist($step);
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}
