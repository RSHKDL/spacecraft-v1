<?php

declare(strict_types=1);

namespace App\Domain\ShipDesign;

use App\Domain\Component\ComponentType;

final class ShipDesign
{
    private function __construct(
        private readonly ShipDesignId $id,
        private readonly array $components,
    ) {}

    /**
     * @param list<Component> $components
     */
    public static function draw(ShipDesignId $id, array $components): self
    {
        if (!array_any($components, static fn (Component $component): bool => ComponentType::Reactor === $component->getType())) {
            throw new \DomainException('A ship design must have a reactor component');
        }

        return new self($id, $components);
    }

    /**
     * @return list<Component>
     */
    public function getComponents(): array
    {
        return $this->components;
    }
}
