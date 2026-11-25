<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Ship;

use App\Domain\Component\ComponentType;
use App\Domain\Ship\Ship;
use App\Domain\Ship\ShipClass;
use App\Domain\Ship\ShipId;
use App\Domain\Ship\ShipName;
use App\Tests\Unit\Domain\ComponentModelTestHelper;
use App\Tests\Unit\Domain\ShipDesignTestHelper;
use PHPUnit\Framework\TestCase;

final class ShipTest extends TestCase
{
    use ComponentModelTestHelper;
    use ShipDesignTestHelper;

    public function testANewlyBuiltShipStartsWithAFullHullForItsClass(): void
    {
        $design = self::drawWith(ShipClass::Corvette, [self::createComponent(ComponentType::Reactor)]);
        $ship = Ship::build(ShipId::generate(), $design);

        self::assertSame(100, $ship->hull->getCurrent());
        self::assertSame(100, $ship->hull->getMax());
    }

    public function testAShipCanBeChristenedWithAName(): void
    {
        $design = self::drawWith(ShipClass::Cruiser, [self::createComponent(ComponentType::Reactor)]);
        $ship = Ship::build(ShipId::generate(), $design);
        $ship->christen(ShipName::create('USS Enterprise'));

        self::assertSame('USS Enterprise', $ship->getName()->getValue());
    }

    public function testAShipCannotBeChristenedTwice(): void
    {
        $design = self::drawWith(ShipClass::Cruiser, [self::createComponent(ComponentType::Reactor)]);
        $ship = Ship::build(ShipId::generate(), $design);
        $ship->christen(ShipName::create('USS Enterprise'));

        $this->expectException(\DomainException::class);

        $ship->christen(ShipName::create('USS Voyager'));
    }

    public function testAShipIsBuiltFromADesignAndCopiesItsClassItsReferenceAndLoadout(): void
    {
        $components = [
            self::createComponent(ComponentType::Reactor),
            self::createComponent(ComponentType::Weapon),
            self::createComponent(ComponentType::Shield),
        ];

        $design = self::drawWith(ShipClass::Frigate, $components);
        $ship = Ship::build(ShipId::generate(), $design);

        self::assertSame(ShipClass::Frigate, $ship->getClass());
        self::assertSame(1, $ship->getDesignRevision()->getValue());
        self::assertEquals($design->getId(), $ship->getDesignId());
        self::assertEquals($components, $ship->getLoadout()->getComponents());
    }

    public function testRetrofittingAShipToTheLatestRevisionOfItsDesignCopiesItsReferenceAndLoadout(): void
    {
        $components = [
            self::createComponent(ComponentType::Reactor),
            self::createComponent(ComponentType::Weapon),
            self::createComponent(ComponentType::Shield),
        ];

        $design = self::drawWith(ShipClass::Frigate, $components);
        $ship = Ship::build(ShipId::generate(), $design);

        $updatedComponents = [
            self::createComponent(ComponentType::Reactor),
            self::createComponent(ComponentType::Weapon),
            self::createComponent(ComponentType::Weapon),
            self::createComponent(ComponentType::Shield),
        ];
        $design->revise($updatedComponents);
        $ship->retrofit($design);

        self::assertSame(2, $ship->getDesignRevision()->getValue());
        self::assertEquals($design->getId(), $ship->getDesignId());
        self::assertEquals($updatedComponents, $ship->getLoadout()->getComponents());
    }

    public function testRetrofittingAShipToADesignOfADifferentClassIsRejected(): void
    {
        $frigateComponents = [
            self::createComponent(ComponentType::Reactor),
        ];

        $frigateDesign = self::drawWith(ShipClass::Frigate, $frigateComponents);
        $ship = Ship::build(ShipId::generate(), $frigateDesign);

        $destroyerComponents = [
            self::createComponent(ComponentType::Reactor),
            self::createComponent(ComponentType::Weapon),
        ];

        $destroyerDesign = self::drawWith(ShipClass::Destroyer, $destroyerComponents);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageIs('A ship cannot be retrofitted to a different class');

        $ship->retrofit($destroyerDesign);
    }

    public function testRetrofittingAShipToAnotherDesignOfTheSameClassCopiesItsReferenceAndLoadout(): void
    {
        $components = [
            self::createComponent(ComponentType::Reactor),
            self::createComponent(ComponentType::Weapon),
            self::createComponent(ComponentType::Shield),
        ];

        $design = self::drawWith(ShipClass::Frigate, $components);
        $ship = Ship::build(ShipId::generate(), $design);

        $newDesignComponents = [
            self::createComponent(ComponentType::Reactor),
            self::createComponent(ComponentType::Weapon),
            self::createComponent(ComponentType::Weapon),
            self::createComponent(ComponentType::Shield),
        ];

        $newDesign = self::drawWith(ShipClass::Frigate, $newDesignComponents);

        $revisedComponents = [
            self::createComponent(ComponentType::Reactor),
            self::createComponent(ComponentType::Weapon),
            self::createComponent(ComponentType::Weapon),
            self::createComponent(ComponentType::Weapon),
            self::createComponent(ComponentType::Shield),
        ];
        $newDesign->revise($revisedComponents);

        $ship->retrofit($newDesign);

        self::assertSame(2, $ship->getDesignRevision()->getValue());
        self::assertEquals($newDesign->getId(), $ship->getDesignId());
        self::assertEquals($revisedComponents, $ship->getLoadout()->getComponents());
    }
}
