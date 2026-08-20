<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shared;

use App\Domain\Fleet\FleetId;
use App\Domain\Ship\ShipId;
use PHPUnit\Framework\TestCase;

final class IdentifierTest extends TestCase
{
    private const string UUID_A = '01a01aef-7d26-76ea-8446-2b36b272b0b7';
    private const string UUID_B = '01a01af0-0cb1-7318-a77a-07b9177e216d';

    public function testBothIdentifiersOfSameAggregateBuiltFromSameUuidAreTheSame(): void
    {
        $identifierA = ShipId::fromString(self::UUID_A);
        $identifierB = ShipId::fromString(self::UUID_A);

        self::assertTrue($identifierA->equals($identifierB));
    }

    public function testBothIdentifiersOfSameAggregateBuiltFromDifferentUuidAreDifferent(): void
    {
        $identifierA = ShipId::fromString(self::UUID_A);
        $identifierB = ShipId::fromString(self::UUID_B);

        self::assertFalse($identifierA->equals($identifierB));
    }

    public function testBothIdentifiersOfDifferentAggregateBuiltFromSameUuidAreDifferent(): void
    {
        $identifierA = ShipId::fromString(self::UUID_A);
        $identifierB = FleetId::fromString(self::UUID_A);

        self::assertFalse($identifierA->equals($identifierB));
    }

    public function testBothIdentifiersOfDifferentAggregateBuiltFromDifferentUuidAreDifferent(): void
    {
        $identifierA = ShipId::fromString(self::UUID_A);
        $identifierB = FleetId::fromString(self::UUID_B);

        self::assertFalse($identifierA->equals($identifierB));
    }
}
