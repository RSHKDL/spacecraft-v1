<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Ship\Command;

use App\Application\Ship\Command\BuildShip;
use App\Application\Ship\Command\BuildShipHandler;
use App\Domain\Component\ComponentType;
use App\Domain\Ship\ShipClass;
use App\Domain\Ship\ShipId;
use App\Infrastructure\Ship\InMemoryShipRepository;
use App\Infrastructure\ShipDesign\InMemoryShipDesignRepository;
use App\Tests\Unit\Domain\ComponentModelTestHelper;
use App\Tests\Unit\Domain\ShipDesignTestHelper;
use PHPUnit\Framework\TestCase;

final class BuildShipHandlerTest extends TestCase
{
    use ShipDesignTestHelper;
    use ComponentModelTestHelper;

    /**
     * For this test we need at least two designs of the same class, so we can ensure a Ship is
     * built from the requested design and only the design ID can tell which one the ship was built
     * from. Loading the wrong design makes this test fail.
     */
    public function testItBuildsTheShipFromTheRequestedDesign(): void
    {
        $shipDesignRepository = new InMemoryShipDesignRepository();
        $shipRepository = new InMemoryShipRepository();

        $otherDesign = self::drawWith(ShipClass::Corvette, [self::createComponent(ComponentType::Reactor)]);
        $requestedDesign = self::drawWith(ShipClass::Corvette, [self::createComponent(ComponentType::Reactor)]);

        $shipDesignRepository->save($otherDesign);
        $shipDesignRepository->save($requestedDesign);

        $handler = new BuildShipHandler($shipRepository, $shipDesignRepository);
        $shipId = ShipId::generate();

        $handler(new BuildShip($shipId, $requestedDesign->getId()));

        self::assertCount(1, $shipRepository->getShips());
        self::assertEquals($requestedDesign->getId(), $shipRepository->getShips()[0]->getDesignId());
    }
}
