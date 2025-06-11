<?php

declare(strict_types=1);

namespace App\Entity\Security;

use App\Entity\User;
use App\Repository\Security\UserTokenRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\String\ByteString;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserTokenRepository::class)]
class UserToken
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: [
        'default' => null,
    ])]
    private ?DateTimeImmutable $usedAt;

    #[ORM\Column(type: Types::STRING)]
    private string $token;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userTokens')]
    private ?User $tokenOwner = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->token = ByteString::fromRandom(12)->toString();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUsedAt(): ?DateTimeImmutable
    {
        return $this->usedAt;
    }

    public function setUsedAt(?DateTimeImmutable $usedAt): void
    {
        $this->usedAt = $usedAt;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function use(): void
    {
        $this->usedAt = new DateTimeImmutable();
    }

    public function getTokenOwner(): ?User
    {
        return $this->tokenOwner;
    }

    public function setTokenOwner(?User $tokenOwner): self
    {
        $this->tokenOwner = $tokenOwner;

        return $this;
    }
}
