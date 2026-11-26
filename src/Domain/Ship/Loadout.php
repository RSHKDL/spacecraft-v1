<?php

declare(strict_types=1);

namespace App\Domain\Ship;

use App\Domain\ShipDesign\Component;

final readonly class Loadout
{
    private function __construct(
        /** @var list<Component> */
        private array $components,
    ) {}

    /**
     * @param list<Component> $components
     */
    public static function of(array $components): self
    {
        return new self($components);
    }

    /**
     * @return list<Component>
     */
    public function getComponents(): array
    {
        return $this->components;
    }
}
