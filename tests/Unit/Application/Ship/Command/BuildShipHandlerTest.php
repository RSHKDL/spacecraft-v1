<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Ship\Command;

use App\Application\Ship\Command\BuildShip;
use App\Application\Ship\Command\BuildShipHandler;
use App\Domain\Ship\Ship;
use App\Domain\Ship\ShipClass;
use App\Domain\Ship\ShipId;
use App\Domain\Ship\ShipRepository;
use PHPUnit\Framework\TestCase;

final class BuildShipHandlerTest extends TestCase
{
    public function testItBuildsAndPersistsAShipOfTheGivenClass(): void
    {
        $repository = new class implements ShipRepository {
            public ?Ship $saved = null;
            public function save(Ship $ship): void { $this->saved = $ship; }
        };

        $handler = new BuildShipHandler($repository);
        $shipId = ShipId::generate();

        $handler(new BuildShip($shipId, ShipClass::Corvette));

        self::assertNotNull($repository->saved);
        self::assertSame('corvette', $repository->saved?->class->value);
    }
}
