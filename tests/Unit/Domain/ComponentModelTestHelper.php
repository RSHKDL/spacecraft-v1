<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain;

use App\Domain\Component\ComponentModel;
use App\Domain\Component\ComponentModelId;
use App\Domain\Component\ComponentType;
use App\Domain\Component\Power;
use App\Domain\Component\PowerConsumption;
use App\Domain\Component\PowerOutput;
use App\Domain\Ship\ShipClass;
use App\Domain\ShipDesign\Component;

trait ComponentModelTestHelper
{
    /**
     * @param list<ShipClass>|null $shipClasses
     */
    private static function releaseModel(
        ComponentType $type,
        ?array $shipClasses = null,
        ?Power $power = null,
    ): ComponentModel
    {
        $shipClasses ??= ShipClass::cases();

        $power ??= $type->suppliesPower()
            ? PowerOutput::create(100)
            : PowerConsumption::create(0);

        return ComponentModel::release(ComponentModelId::generate(), $type, $shipClasses, $power);
    }

    /**
     * @param list<ShipClass>|null $shipClasses
     */
    private static function createComponent(ComponentType $type, ?array $shipClasses = null, ?Power $power = null): Component
    {
        return Component::createFrom(self::releaseModel($type, $shipClasses, $power));
    }
}
