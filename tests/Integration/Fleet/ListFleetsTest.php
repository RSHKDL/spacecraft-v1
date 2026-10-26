<?php

declare(strict_types=1);

namespace App\Tests\Integration\Fleet;

use App\Application\Fleet\Query\ListFleets;
use App\Application\Query\QueryBus;
use App\Tests\Integration\FleetTestHelper;
use App\Tests\Integration\IntegrationTestCase;

final class ListFleetsTest extends IntegrationTestCase
{
    use FleetTestHelper;

    public function testPersistedFleetsCanBeQueriedAndListed(): void
    {
        $firstFleetId = self::formFleet('Zulu', 10);
        $secondFleetId = self::formFleet('Bravo', 2);

        $queryBus = self::getContainer()->get(QueryBus::class);
        $fleetList = $queryBus->ask(new ListFleets());

        // test both count and order of the $fleetList
        self::assertEquals([$secondFleetId, $firstFleetId], array_column($fleetList, 'id'));

        $firstFleet = self::fleetViewOf($firstFleetId, $fleetList);
        self::assertSame('Zulu', $firstFleet->name);
        self::assertSame(10, $firstFleet->shipCount);

        $secondFleet = self::fleetViewOf($secondFleetId, $fleetList);
        self::assertSame('Bravo', $secondFleet->name);
        self::assertSame(2, $secondFleet->shipCount);
    }

    public function testAFleetWithNoShipLeftIsStillListed(): void
    {
        $firstFleetId = self::formFleet('Alpha', 2);
        $queryBus = self::getContainer()->get(QueryBus::class);
        self::detachAllShips($firstFleetId);

        $fleetList = $queryBus->ask(new ListFleets());
        $firstFleet = self::fleetViewOf($firstFleetId, $fleetList);
        self::assertSame(0, $firstFleet->shipCount);
    }
}
