<?php

declare(strict_types=1);

namespace App\Services\Security;

use App\Entity\Company\Company;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class RegistrationService
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $router,
    ) {
    }

    public function sendWelcomeEmail(User $user): TransportExceptionInterface|JsonResponse
    {
        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject($this->translator->trans('Welcome to productdigitalpass', domain: 'messages'))
            ->htmlTemplate('Email/Security/welcome.html.twig');
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error($e);
            return $e;
        }

        return new JsonResponse('');
    }

    public function sendUserInvitationEmail(User $user): TransportExceptionInterface|JsonResponse
    {
        $inviteUrl = $this->router->generate('invite', [
            'token' => $user->getInvitationToken(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject($this->translator->trans('Invitation to manage a company', domain: 'messages'))
            ->htmlTemplate('Email/Security/userInvite.html.twig')
            ->context([
                'user_email' => $user->getEmail(),
                'invitation_link' => $inviteUrl,
            ]);
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error($e);
            return $e;
        }

        return new JsonResponse('');
    }

    public function sendCompanyInvitationEmail(Company $company): TransportExceptionInterface|JsonResponse
    {
        $inviteUrl = $this->router->generate('invite', [
            'token' => $company->getInvitationToken(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new TemplatedEmail())
            ->to($company->getEmail())
            ->subject($this->translator->trans('Company invitation to Aeon Trace', domain: 'messages'))
            ->htmlTemplate('Email/Security/companyInvite.html.twig')
            ->context([
                'user_email' => $company->getEmail(),
                'invitation_link' => $inviteUrl,
            ]);
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error($e);
            return $e;
        }

        return new JsonResponse('');
    }

    public function sendCompanyRegistrationEmail(Company $company): TransportExceptionInterface|JsonResponse
    {
        $inviteUrl = $this->router->generate('company_registration', [
            'token' => $company->getInvitationToken(),
            'email' => $company->getEmail(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new TemplatedEmail())
            ->to($company->getEmail())
            ->subject($this->translator->trans('Register your company at Aeon Trace', domain: 'messages'))
            ->htmlTemplate('Email/Security/companyRegistrationInvite.html.twig')
            ->context([
                'user_email' => $company->getEmail(),
                'invitation_link' => $inviteUrl,
            ]);
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error($e);
            return $e;
        }

        return new JsonResponse('');
    }
}
