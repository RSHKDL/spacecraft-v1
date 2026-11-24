<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ShipDesign;

use App\Domain\Component\ComponentType;
use App\Domain\Component\PowerConsumption;
use App\Domain\Component\PowerOutput;
use App\Domain\Ship\ShipClass;
use App\Domain\ShipDesign\Component;
use App\Domain\ShipDesign\ShipDesign;
use App\Domain\ShipDesign\ShipDesignId;
use App\Domain\ShipDesign\ShipDesignName;
use App\Tests\Unit\Domain\ComponentModelTestHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class ShipDesignTest extends TestCase
{
    use ComponentModelTestHelper;

    public function testADesignWithoutAReactorIsRejected(): void
    {
        $components = [self::createComponent(type: ComponentType::Weapon), self::createComponent(type: ComponentType::Weapon)];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageIs('A ship design must have a reactor component');

        self::drawWith(ShipClass::Frigate, $components);
    }

    #[TestWith([ComponentType::Reactor], 'multiple reactors')]
    #[TestWith([ComponentType::Shield], 'multiple shields')]
    public function testADesignWithMoreThanOneUniqueComponentIsRejected(ComponentType $type): void
    {
        $components = [
            self::createComponent(type: ComponentType::Reactor),
            self::createComponent(type: $type),
            self::createComponent(type: $type),
        ];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageIs(sprintf('A ship design cannot have more than one %s component', $type->value));

        self::drawWith(ShipClass::Frigate, $components);
    }

    public function testADesignWithOnlyMandatoryComponentsIsAccepted(): void
    {
        $reactorComponent = self::createComponent(type: ComponentType::Reactor);
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
            self::createComponent(type: ComponentType::Reactor),
        ];

        for ($i = 0; $i < $limit + 1; $i++) {
            $components[] = self::createComponent(type: $componentType);
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
            self::createComponent(type: ComponentType::Reactor),
        ];

        for ($i = 0; $i < $limit; $i++) {
            $components[] = self::createComponent(type: $componentType);
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
            self::createComponent(type: ComponentType::Reactor, shipClasses: [$shipClass]),
            self::createComponent(type: $componentType, shipClasses: $componentClasses),
        ];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageIs(sprintf(
            'A ship design cannot have a component not designed for its class (%s)',
            $componentType->value
        ));

        self::drawWith($shipClass, $components);
    }

    public function testADesignWithANegativePowerBalanceIsRejected(): void
    {
        $components = [
            self::createComponent(type: ComponentType::Reactor, power: PowerOutput::create(75)),
            self::createComponent(type: ComponentType::Weapon, power: PowerConsumption::create(76)),
        ];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageIs('The power balance of a ship design cannot be negative');

        self::drawWith(ShipClass::Corvette, $components);
    }

    public function testADesignWithAZeroPowerBalanceIsAccepted(): void
    {
        $components = [
            self::createComponent(type: ComponentType::Reactor, power: PowerOutput::create(75)),
            self::createComponent(type: ComponentType::Weapon, power: PowerConsumption::create(75)),
        ];

        $design = self::drawWith(ShipClass::Corvette, $components);

        self::assertEquals($components, $design->getComponents());
    }

    public function testADesignKeepsItsNameAndClass(): void
    {
        $components = [
            self::createComponent(type: ComponentType::Reactor),
        ];

        $design = self::drawWith(ShipClass::Frigate, $components, 'Harbinger');

        self::assertSame(ShipClass::Frigate, $design->getClass());
        self::assertSame('Harbinger', $design->getName()->getValue());
    }

    public function testANewDesignIsAtRevisionOne(): void
    {
        $components = [
            self::createComponent(type: ComponentType::Reactor),
        ];

        $design = self::drawWith(ShipClass::Frigate, $components);

        self::assertSame(1, $design->getRevision()->getValue());
    }

    public function testARevisionReplacesTheComponentsAndIncrementsTheRevision(): void
    {
        $components = [
            self::createComponent(type: ComponentType::Reactor),
            self::createComponent(type: ComponentType::Weapon),
        ];

        $design = self::drawWith(ShipClass::Frigate, $components);

        $updatedComponents = [
            self::createComponent(type: ComponentType::Reactor),
            self::createComponent(type: ComponentType::Shield),
            self::createComponent(type: ComponentType::Auxiliary),
        ];

        $design->revise($updatedComponents);

        self::assertSame(2, $design->getRevision()->getValue());
        self::assertEquals($updatedComponents, $design->getComponents());
    }

    public function testARevisionWithoutAReactorIsRejected(): void
    {
        $components = [
            self::createComponent(type: ComponentType::Reactor),
        ];

        $design = self::drawWith(ShipClass::Frigate, $components);

        $updatedComponents = [
            self::createComponent(type: ComponentType::Weapon),
        ];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageIs('A ship design must have a reactor component');

        $design->revise($updatedComponents);
    }

    public function testARevisionWithTheSameComponentModelsLeavesTheRevisionUnchanged(): void
    {
        $model = self::releaseModel(ComponentType::Reactor);
        $design = self::drawWith(ShipClass::Frigate, [Component::createFrom($model)]);

        $design->revise([Component::createFrom($model)]);

        self::assertSame(1, $design->getRevision()->getValue());
    }

    public function testARevisionWithTheSameComponentModelsInAnotherOrderLeavesTheRevisionUnchanged(): void
    {
        $reactorModel = self::releaseModel(ComponentType::Reactor);
        $weaponModel = self::releaseModel(ComponentType::Weapon);

        $design = self::drawWith(ShipClass::Frigate, [
            Component::createFrom($reactorModel),
            Component::createFrom($weaponModel),
        ]);

        $design->revise([
            Component::createFrom($weaponModel),
            Component::createFrom($reactorModel),
        ]);

        self::assertSame(1, $design->getRevision()->getValue());
    }

    public function testARevisionRemovingOneOfTwoComponentsOfTheSameModelIncrementsTheRevision(): void
    {
        $reactorModel = self::releaseModel(ComponentType::Reactor);
        $weaponModel = self::releaseModel(ComponentType::Weapon);

        $design = self::drawWith(ShipClass::Frigate, [
            Component::createFrom($reactorModel),
            Component::createFrom($weaponModel),
            Component::createFrom($weaponModel),
        ]);

        $design->revise([
            Component::createFrom($reactorModel),
            Component::createFrom($weaponModel),
        ]);

        self::assertSame(2, $design->getRevision()->getValue());
    }

    public function testARevisionSwappingAModelForAnotherWithTheSameStatsIncrementsTheRevision(): void
    {
        $weaponModel = self::releaseModel(ComponentType::Weapon);
        $aReactorModel = self::releaseModel(ComponentType::Reactor, power: PowerOutput::create(75));
        $anotherReactorModel = self::releaseModel(ComponentType::Reactor, power: PowerOutput::create(75));

        $components = [
            Component::createFrom($weaponModel),
            Component::createFrom($aReactorModel),
        ];

        $design = self::drawWith(ShipClass::Frigate, $components);

        $updatedComponents = [
            Component::createFrom($weaponModel),
            Component::createFrom($anotherReactorModel),
        ];

        $design->revise($updatedComponents);

        self::assertSame(2, $design->getRevision()->getValue());
    }

    /**
     * @param list<Component> $components
     */
    private static function drawWith(ShipClass $class, array $components, string $name = 'Default'): ShipDesign
    {
        return ShipDesign::draw(ShipDesignId::generate(), ShipDesignName::create($name), $class, $components);
    }
}
