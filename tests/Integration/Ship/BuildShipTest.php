<?php

declare(strict_types=1);

namespace App\Tests\Integration\Ship;

use App\Application\Ship\Command\BuildShip;
use App\Domain\Component\ComponentType;
use App\Domain\Ship\Ship;
use App\Domain\Ship\ShipClass;
use App\Domain\Ship\ShipId;
use App\Domain\ShipDesign\ShipDesignRepository;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\ShipTestHelper;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

final class BuildShipTest extends IntegrationTestCase
{
    use ShipTestHelper;

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function testAShipIsPersisted(): void
    {
        $shipDesignRepository = self::getContainer()->get(ShipDesignRepository::class);
        $bus = self::getContainer()->get('command.bus');

        $design = self::drawWith(ShipClass::Corvette, [self::createComponent(ComponentType::Reactor)]);

        $shipDesignRepository->save($design);

        // Flush and detach the design so the handler has to load it back from the
        // database by its id, rather than find it in the identity map.
        $this->entityManager->flush();
        $this->entityManager->clear();

        $shipId = ShipId::generate();
        $command = new BuildShip($shipId, $design->getId());
        $bus->dispatch($command);

        $this->entityManager->clear();

        /** @var Ship $ship */
        $ship = $this->entityManager->find(Ship::class, $shipId);
        $expectedShip = Ship::build($shipId, $design);

        self::assertEquals($expectedShip->class, $ship->class);
        self::assertEquals($design->getId(), $ship->getDesignId());
        self::assertEquals($expectedShip->hull->getCurrent(), $ship->hull->getCurrent());
        self::assertEquals($expectedShip->hull->getMax(), $ship->hull->getMax());
        self::AssertNull($ship->getName());
    }
}
