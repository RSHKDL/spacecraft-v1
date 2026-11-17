<?php

declare(strict_types=1);

namespace App\Domain\Component;

use App\Domain\Ship\ShipClass;

final class ComponentModel
{
    private bool $discontinued = false;

    private function __construct(
        private readonly ComponentModelId $id,
        private readonly ComponentType $type,
        private readonly array $shipClasses,
    ) {}

    /**
     * @param list<ShipClass> $shipClasses
     */
    public static function release(ComponentModelId $id, ComponentType $type, array $shipClasses): self
    {
        if ([] === $shipClasses) {
            throw new \DomainException('A component model must support at least one ship class');
        }

        return new self($id, $type, $shipClasses);
    }

    public function discontinue(): void
    {
        $this->discontinued = true;
    }

    public function isDiscontinued(): bool
    {
        return $this->discontinued;
    }

    public function getType(): ComponentType
    {
        return $this->type;
    }

    /**
     * @return list<ShipClass>
     */
    public function getSupportedShipClasses(): array
    {
        return $this->shipClasses;
    }
}
