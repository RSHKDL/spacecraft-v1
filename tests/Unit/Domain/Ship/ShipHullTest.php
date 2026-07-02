<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Ship;

use App\Domain\Ship\ShipHull;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ShipHullTest extends TestCase
{
    public function testANewHullStartsAtItsMax(): void
    {
        $hull = ShipHull::createNew(100);

        self::assertSame(100, $hull->current());
        self::assertSame(100, $hull->max());
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
}
