<?php

declare(strict_types=1);

namespace App\Domain\ShipDesign;

use App\Domain\Component\ComponentModel;
use App\Domain\Component\ComponentType;

final readonly class Component
{
    private function __construct(
        private ComponentType $type,
    ) {}

    public static function createFrom(ComponentModel $model): self
    {
        return new self($model->getType());
    }

    public function getType(): ComponentType
    {
        return $this->type;
    }
}
