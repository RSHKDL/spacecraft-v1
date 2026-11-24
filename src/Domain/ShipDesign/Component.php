<?php

declare(strict_types=1);

namespace App\Domain\ShipDesign;

use App\Domain\Component\ComponentModel;
use App\Domain\Component\ComponentModelId;
use App\Domain\Component\ComponentType;
use App\Domain\Component\Power;
use App\Domain\Ship\ShipClass;

final readonly class Component
{
    private function __construct(
        private ComponentModelId $modelId,
        private ComponentType $type,
        private array $shipClasses,
        private Power $power,
    ) {}

    public static function createFrom(ComponentModel $model): self
    {
        return new self($model->getId(), $model->getType(), $model->getSupportedShipClasses(), $model->getPower());
    }

    public function getModelId(): ComponentModelId
    {
        return $this->modelId;
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

    public function getPower(): Power
    {
        return $this->power;
    }
}
