<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Dpp;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class InputJsonWrapper
{
    /** @var Collection<int, InputJson> */
    public Collection $inputs;

    public function __construct()
    {
        $this->inputs = new ArrayCollection();
    }

    /**
     * @return Collection<int, InputJson>
     */
    public function getInputs(): Collection
    {
        return $this->inputs;
    }

    public function addInput(InputJson $item): self
    {
        if (!$this->inputs->contains($item)) {
            $this->inputs->add($item);
        }

        return $this;
    }

    public function removeInput(InputJson $item): self
    {
        if ($this->inputs->contains($item)) {
            $this->inputs->removeElement($item);
        }

        return $this;
    }
}
