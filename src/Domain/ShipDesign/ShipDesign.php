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

        $layout = $class->hullLayout();

        foreach (ComponentType::cases() as $type) {
            $componentCount = self::countOf($components, $type);

            if ($componentCount > $layout->getSlotsFor($type)) {
                if ($type->allowsMultiple()) {
                    throw new \DomainException(sprintf(
                        'A ship design cannot have more %s components than its class has %1$s slots',
                        $type->value,
                    ));
                }

                throw new \DomainException(sprintf('A ship design cannot have more than one %s component', $type->value));
            }
        }

        foreach ($components as $component) {
            if (!in_array($class, $component->getSupportedShipClasses(), true)) {
                throw new \DomainException(
                    sprintf(
                        'A ship design cannot have a component not designed for its class (%s)',
                        $component->getType()->value
                    )
                );
            }
        }

        if (PowerBalance::of($components)->getValue() < 0) {
            throw new \DomainException('The power balance of a ship design cannot be negative');
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
