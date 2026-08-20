<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Fleet;

use App\Domain\Fleet\FleetId;
use App\Domain\Fleet\FleetName;
use App\Domain\Ship\ShipId;
use App\Domain\Fleet\Fleet;
use PHPUnit\Framework\TestCase;

final class FleetTest extends TestCase
{
    private const string FLEET_NAME = 'Star Fleet Battle Group Omega';

    public function testANewlyFormedFleetIsValid(): void
    {
        $flagshipId = ShipId::generate();
        $ships = [ShipId::generate(), ShipId::generate(), $flagshipId];

        $fleet = Fleet::form(
            FleetId::generate(),
            FleetName::create(self::FLEET_NAME),
            $ships,
            $flagshipId,
        );

        self::assertSame(3, $fleet->countShips());
        self::assertEquals($flagshipId, $fleet->getFlagshipId());
        self::assertEquals(FleetName::create(self::FLEET_NAME), $fleet->getName());
    }

    public function testANewlyFormedFleetMustHaveAtLeastTwoShips(): void
    {
        $flagshipId = ShipId::generate();
        $shipIds = [$flagshipId];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageIs('A fleet must have at least two ships');
        self::formFleet($shipIds, $flagshipId);
    }

    public function testANewlyFormedFleetMustHaveAFlagshipThatIsPartOfTheFleet(): void
    {
        $shipIds = [ShipId::generate(), ShipId::generate(), ShipId::generate()];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageIs('The flagship must be part of the fleet');
        self::formFleet($shipIds, ShipId::generate());
    }

    public function testANewlyFormedFleetCannotEnlistTheSameShipTwice(): void
    {
        $flagshipId = ShipId::generate();
        $shipIds = [$flagshipId, $flagshipId];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageIs('A fleet cannot enlist the same ship twice');
        self::formFleet($shipIds, $flagshipId);
    }

    private static function formFleet(array $shipIds, ShipId $flagshipId): void
    {
        Fleet::form(
            FleetId::generate(),
            FleetName::create(self::FLEET_NAME),
            $shipIds,
            $flagshipId,
        );
    }
}
