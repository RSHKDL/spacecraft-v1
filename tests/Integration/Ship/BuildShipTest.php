<?php

declare(strict_types=1);

namespace App\Tests\Integration\Ship;

use App\Application\Ship\Command\BuildShip;
use App\Domain\Ship\Ship;
use App\Domain\Ship\ShipClass;
use App\Domain\Ship\ShipId;
use App\Tests\Integration\IntegrationTestCase;

class BuildShipTest extends IntegrationTestCase
{
    public function testAShipIsPersisted(): void
    {
        $bus = self::getContainer()->get('command.bus');

        $shipId = ShipId::generate();
        $command = new BuildShip($shipId, ShipClass::Corvette);
        $bus->dispatch($command);

        $this->entityManager->clear();
        /** @var Ship $ship */
        $ship = $this->entityManager->find(Ship::class, $shipId);
        $expectedShip = Ship::build($shipId, ShipClass::Corvette);

        self::assertEquals($expectedShip->class, $ship->class);
        self::assertEquals($expectedShip->hull->current(), $ship->hull->current());
        self::assertEquals($expectedShip->hull->max(), $ship->hull->max());
        self::AssertNull($ship->getName());
    }
}
