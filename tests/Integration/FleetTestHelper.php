<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Application\Fleet\Command\DetachShip;
use App\Application\Fleet\Command\FormFleet;
use App\Application\Fleet\Query\FleetListView;
use App\Domain\Fleet\Fleet;
use App\Domain\Fleet\FleetId;
use App\Domain\Fleet\FleetName;
use App\Domain\Fleet\FleetRepository;
use App\Domain\Ship\ShipClass;

trait FleetTestHelper
{
    use ShipTestHelper;

    protected static function formFleet(string $name, int $shipCount): FleetId
    {
        $fleetId = FleetId::generate();
        $shipIds = [];

        for ($i = 0; $i < $shipCount; ++$i) {
            $shipIds[] = self::buildShip(ShipClass::Frigate);
        }

        $formFleetCommand = new FormFleet(
            $fleetId,
            FleetName::create($name),
            $shipIds,
            $shipIds[0]
        );

        self::getContainer()->get('command.bus')->dispatch($formFleetCommand);

        return $fleetId;
    }

    protected static function detachAllShips(FleetId $fleetId): void
    {
        /** @var Fleet $fleet */
        $fleet = self::getContainer()->get(FleetRepository::class)->get($fleetId);
        $shipIds = $fleet->getShipIds();
        foreach ($shipIds as $shipId) {
            self::getContainer()->get('command.bus')->dispatch(new DetachShip($fleetId, $shipId));
        }
    }

    protected static function fleetViewOf(FleetId $fleetId, array $views): FleetListView
    {
        /** @var FleetListView $view */
        foreach ($views as $view) {
            if ($view->id->equals($fleetId)) {
                return $view;
            }
        }

        self::fail(sprintf('No view found for fleet "%s".', $fleetId));
    }
}
