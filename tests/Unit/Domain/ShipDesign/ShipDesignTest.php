<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ShipDesign;

use App\Domain\Component\ComponentModel;
use App\Domain\Component\ComponentModelId;
use App\Domain\Component\ComponentType;
use App\Domain\ShipDesign\Component;
use App\Domain\ShipDesign\ShipDesign;
use App\Domain\ShipDesign\ShipDesignId;
use PHPUnit\Framework\TestCase;

final class ShipDesignTest extends TestCase
{
    public function testADesignWithoutAReactorIsRejected(): void
    {
        $components = [self::createComponent(ComponentType::Weapon), self::createComponent(ComponentType::Weapon)];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageIs('A ship design must have a reactor component');

        self::drawWith($components);
    }

    public function testADesignWithOnlyMandatoryComponentsIsAccepted(): void
    {
        $reactorComponent = self::createComponent(ComponentType::Reactor);
        $design = self::drawWith([$reactorComponent]);

        self::assertEquals([$reactorComponent], $design->getComponents());
    }

    /**
     * @param list<Component> $components
     */
    private static function drawWith(array $components): ShipDesign
    {
        return ShipDesign::draw(ShipDesignId::generate(), $components);
    }

    private static function createComponent(ComponentType $type): Component
    {
        return Component::createFrom(ComponentModel::release(ComponentModelId::generate(), $type));
    }
}
