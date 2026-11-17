<?php

declare(strict_types=1);

namespace App\Domain\ShipDesign;

use App\Domain\Component\ComponentModel;
use App\Domain\Component\ComponentType;
use App\Domain\Ship\ShipClass;

final readonly class Component
{
    private function __construct(
        private ComponentType $type,
        private array $shipClasses,
    ) {}

    public static function createFrom(ComponentModel $model): self
    {
        return new self($model->getType(), $model->getSupportedShipClasses());
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
