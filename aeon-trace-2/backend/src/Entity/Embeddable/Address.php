<?php

declare(strict_types=1);

namespace App\Entity\Embeddable;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Embeddable]
class Address
{
    public const ADDRESS_READ = 'address-read';
    public const ADDRESS_WRITE = 'address-write';

    #[ORM\Column(type: Types::STRING, nullable: true, options: [
        'default' => '',
    ])]
    #[Groups(groups: [
        self::ADDRESS_READ,
        self::ADDRESS_WRITE,
    ])]
    private ?string $street = '';

    #[ORM\Column(type: Types::STRING, nullable: true, options: [
        'default' => '',
    ])]
    #[Groups(groups: [
        self::ADDRESS_READ,
        self::ADDRESS_WRITE,
    ])]
    private ?string $city = '';

    #[ORM\Column(type: Types::STRING, nullable: true, options: [
        'default' => '',
    ])]
    #[Groups(groups: [
        self::ADDRESS_READ,
        self::ADDRESS_WRITE,
    ])]
    private ?string $postcode = '';

    #[ORM\Column(type: Types::STRING, nullable: true, options: [
        'default' => '',
    ])]
    #[Groups(groups: [
        self::ADDRESS_READ,
        self::ADDRESS_WRITE,
    ])]
    private ?string $houseNo = '';

    #[ORM\Column(type: Types::STRING, nullable: true, options: [
        'default' => '',
    ])]
    #[Groups(groups: [
        self::ADDRESS_READ,
        self::ADDRESS_WRITE,
    ])]
    private ?string $country = '';

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): void
    {
        $this->street = $street;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(?string $postcode): void
    {
        $this->postcode = $postcode;
    }

    public function getHouseNo(): ?string
    {
        return $this->houseNo;
    }

    public function setHouseNo(?string $houseNo): void
    {
        $this->houseNo = $houseNo;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }
}
