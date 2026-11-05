<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Ship;

use App\Domain\Ship\ShipClass;
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
}
