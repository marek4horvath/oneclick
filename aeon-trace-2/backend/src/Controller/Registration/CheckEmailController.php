<?php

declare(strict_types=1);

namespace App\Controller\Registration;

use ApiPlatform\Symfony\Validator\Exception\ValidationException;
use App\DataTransferObjects\Registration\EmailInput;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CheckEmailController extends AbstractController
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(EmailInput $input): JsonResponse
    {
        $violations = $this->validator->validate($input);
        if (count($violations)) {
            throw new ValidationException($violations);
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $input->email,
        ]);

        if ($user) {
            return new JsonResponse(data: [
                'exists' => true,
            ], headers: ['Content-Type', 'application/ld+json']);
        }

        return new JsonResponse(data: [
            'exists' => false,
        ], headers: ['Content-Type', 'application/ld+json']);
    }
}
