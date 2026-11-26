<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Ship;

use App\Domain\Ship\ShipName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ShipNameTest extends TestCase
{
    public function testNormalizesSurroundingWhitespace(): void
    {
        $name = ShipName::create('     Nostromo    ');

        self::assertSame('Nostromo', $name->getValue());
    }

    #[DataProvider('nameProvider')]
    public function testRejectInvalidState(string $name): void
    {
        $this->expectException(\InvalidArgumentException::class);

        ShipName::create($name);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function nameProvider(): iterable
    {
        yield 'empty name' => [''];
        yield 'blank name' => ['           '];
    }
}
