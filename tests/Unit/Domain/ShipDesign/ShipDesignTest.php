<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ShipDesign;

use App\Domain\Component\ComponentModel;
use App\Domain\Component\ComponentModelId;
use App\Domain\Component\ComponentType;
use App\Domain\Ship\ShipClass;
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

        self::drawWith(ShipClass::Frigate, $components);
    }

    public function testADesignWithMoreThanOneReactorIsRejected(): void
    {
        $components = [self::createComponent(ComponentType::Reactor), self::createComponent(ComponentType::Reactor)];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageIs('A ship design cannot have more than one reactor component');

        self::drawWith(ShipClass::Frigate, $components);
    }

    public function testADesignWithOnlyMandatoryComponentsIsAccepted(): void
    {
        $reactorComponent = self::createComponent(ComponentType::Reactor);
        $design = self::drawWith(ShipClass::Frigate, [$reactorComponent]);

        self::assertEquals([$reactorComponent], $design->getComponents());
    }

    public function testACorvetteDesignWithTwoAuxiliaryComponentsIsRejected(): void
    {
        $components = [
            self::createComponent(ComponentType::Reactor),
            self::createComponent(ComponentType::Auxiliary),
            self::createComponent(ComponentType::Auxiliary),
        ];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageIs('A ship design cannot have more auxiliary components than its class has auxiliary slots');

        self::drawWith(ShipClass::Corvette, $components);
    }

    /**
     * @param list<Component> $components
     */
    private static function drawWith(ShipClass $class, array $components): ShipDesign
    {
        return ShipDesign::draw(ShipDesignId::generate(), $class, $components);
    }

    private static function createComponent(ComponentType $type): Component
    {
        return Component::createFrom(ComponentModel::release(ComponentModelId::generate(), $type));
    }
}
