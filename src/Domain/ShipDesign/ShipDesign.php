<?php

declare(strict_types=1);

namespace App\Domain\ShipDesign;

use App\Domain\Component\ComponentType;
use App\Domain\Ship\ShipClass;

final class ShipDesign
{
    private function __construct(
        private readonly ShipDesignId $id,
        private readonly ShipDesignName $name,
        private readonly ShipClass $class,
        private ShipDesignRevision $revision,
        private array $components,
    ) {}

    /**
     * @param list<Component> $components
     */
    public static function draw(ShipDesignId $id, ShipDesignName $name, ShipClass $class, array $components): self
    {
        self::assertValid($class, $components);

        return new self($id, $name, $class, ShipDesignRevision::first(), $components);
    }

    /**
     * @param list<Component> $components
     */
    public function revise(array $components): void
    {
        if (self::modelIdsOf($components) === self::modelIdsOf($this->components)) {
            return;
        }

        self::assertValid($this->class, $components);

        $this->revision = $this->revision->next();
        $this->components = $components;
    }

    /**
     * @return list<Component>
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    public function getName(): ShipDesignName
    {
        return $this->name;
    }

    public function getClass(): ShipClass
    {
        return $this->class;
    }

    public function getRevision(): ShipDesignRevision
    {
        return $this->revision;
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

    /**
     * @param list<Component> $components
     */
    private static function assertValid(ShipClass $class, array $components): void
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
    }

    /**
     * Return a sorted array of component model ids
     *
     * @param list<Component> $components
     * @return list<string>
     */
    private static function modelIdsOf(array $components): array
    {
        $ids = array_map(static fn (Component $component): string => $component->getModelId()->getValue(), $components);
        sort($ids);

        return $ids;
    }
}
