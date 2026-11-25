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
        private array $supportedShipClasses,
        private Power $power,
    ) {}

    public static function createFrom(ComponentModel $model): self
    {
        return new self($model->getId(), $model->getType(), $model->getSupportedShipClasses(), $model->getPower());
    }

    /**
     * Rebuilds a component from storage, without its catalog model: the copy was
     * made when the component was fitted, and the catalog is never read again.
     * Reserved for persistence; the domain uses createFrom().
     *
     * @param list<ShipClass> $supportedShipClasses
     */
    public static function createFromStorage(
        ComponentModelId $modelId,
        ComponentType $type,
        array $supportedShipClasses,
        Power $power,
    ): self
    {
        return new self(
            $modelId,
            $type,
            $supportedShipClasses,
            $power,
        );
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
        return $this->supportedShipClasses;
    }

    public function getPower(): Power
    {
        return $this->power;
    }
}
