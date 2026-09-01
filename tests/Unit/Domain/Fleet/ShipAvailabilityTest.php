<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Fleet;

use App\Domain\Fleet\Fleet;
use App\Domain\Fleet\FleetId;
use App\Domain\Fleet\FleetName;
use App\Domain\Fleet\FleetRepository;
use App\Domain\Fleet\ShipAvailability;
use App\Domain\Ship\Ship;
use App\Domain\Ship\ShipClass;
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

    public function testAShipAlreadyInTheFleetItJoinsIsNotRejected(): void
    {
        $this->expectNotToPerformAssertions();

        $cruiser = Ship::build(ShipId::generate(), ShipClass::Cruiser);
        $flagShip = Ship::build(ShipId::generate(), ShipClass::Destroyer);
        $fleet = Fleet::form(
            FleetId::generate(),
            FleetName::create('1st Fleet'),
            [$cruiser->id, $flagShip->id],
            $flagShip->id
        );

        // A mock check there was a call to the method, a stub just return a value.
        $fleetRepository = $this->createStub(FleetRepository::class);
        $fleetRepository
            ->method('findAlreadyAssignedShipIds')
            ->willReturnCallback(static fn (array $shipIds): array => in_array($cruiser->id, $shipIds, true) ? [$cruiser->id,] : []);

        new ShipAvailability($fleetRepository)->ensureUnassigned([$cruiser->id,], $fleet);
    }
}
