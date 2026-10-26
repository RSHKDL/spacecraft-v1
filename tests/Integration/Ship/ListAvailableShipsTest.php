<?php

declare(strict_types=1);

namespace App\Tests\Integration\Ship;

use App\Application\Query\QueryBus;
use App\Application\Ship\Query\ListAvailableShips;
use App\Domain\Ship\ShipClass;
use App\Tests\Integration\FleetTestHelper;
use App\Tests\Integration\IntegrationTestCase;

final class ListAvailableShipsTest extends IntegrationTestCase
{
    use FleetTestHelper;

    public function testOnlyUnassignedShipsAreListed(): void
    {
        self::formFleet('First Fleet', 2);

        $corvetteId = self::buildShip(ShipClass::Corvette);
        $cruiserId = self::buildShip(ShipClass::Cruiser);

        $queryBus = self::getContainer()->get(QueryBus::class);
        $availableShipsList = $queryBus->ask(new ListAvailableShips());

        // test that only both ships without fleet are queried
        self::assertEquals([$corvetteId, $cruiserId], array_column($availableShipsList, 'id'));
    }
}
