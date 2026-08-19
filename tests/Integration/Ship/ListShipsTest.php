<?php

declare(strict_types=1);

namespace App\Tests\Integration\Ship;

use App\Application\Query\QueryBus;
use App\Application\Ship\Query\ListShips;
use App\Domain\Ship\HullStatus;
use App\Domain\Ship\ShipClass;
use App\Tests\Integration\IntegrationTestCase;

final class ListShipsTest extends IntegrationTestCase
{
    use ShipTestHelper;

    public function testPersistedShipsCanBeQueriedAndListed(): void
    {
        $corvetteId = self::buildShip(ShipClass::Corvette);
        $cruiserId = self::buildShip(ShipClass::Cruiser);

        $queryBus = self::getContainer()->get(QueryBus::class);
        $shipsList = $queryBus->ask(new ListShips());

        // test both count and order of the $shipsList
        self::assertEquals([$corvetteId, $cruiserId], array_column($shipsList, 'id'));

        $corvette = self::viewOf($corvetteId, $shipsList);
        self::assertEquals($corvetteId, $corvette->id);
        self::assertSame(ShipClass::Corvette, $corvette->class);
        self::assertNull($corvette->name);
        self::assertSame(HullStatus::Intact, $corvette->hullStatus);

        $cruiser = self::viewOf($cruiserId, $shipsList);
        self::assertEquals($cruiserId, $cruiser->id);
        self::assertSame(ShipClass::Cruiser, $cruiser->class);
    }
}
