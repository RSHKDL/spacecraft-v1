<?php

declare(strict_types=1);

namespace App\Domain\ShipDesign;

use App\Domain\Component\ComponentType;
use App\Domain\Ship\ShipClass;

final class ShipDesign
{
    private function __construct(
        private readonly ShipDesignId $id,
        private readonly array $components,
    ) {}

    /**
     * @param list<Component> $components
     */
    public static function draw(ShipDesignId $id, ShipClass $class, array $components): self
    {
        $reactorCount = self::countOf($components, ComponentType::Reactor);

        if (0 === $reactorCount) {
            throw new \DomainException('A ship design must have a reactor component');
        }

        foreach (ComponentType::cases() as $type) {
            if ($type->allowsMultiple()) {
                continue;
            }

            if (self::countOf($components, $type) > 1) {
                throw new \DomainException(sprintf('A ship design cannot have more than one %s component', $type->value));
            }
        }

        $auxiliaryCount = self::countOf($components, ComponentType::Auxiliary);

        if ($auxiliaryCount > $class->hullLayout()->getAuxiliarySlots()) {
            throw new \DomainException('A ship design cannot have more auxiliary components than its class has auxiliary slots');
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

    /**
     * @param list<Component> $components
     */
    private static function countOf(array $components, ComponentType $type): int
    {
        return count(array_filter(
            $components,
            static fn (Component $component): bool => $type === $component->getType(),
        ));
    }
}
