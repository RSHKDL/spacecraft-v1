<?php

declare(strict_types=1);

namespace App\Tests\Integration\Ship;

use App\Application\Ship\Command\ChristenShip;
use App\Domain\Ship\Ship;
use App\Domain\Ship\ShipClass;
use App\Domain\Ship\ShipName;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\ShipTestHelper;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

final class ChristenShipTest extends IntegrationTestCase
{
    use ShipTestHelper;

    private const string FAMOUS_SHIP_NAME = 'Millennium Falcon';

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function testAShipIsChristened(): void
    {
        $bus = self::getContainer()->get('command.bus');
        $shipId = self::buildShip(ShipClass::Cruiser);

        $command = new ChristenShip($shipId, ShipName::create(self::FAMOUS_SHIP_NAME));
        $bus->dispatch($command);

        $this->entityManager->clear();

        /** @var Ship $ship */
        $ship = $this->entityManager->find(Ship::class, $shipId);

        self::assertEquals(ShipName::create(self::FAMOUS_SHIP_NAME), $ship->getName());
    }
}
