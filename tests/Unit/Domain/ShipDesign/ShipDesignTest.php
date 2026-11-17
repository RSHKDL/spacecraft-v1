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
use PHPUnit\Framework\Attributes\TestWith;
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

    #[TestWith([ComponentType::Reactor], 'multiple reactors')]
    #[TestWith([ComponentType::Shield], 'multiple shields')]
    public function testADesignWithMoreThanOneUniqueComponentIsRejected(ComponentType $type): void
    {
        $components = [
            self::createComponent(ComponentType::Reactor),
            self::createComponent($type),
            self::createComponent($type),
        ];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageIs(sprintf('A ship design cannot have more than one %s component', $type->value));

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
     * @param list<ShipClass> $componentClasses
     */
    #[TestWith([ComponentType::Shield, [ShipClass::Corvette, ShipClass::Cruiser], ShipClass::Frigate], 'wrong shield class (unique component case)')]
    #[TestWith([ComponentType::Weapon, [ShipClass::Destroyer], ShipClass::Frigate], 'wrong weapon class (multiple component case)')]
    public function testADesignWithAComponentNotDesignedForItsClassIsRejected(
        ComponentType $componentType,
        array $componentClasses,
        ShipClass $shipClass,
    ): void
    {
        $components = [
            self::createComponent(ComponentType::Reactor, [$shipClass]),
            self::createComponent($componentType, $componentClasses),
        ];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageIs(sprintf(
            'A ship design cannot have a component not designed for its class (%s)',
            $componentType->value
        ));

        self::drawWith($shipClass, $components);
    }

    /**
     * @param list<Component> $components
     */
    private static function drawWith(ShipClass $class, array $components): ShipDesign
    {
        return ShipDesign::draw(ShipDesignId::generate(), $class, $components);
    }

    /**
     * @param list<ShipClass> $shipClasses
     */
    private static function createComponent(ComponentType $type, ?array $shipClasses = null): Component
    {
        if (null === $shipClasses) {
            $shipClasses = ShipClass::cases();
        }

        return Component::createFrom(ComponentModel::release(ComponentModelId::generate(), $type, $shipClasses));
    }
}
