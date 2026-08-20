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

    public function testANewlyFormedFleetCannotAssignTheSameShipTwice(): void
    {
        $flagshipId = ShipId::generate();
        $shipIds = [$flagshipId, $flagshipId];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageIs('A fleet cannot assign the same ship twice');
        self::formFleet($shipIds, $flagshipId);
    }

    public function testAShipCanBePromotedToFlagship(): void
    {
        $flagshipId = ShipId::generate();
        $futureFlagshipId = ShipId::generate();
        $shipIds = [ShipId::generate(), ShipId::generate(), $flagshipId, $futureFlagshipId];

        $fleet = self::formFleet($shipIds, $flagshipId);
        $fleet->promoteToFlagship($futureFlagshipId);

        self::assertEquals($futureFlagshipId, $fleet->getFlagshipId());
        self::assertSame(4, $fleet->countShips());
    }

    public function testAShipOutsideTheFleetCannotBePromotedToFlagship(): void
    {
        $flagshipId = ShipId::generate();
        $shipIds = [ShipId::generate(), ShipId::generate(), $flagshipId];

        $fleet = self::formFleet($shipIds, $flagshipId);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageIs('The flagship must be part of the fleet');
        $fleet->promoteToFlagship(ShipId::generate());
    }

    public function testADetachedShipLeavesTheFleet(): void
    {
        $flagshipId = ShipId::generate();
        $leavingShipId = ShipId::generate();
        $shipIds = [ShipId::generate(), $leavingShipId, $flagshipId];

        $fleet = self::formFleet($shipIds, $flagshipId);
        self::assertSame(3, $fleet->countShips());

        $fleet->detach(ShipId::fromString($leavingShipId->getValue()));
        self::assertSame(2, $fleet->countShips());
        self::assertNotContains($leavingShipId->getValue(), array_map(
            static fn (ShipId $shipId): string => $shipId->getValue(),
            $fleet->getShipIds(),
        ));
    }

    public function testDetachingTheFlagshipLeavesTheFleetWithoutOne(): void
    {
        $flagshipId = ShipId::generate();
        $shipIds = [ShipId::generate(), ShipId::generate(), $flagshipId];

        $fleet = self::formFleet($shipIds, $flagshipId);

        $fleet->detach(ShipId::fromString($flagshipId->getValue()));
        self::assertSame(2, $fleet->countShips());
        self::assertNull($fleet->getFlagshipId());
    }

    public function testAFleetCanBeReducedToASingleShip(): void
    {
        $flagshipId = ShipId::generate();
        $leavingShipId = ShipId::generate();
        $shipIds = [$leavingShipId, $flagshipId];

        $fleet = self::formFleet($shipIds, $flagshipId);

        $fleet->detach(ShipId::fromString($leavingShipId->getValue()));
        self::assertSame(1, $fleet->countShips());
    }

    public function testDetachingAShipOutsideTheFleetIsANoOp(): void
    {
        $flagshipId = ShipId::generate();
        $fleet = self::formFleet([ShipId::generate(), $flagshipId], $flagshipId);

        $fleet->detach(ShipId::generate());

        self::assertSame(2, $fleet->countShips());
    }

    public function testAShipCanBeAssignedToAFleet(): void
    {
        $flagshipId = ShipId::generate();
        $fleet = self::formFleet([ShipId::generate(), $flagshipId], $flagshipId);
        self::assertSame(2, $fleet->countShips());

        $newShipId = ShipId::generate();
        $fleet->assign($newShipId);
        self::assertSame(3, $fleet->countShips());
        self::assertContains($newShipId->getValue(), array_map(
            static fn (ShipId $shipId): string => $shipId->getValue(),
            $fleet->getShipIds(),
        ));
    }

    public function testAFleetCannotAssignTheSameShipTwice(): void
    {
        $flagshipId = ShipId::generate();
        $aShipId = ShipId::generate();
        $fleet = self::formFleet([$aShipId, $flagshipId], $flagshipId);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageIs('A fleet cannot assign the same ship twice');
        $fleet->assign(ShipId::fromString($aShipId->getValue()));
    }

    private static function formFleet(array $shipIds, ShipId $flagshipId): Fleet
    {
        return Fleet::form(
            FleetId::generate(),
            FleetName::create(self::FLEET_NAME),
            $shipIds,
            $flagshipId,
        );
    }
}
