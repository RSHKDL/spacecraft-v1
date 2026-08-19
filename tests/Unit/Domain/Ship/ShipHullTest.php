<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Ship;

use App\Domain\Ship\HullStatus;
use App\Domain\Ship\ShipHull;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ShipHullTest extends TestCase
{
    public function testANewHullStartsAtItsMax(): void
    {
        $hull = ShipHull::createNew(100);

        self::assertSame(100, $hull->getCurrent());
        self::assertSame(100, $hull->getMax());
    }

    #[DataProvider('hullProvider')]
    public function testRejectInvalidState(int $current, int $max): void
    {
        $this->expectException(\InvalidArgumentException::class);

        ShipHull::create($current, $max);
    }

    public static function hullProvider(): iterable
    {
        yield 'current > max' => [150, 100];
        yield 'current < 0' => [-100, 100];
        yield 'max <= 0' => [100, 0];
    }

    #[DataProvider('hullStatusProvider')]
    public function testHullStatusIsCoherent(int $current, int $max, HullStatus $expected): void
    {
        $hull = ShipHull::create($current, $max);

        self::assertSame($expected, $hull->getStatus());
    }

    public static function hullStatusProvider(): iterable
    {
        yield 'hull is full' => [100, 100, HullStatus::Intact];
        yield 'hull is exactly at the threshold' => [10, 100, HullStatus::Damaged];
        yield 'hull is just below the threshold' => [9, 100, HullStatus::Destroyed];
        yield 'no hull left' => [0, 100, HullStatus::Destroyed];
    }
}
