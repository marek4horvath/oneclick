<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Api\Filter\UidFilter;
use App\Api\Filter\UserFullNameOrderFilter;
use App\Controller\Registration\CheckEmailController;
use App\Controller\Security\ChangeCurrentPasswordController;
use App\Controller\Security\FirstLoginController;
use App\Controller\Security\RequestPasswordResetController;
use App\Controller\Security\ResetPasswordController;
use App\DataTransferObjects\Registration\EmailInput;
use App\DataTransferObjects\Security\ChangeCurrentPasswordInput;
use App\DataTransferObjects\Security\PasswordInput;
use App\DataTransferObjects\Security\RefreshTokenInput;
use App\DataTransferObjects\Security\RequestPasswordResetInput;
use App\Entity\Company\Company;
use App\Entity\Dpp\Dpp;
use App\Entity\Embeddable\Address;
use App\Entity\Product\ProductInputHistory;
use App\Entity\Product\ProductTemplate;
use App\Entity\Security\UserToken;
use App\Entity\Step\ProductStep;
use App\Repository\UserRepository;
use App\StateProcessors\AdminRegistrationProcessor;
use App\StateProcessors\FileDeleteProcessor;
use App\StateProcessors\ImageUploadProcessor;
use App\StateProcessors\UserRegistrationProcessor;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(
            uriTemplate: '/auth/login',
            normalizationContext: [],
            denormalizationContext: [],
        ),
        new Post(
            uriTemplate: '/auth/refresh-token',
            shortName: 'Security',
            normalizationContext: [],
            denormalizationContext: [],
            input: RefreshTokenInput::class,
        ),
        new Post(
            uriTemplate: '/auth/request-password-reset',
            controller: RequestPasswordResetController::class,
            shortName: 'Security',
            normalizationContext: [],
            denormalizationContext: [],
            input: RequestPasswordResetInput::class
        ),
        new Post(
            uriTemplate: '/auth/admin/registration',
            shortName: 'Security',
            normalizationContext: ['groups' => [self::USER_REGISTRATION_READ]],
            denormalizationContext: ['groups' => [self::USER_REGISTRATION_WRITE]],
            processor: AdminRegistrationProcessor::class
        ),
        new Post(
            uriTemplate: '/auth/user/registration',
            shortName: 'Security',
            normalizationContext: ['groups' => [self::USER_REGISTRATION_COMPANY_LINK_READ]],
            denormalizationContext: ['groups' => [self::USER_REGISTRATION_COMPANY_LINK_WRITE]],
            processor: UserRegistrationProcessor::class
        ),
        new Post(
            uriTemplate: '/auth/reset-password',
            controller: ResetPasswordController::class,
            shortName: 'Security',
            normalizationContext: [],
            denormalizationContext: [],
            input: PasswordInput::class
        ),
        new Post(
            uriTemplate: '/auth/first-login',
            controller: FirstLoginController::class,
            shortName: 'Security',
            normalizationContext: [],
            denormalizationContext: [],
            input: PasswordInput::class
        ),
        new Post(
            uriTemplate: '/auth/check-email',
            controller: CheckEmailController::class,
            shortName: 'Security',
            normalizationContext: [],
            denormalizationContext: [],
            input: EmailInput::class,
        ),
        new Post(
            uriTemplate: '/users/{id}/user_avatar',
            inputFormats: [
                'multipart' => ['multipart/form-data'],
            ],
            denormalizationContext: [
                'groups' => [self::USER_AVATAR_UPLOAD],
            ],
            processor: ImageUploadProcessor::class
        ),
        new Patch(
            uriTemplate: '/users/change-password',
            controller: ChangeCurrentPasswordController::class,
            normalizationContext: [],
            denormalizationContext: [],
            input: ChangeCurrentPasswordInput::class
        ),
        new Patch(),
        new Delete(),
        new Delete(
            uriTemplate: '/users/{id}/user_avatar',
            processor: FileDeleteProcessor::class,
        ),
    ],
    normalizationContext: [
        'groups' => [
            self::USER_READ,
        ],
    ],
    denormalizationContext: [
        'groups' => [
            self::USER_WRITE,
            Address::ADDRESS_WRITE,
        ],
    ],
    order: ['createdAt' => 'DESC'],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'roles' => 'exact',
])]
#[ApiFilter(filterClass: UidFilter::class, properties: ['company.id' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['email'])]
#[ApiFilter(SearchFilter::class, properties: ['email' => 'partial'])]
#[ApiFilter(UserFullNameOrderFilter::class, properties: ['order[name]'])]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap([
    'user' => User::class,
    'company' => Company::class,
])]
#[UniqueEntity(
    fields: ['email'],
    message: 'The email {{ value }} is already in use.'
)]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const USER_READ = 'user-read';
    public const USER_WRITE = 'user-write';
    public const USER_AVATAR_UPLOAD = 'user-avatar-upload';
    public const USER_REGISTRATION_READ = 'user-registration-read';
    public const USER_REGISTRATION_WRITE = 'user-registration-write';
    public const USER_REGISTRATION_COMPANY_LINK_READ = 'user-registration-company-link-read';
    public const USER_REGISTRATION_COMPANY_LINK_WRITE = 'user-registration-company-link-write';

    #[Vich\UploadableField(mapping: 'user_avatars', fileNameProperty: 'userAvatar')]
    #[Groups([self::USER_AVATAR_UPLOAD])]
    public ?File $file = null;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([
        self::USER_READ,
        Company::COMPANY_READ,
        Company::COMPANY_NAME,
        Company::COMPANY_READ_LISTING,
        Company::COMPANY_READ_DETAIL,
        ProductTemplate::PRODUCT_TEMPLATE_READ,
        Dpp::DPP_READ_DETAIL,
        ProductStep::STEP_READ,
    ])]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, unique: true, options: [
        'default' => '',
    ])]
    #[Groups([
        self::USER_READ,
        self::USER_REGISTRATION_READ,
        self::USER_REGISTRATION_WRITE,
        self::USER_REGISTRATION_COMPANY_LINK_READ,
        self::USER_REGISTRATION_COMPANY_LINK_WRITE,
        self::USER_WRITE,
        Company::COMPANY_READ,
        Company::COMPANY_WRITE,
        Company::COMPANY_EMAIL_INVITE,
        Company::COMPANY_READ_LISTING,
        Company::COMPANY_READ_DETAIL,
    ])]
    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email = '';

    #[ORM\Column(type: Types::STRING, options: [
        'default' => '',
    ])]
    #[Groups([
        self::USER_READ,
        self::USER_REGISTRATION_READ,
        Company::COMPANY_READ,
        Company::COMPANY_READ_DETAIL,
        self::USER_REGISTRATION_WRITE,
        self::USER_REGISTRATION_COMPANY_LINK_READ,
        self::USER_REGISTRATION_COMPANY_LINK_WRITE,
        self::USER_WRITE,
        ProductInputHistory::PRODUCT_INPUT_HISTORY_READ,
    ])]
    private string $firstName = '';

    #[ORM\Column(type: Types::STRING, options: [
        'default' => '',
    ])]
    #[Groups([
        self::USER_READ,
        self::USER_REGISTRATION_READ,
        Company::COMPANY_READ,
        Company::COMPANY_READ_DETAIL,
        self::USER_REGISTRATION_WRITE,
        self::USER_REGISTRATION_COMPANY_LINK_READ,
        self::USER_REGISTRATION_COMPANY_LINK_WRITE,
        self::USER_WRITE,
        ProductInputHistory::PRODUCT_INPUT_HISTORY_READ,
    ])]
    private string $lastName = '';

    #[ORM\Column(type: Types::STRING, nullable: true, options: [
        'default' => '',
    ])]
    #[Groups([
        self::USER_READ,
        self::USER_WRITE,
    ])]
    private ?string $userAvatar = '';

    #[ORM\Column(type: Types::STRING, options: [
        'default' => '',
    ])]
    #[Groups([
        self::USER_WRITE,
        self::USER_REGISTRATION_WRITE,
        Company::COMPANY_WRITE,
    ])]
    private string $password = '';

    #[ORM\Column(type: Types::STRING, options: [
        'default' => '',
    ])]
    private string $invitationToken = '';

    #[ORM\Column(type: Types::BOOLEAN, options: [
        'default' => false,
    ])]
    #[Groups([
        self::USER_READ,
        self::USER_REGISTRATION_READ,
    ])]
    private bool $active = false;

    #[ORM\ManyToOne(targetEntity: Company::class, cascade: ['persist'], inversedBy: 'users')]
    #[Groups([
        self::USER_READ,
        self::USER_WRITE,
        self::USER_REGISTRATION_COMPANY_LINK_READ,
        self::USER_REGISTRATION_COMPANY_LINK_WRITE,
    ])]
    private ?Company $company = null;

    #[ORM\Column(type: Types::STRING, options: ['default' => ''])]
    #[Groups([
        self::USER_READ,
        self::USER_WRITE,
        Company::COMPANY_READ,
        Company::COMPANY_WRITE,
        Company::COMPANY_READ_DETAIL,
    ])]
    private string $phone = '';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups([self::USER_READ])]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups([self::USER_READ])]
    private ?DateTimeImmutable $deletedAt = null;

    /**
     * @var array<string> $roles
     */
    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    #[Groups([
        self::USER_READ,
        self::USER_WRITE,
        Company::COMPANY_READ,
        Company::COMPANY_READ_DETAIL,
        self::USER_REGISTRATION_COMPANY_LINK_READ,
        self::USER_REGISTRATION_COMPANY_LINK_WRITE,
    ])]
    private array $roles = [];

    /**
     * @var Collection<int, UserToken> $userTokens
     */
    #[ORM\OneToMany(targetEntity: UserToken::class, mappedBy: 'tokenOwner', orphanRemoval: true)]
    private Collection $userTokens;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->userTokens = new ArrayCollection();
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(File $file): void
    {
        $this->file = $file;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getUserAvatar(): ?string
    {
        return $this->userAvatar;
    }

    public function setUserAvatar(?string $userAvatar): void
    {
        $this->userAvatar = $userAvatar;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getInvitationToken(): string
    {
        return $this->invitationToken;
    }

    public function setInvitationToken(string $invitationToken): void
    {
        $this->invitationToken = $invitationToken;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTimeImmutable $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }

    public function getRoles(): array
    {
        return [...$this->roles, 'ROLE_USER'];
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @return Collection<int, UserToken>
     */
    public function getUserTokens(): Collection
    {
        return $this->userTokens;
    }

    public function addUserToken(UserToken $userToken): self
    {
        if (!$this->userTokens->contains($userToken)) {
            $this->userTokens->add($userToken);
            $userToken->setTokenOwner($this);
        }

        return $this;
    }

    public function removeUserToken(UserToken $userToken): self
    {
        if ($this->userTokens->contains($userToken)) {
            $this->userTokens->removeElement($userToken);
        }

        return $this;
    }
}
