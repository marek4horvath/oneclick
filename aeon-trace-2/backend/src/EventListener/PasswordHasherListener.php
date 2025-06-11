<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use TypeError;

class PasswordHasherListener implements EventSubscriber
{
    private UserPasswordHasherInterface $hasher;

    private LoggerInterface $logger;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        LoggerInterface $logger
    ) {
        $this->hasher = $passwordHasher;
        $this->logger = $logger;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof User) {
            return;
        }

        $this->logger->info('PasswordHasherListener prePersist called for User entity.');

        if (is_string($entity->getPassword())) {
            $entity->setPassword($this->hasher->hashPassword($entity, $entity->getPassword()));
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof User) {
            return;
        }
        if ($args->hasChangedField('password')) {
            $newPassword = $args->getNewValue('password');
            if (is_string($newPassword)) {
                $args->setNewValue('password', $this->hasher->hashPassword($entity, $newPassword));
            } else {
                throw new TypeError('Expected password to be a string, ' . gettype($newPassword) . ' given.');
            }
        }
    }
}
