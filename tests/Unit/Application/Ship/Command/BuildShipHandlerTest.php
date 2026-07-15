<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Ship\Command;

use App\Application\Ship\Command\BuildShip;
use App\Application\Ship\Command\BuildShipHandler;
use App\Domain\Ship\ShipClass;
use App\Domain\Ship\ShipId;
use App\Infrastructure\Ship\InMemoryShipRepository;
use PHPUnit\Framework\TestCase;

final class BuildShipHandlerTest extends TestCase
{
    public function testItBuildsAndPersistsAShipOfTheGivenClass(): void
    {
        $repository = new InMemoryShipRepository();

        $handler = new BuildShipHandler($repository);
        $shipId = ShipId::generate();

        $handler(new BuildShip($shipId, ShipClass::Corvette));

        self::assertCount(1, $repository->getShips());
        self::assertSame(ShipClass::Corvette, $repository->getShips()[0]->class);
    }
}
