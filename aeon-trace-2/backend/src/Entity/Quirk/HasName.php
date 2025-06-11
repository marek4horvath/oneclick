<?php declare(strict_types=1);

namespace App\Entity\Quirk;

use Doctrine\ORM\Mapping as ORM;

/**
 * Define GET and SET methods for entities that needs to have '$name' property
 */
trait HasName
{
    #[ORM\Column(type: 'string', length: 255)]
    private string $name = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
