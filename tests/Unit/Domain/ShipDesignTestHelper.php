<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain;

use App\Domain\Ship\ShipClass;
use App\Domain\ShipDesign\Component;
use App\Domain\ShipDesign\ShipDesign;
use App\Domain\ShipDesign\ShipDesignId;
use App\Domain\ShipDesign\ShipDesignName;

trait ShipDesignTestHelper
{
    /**
     * @param list<Component> $components
     */
    private static function drawWith(ShipClass $class, array $components, string $name = 'Default'): ShipDesign
    {
        return ShipDesign::draw(ShipDesignId::generate(), ShipDesignName::create($name), $class, $components);
    }
}
