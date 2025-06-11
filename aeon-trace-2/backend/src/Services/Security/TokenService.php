<?php

declare(strict_types=1);

namespace App\Services\Security;

use App\DataTransferObjects\Security\ChangeCurrentPasswordInput;
use App\DataTransferObjects\Security\PasswordInput;
use App\DataTransferObjects\Security\RequestPasswordResetInput;
use App\Entity\Security\UserToken;
use App\Entity\User;
use Countable;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Negotiation\Exception\InvalidArgument;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class TokenService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private UserPasswordHasherInterface $passwordHasher,
        private UrlGeneratorInterface $router,
        private LoggerInterface $logger,
        private Security $security,
        private TranslatorInterface $translator,
    ) {
    }

    public function requestReset(RequestPasswordResetInput $DTO): TransportExceptionInterface|JsonResponse
    {
        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $DTO->email,
        ]);

        if (!($user instanceof User)) {
            throw new NotFoundHttpException($this->translator->trans('Account not found', domain: 'messages'));
        }

        $tokens = $this->entityManager->getRepository(UserToken::class)->findValid(new DateTimeImmutable(), $user);

        if (is_array($tokens) || $tokens instanceof Countable) {
            if (count($tokens) >= 3) {
                throw new NotAcceptableHttpException($this->translator->trans('Max number of password resets', domain: 'messages'));
            }
        }

        $token = new UserToken();
        $token->setTokenOwner($user);
        $this->entityManager->persist($token);

        $this->entityManager->flush();

        $resetUrl = $this->router->generate('password_reset', [
            'token' => $token->getToken(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject($this->translator->trans('Password reset', domain: 'messages'))
            ->htmlTemplate('Email/Security/requestPasswordReset.html.twig')
            ->context([
                'reset_link' => $resetUrl,
            ]);
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error($e);
            return $e;
        }

        return new JsonResponse();
    }

    public function resetPassword(PasswordInput $input): void
    {
        /** @var UserToken|null $token */
        $token = $this->entityManager->getRepository(UserToken::class)->findOneBy([
            'token' => $input->token,
            'usedAt' => null,
        ]);
        if ($token === null) {
            throw new InvalidArgument($this->translator->trans('Invalid password reset token', domain: 'messages'));
        }
        $date = new DateTimeImmutable();
        $isExpired = $date->diff($token->getCreatedAt());
        if ($isExpired->i >= 15) {
            throw new InvalidArgument($this->translator->trans('Expired password reset token', domain: 'messages'));
        }
        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->find($token->getTokenOwner());
        $user->setPassword($input->password);
        $token->use();

        $this->entityManager->persist($token);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function changeCurrentPassword(ChangeCurrentPasswordInput $input): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if (!$this->passwordHasher->isPasswordValid($user, $input->currentPassword)) {
            throw new BadRequestException($this->translator->trans('Passwords do not match', domain: 'messages'));
        }

        $user->setPassword($input->newPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function confirmLogin(PasswordInput $input): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'invitationToken' => $input->token,
        ]);

        if (!($user instanceof User)) {
            throw new NotFoundHttpException($this->translator->trans('Token not valid', domain: 'messages'));
        }

        $user->setPassword($input->password);
        $user->setActive(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
