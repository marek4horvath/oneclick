<?php

declare(strict_types=1);

namespace App\Controller\Security;

use ApiPlatform\Symfony\Validator\Exception\ValidationException;
use App\DataTransferObjects\Security\RequestPasswordResetInput;
use App\Services\Security\TokenService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RequestPasswordResetController extends AbstractController
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function __invoke(TokenService $tokenService, RequestPasswordResetInput $input): JsonResponse
    {
        $violations = $this->validator->validate($input);
        if (count($violations)) {
            throw new ValidationException($violations);
        }
        $tokenService->requestReset($input);

        return new JsonResponse([
            'message' => $this->translator->trans('Request password reset success', domain: 'messages'),
        ]);
    }
}
