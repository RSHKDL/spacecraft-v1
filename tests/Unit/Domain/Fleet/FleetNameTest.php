<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Fleet;

use App\Domain\Fleet\FleetName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FleetNameTest extends TestCase
{
    public function testNormalizesSurroundingWhitespace(): void
    {
        $name = FleetName::create('     1st Fleet    ');

        self::assertSame('1st Fleet', $name->getValue());
    }

    #[DataProvider('nameProvider')]
    public function testRejectInvalidState(string $name): void
    {
        $this->expectException(\InvalidArgumentException::class);

        FleetName::create($name);
    }

    public static function nameProvider(): iterable
    {
        yield 'empty name' => [''];
        yield 'blank name' => ['           '];
    }
}
