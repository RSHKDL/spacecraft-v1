<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Ship;

use App\Domain\Ship\ShipClass;
use PHPUnit\Framework\TestCase;

final class ShipClassTest extends TestCase
{
    public function testEveryShipClassHasAPositiveBaseHull(): void
    {
        foreach (ShipClass::cases() as $shipClass) {
            self::assertGreaterThan(0, $shipClass->baseHull());
        }
    }
}
