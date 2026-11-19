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
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[DataProvider('slotsLimitProvider')]
    public function testADesignWithMoreComponentsThanItsSlotsIsRejected(
        ShipClass $class,
        ComponentType $componentType,
        int $limit,
    ): void
    {
        $components = [
            self::createComponent(ComponentType::Reactor),
        ];

        for ($i = 0; $i < $limit + 1; $i++) {
            $components[] = self::createComponent($componentType);
        }

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageIs(sprintf(
            'A ship design cannot have more %s components than its class has %1$s slots',
            $componentType->value,
        ));

        self::drawWith($class, $components);
    }

    #[DataProvider('slotsLimitProvider')]
    public function testADesignFillingItsComponentSlotsIsAccepted(
        ShipClass $class,
        ComponentType $componentType,
        int $limit,
    ): void
    {
        $components = [
            self::createComponent(ComponentType::Reactor),
        ];

        for ($i = 0; $i < $limit; $i++) {
            $components[] = self::createComponent($componentType);
        }

        $design = self::drawWith($class, $components);

        self::assertEquals($components, $design->getComponents());
    }

    public static function slotsLimitProvider(): iterable
    {
        yield 'frigate weapons limit' => [ShipClass::Frigate, ComponentType::Weapon, 3];
        yield 'corvette weapons limit' => [ShipClass::Corvette, ComponentType::Weapon, 2];
        yield 'frigate auxiliaries limit' => [ShipClass::Frigate, ComponentType::Auxiliary, 2];
        yield 'corvette auxiliaries limit' => [ShipClass::Corvette, ComponentType::Auxiliary, 1];
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
