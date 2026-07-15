<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Ship;

use App\Domain\Ship\Ship;
use App\Domain\Ship\ShipClass;
use App\Domain\Ship\ShipId;
use App\Domain\Ship\ShipName;
use PHPUnit\Framework\TestCase;

final class ShipTest extends TestCase
{
    public function testANewlyBuiltShipStartsWithAFullHullForItsClass(): void
    {
        $ship = Ship::build(ShipId::generate(), ShipClass::Corvette);

        self::assertSame(100, $ship->hull->current());
        self::assertSame(100, $ship->hull->max());
    }

    public function testAShipCanBeChristenedWithAName(): void
    {
        $ship = Ship::build(ShipId::generate(), ShipClass::Cruiser);
        $ship->christen(ShipName::create('USS Enterprise'));

        self::assertSame('USS Enterprise', $ship->name->value());
    }

    public function testAShipCannotBeChristenedTwice(): void
    {
        $ship = Ship::build(ShipId::generate(), ShipClass::Cruiser);
        $ship->christen(ShipName::create('USS Enterprise'));

        $this->expectException(\DomainException::class);

        $ship->christen(ShipName::create('USS Voyager'));
    }
}
