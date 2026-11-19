<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Ship;

use App\Domain\Component\ComponentType;
use App\Domain\Ship\ShipClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ShipClassTest extends TestCase
{
    public function testShipClassesAreDeclaredFromSmallestToLargestWithPositiveBaseHull(): void
    {
        $previous = 0;
        foreach (ShipClass::cases() as $shipClass) {
            self::assertGreaterThan($previous, $shipClass->baseHull());
            $previous = $shipClass->baseHull();
        }
    }

    #[DataProvider('hullLayoutProvider')]
    public function testShipClassesHaveExpectedComponentSlots(
        ShipClass $class,
        int $expectedWeapons,
        int $expectedAuxiliaries,
    ): void
    {
        self::assertSame($expectedWeapons, $class->hullLayout()->getSlotsFor(ComponentType::Weapon));
        self::assertSame($expectedAuxiliaries, $class->hullLayout()->getSlotsFor(ComponentType::Auxiliary));
    }

    /**
     * @return iterable<string, array{ShipClass, int, int}>
     */
    public static function hullLayoutProvider(): iterable
    {
        yield 'Corvette' => [ShipClass::Corvette, 2, 1];
        yield 'Frigate' => [ShipClass::Frigate, 3, 2];
        yield 'Destroyer' => [ShipClass::Destroyer, 5, 2];
        yield 'Cruiser' => [ShipClass::Cruiser, 8, 3];
        yield 'Battleship' => [ShipClass::Battleship, 10, 4];
    }
}
