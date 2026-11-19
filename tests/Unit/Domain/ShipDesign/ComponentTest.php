<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ShipDesign;

use App\Domain\Component\ComponentType;
use App\Domain\Component\PowerConsumption;
use App\Domain\ShipDesign\Component;
use App\Tests\Unit\Domain\ComponentModelTestHelper;
use PHPUnit\Framework\TestCase;

final class ComponentTest extends TestCase
{
    use ComponentModelTestHelper;

    public function testAComponentCopiesThePowerOfItsModel(): void
    {
        $componentModel = self::releaseModel(type: ComponentType::Auxiliary, power: PowerConsumption::create(12));
        $component = Component::createFrom($componentModel);

        self::assertEquals(PowerConsumption::create(12), $component->getPower());
    }
}
