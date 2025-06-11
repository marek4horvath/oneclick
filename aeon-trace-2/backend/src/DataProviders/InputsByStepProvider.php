<?php

declare(strict_types=1);

namespace App\DataProviders;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Product\Input;
use App\Repository\Product\InputRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<Input>
 */
final class InputsByStepProvider implements ProviderInterface
{
    /**
     * @param InputRepository<Input> $inputRepository
     */
    public function __construct(
        private InputRepository $inputRepository
    ) {
    }

    /**
     * @return iterable<Input>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $stepId = $uriVariables['id'] ?? null;

        if (!$stepId) {
            throw new NotFoundHttpException('Step ID not provided.');
        }

        return $this->inputRepository->findBy(['step' => $stepId]);
    }
}
