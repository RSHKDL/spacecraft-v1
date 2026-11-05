<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Ship;

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
    public function testShipClassesHaveExpectedAuxiliarySlots(ShipClass $class, int $expected): void
    {
        self::assertSame($expected, $class->hullLayout()->getAuxiliarySlots());
    }

    /**
     * @return iterable<string, array{ShipClass, int}>
     */
    public static function hullLayoutProvider(): iterable
    {
        yield 'Corvette' => [ShipClass::Corvette, 1];
        yield 'Frigate' => [ShipClass::Frigate, 2];
        yield 'Destroyer' => [ShipClass::Destroyer, 2];
        yield 'Cruiser' => [ShipClass::Cruiser, 3];
        yield 'Battleship' => [ShipClass::Battleship, 4];
    }
}
