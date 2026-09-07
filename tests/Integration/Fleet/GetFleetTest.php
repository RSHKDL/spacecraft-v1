<?php

declare(strict_types=1);

namespace App\Tests\Integration\Fleet;

use App\Application\Fleet\Command\FormFleet;
use App\Application\Fleet\Query\FleetShipView;
use App\Application\Fleet\Query\FleetView;
use App\Application\Fleet\Query\GetFleet;
use App\Application\Query\QueryBus;
use App\Domain\Fleet\FleetId;
use App\Domain\Fleet\FleetName;
use App\Domain\Ship\HullStatus;
use App\Domain\Ship\ShipClass;
use App\Domain\Ship\ShipId;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\ShipTestHelper;

final class GetFleetTest extends IntegrationTestCase
{
    use ShipTestHelper;

    public function testPersistedFleetsCanBeQueriedByTheirId(): void
    {
        $bus = self::getContainer()->get('command.bus');

        $flagshipId = self::buildShip(ShipClass::Cruiser);
        $destroyerId = self::buildShip(ShipClass::Destroyer);

        $fleetId = FleetId::generate();
        $fleetName = FleetName::create('1st Fleet');
        $command = new FormFleet(
            $fleetId,
            $fleetName,
            [$flagshipId, $destroyerId],
            $flagshipId
        );

        $bus->dispatch($command);
        $this->entityManager->clear();

        $queryBus = self::getContainer()->get(QueryBus::class);

        /** @var FleetView $fleetView */
        $fleetView = $queryBus->ask(new GetFleet($fleetId));

        self::assertEquals($fleetName->getValue(), $fleetView->name);
        self::assertEquals($flagshipId, $fleetView->flagshipId);
        self::assertEqualsCanonicalizing(
            [$flagshipId, $destroyerId],
            array_map(static fn (FleetShipView $ship): ShipId => $ship->id, $fleetView->ships)
        );

        $flagshipView = current(array_filter(
            $fleetView->ships,
            static fn (FleetShipView $ship): bool => $ship->id->equals($flagshipId),
        ));

        self::assertSame(ShipClass::Cruiser, $flagshipView->class);
        self::assertNull($flagshipView->name);
        self::assertSame(HullStatus::Intact, $flagshipView->hullStatus);
    }

    public function testQueryingAnUnknownFleetIsNotAnError(): void
    {
        $queryBus = self::getContainer()->get(QueryBus::class);
        $fleet = $queryBus->ask(new GetFleet(FleetId::generate()));

        self::assertNull($fleet);
    }
}
