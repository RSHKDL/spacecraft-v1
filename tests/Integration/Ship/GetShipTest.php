<?php

declare(strict_types=1);

namespace App\Tests\Integration\Ship;

use App\Application\Query\QueryBus;
use App\Application\Ship\Query\GetShip;
use App\Application\Ship\Query\ShipView;
use App\Domain\Ship\ShipClass;
use App\Domain\Ship\ShipId;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\ShipTestHelper;

final class GetShipTest extends IntegrationTestCase
{
    use ShipTestHelper;

    public function testPersistedShipsCanBeQueriedByTheirId(): void
    {
        $cruiserShipId = self::buildShip(ShipClass::Cruiser);
        $battleshipId = self::buildShip(ShipClass::Battleship);

        $queryBus = self::getContainer()->get(QueryBus::class);

        $battleship = $queryBus->ask(new GetShip($battleshipId));
        self::assertInstanceOf(ShipView::class, $battleship);
        self::assertEquals($battleshipId, $battleship->id);
        self::assertSame(ShipClass::Battleship, $battleship->class);

        $cruiser = $queryBus->ask(new GetShip($cruiserShipId));
        self::assertInstanceOf(ShipView::class, $cruiser);
        self::assertEquals($cruiserShipId, $cruiser->id);
        self::assertSame(ShipClass::Cruiser, $cruiser->class);
    }

    public function testQueryingAnUnknownShipIsNotAnError(): void
    {
        $shipId = ShipId::generate();

        $queryBus = self::getContainer()->get(QueryBus::class);
        $ship = $queryBus->ask(new GetShip($shipId));

        self::assertNull($ship);
    }
}
