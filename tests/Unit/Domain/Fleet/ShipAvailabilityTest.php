<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Fleet;

use App\Domain\Fleet\FleetRepository;
use App\Domain\Fleet\ShipAvailability;
use App\Domain\Ship\ShipId;
use PHPUnit\Framework\TestCase;

final class ShipAvailabilityTest extends TestCase
{
    public function testAlreadyAssignedShipCannotBeAssignedToAnotherFleet(): void
    {
        $assignedShipId = ShipId::generate();

        // A mock check there was a call to the method, a stub just return a value.
        $fleetRepository = $this->createStub(FleetRepository::class);
        $fleetRepository->method('findAlreadyAssignedShipIds')->willReturn([$assignedShipId]);

        $availability = new ShipAvailability($fleetRepository);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageIs(sprintf(
            'These ships already serve in another fleet: %s',
            $assignedShipId->getValue(),
        ));
        $availability->ensureUnassigned([ShipId::generate(), $assignedShipId]);
    }

    public function testAvailableShipCanBeAssignedToAFleet(): void
    {
        $this->expectNotToPerformAssertions();

        // A mock check there was a call to the method, a stub just return a value.
        $fleetRepository = $this->createStub(FleetRepository::class);
        $fleetRepository->method('findAlreadyAssignedShipIds')->willReturn([]);

        new ShipAvailability($fleetRepository)->ensureUnassigned([ShipId::generate(), ShipId::generate()]);
    }
}
