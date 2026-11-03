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

    #[DataProvider('invalidNameProvider')]
    public function testRejectInvalidState(string $name): void
    {
        $this->expectException(\InvalidArgumentException::class);

        FleetName::create($name);
    }

    public static function invalidNameProvider(): iterable
    {
        yield 'empty name' => [''];
        yield 'blank name' => ['           '];
        yield 'name too long' => [str_repeat('a', 71)];
    }

    #[DataProvider('validNameProvider')]
    public function testAcceptsNamesUpToTheLimit(string $name, string $expected): void
    {
        self::assertSame($expected, FleetName::create($name)->getValue());
    }

    public static function validNameProvider(): iterable
    {
        yield '70 chars ASCII' => [str_repeat('a', 70), str_repeat('a', 70)];
        yield '70 chars ASCII with whitespace' => [' '.str_repeat('a', 70).' ', str_repeat('a', 70)];
        yield '70 chars multi-octets' => [str_repeat('é', 70), str_repeat('é', 70)];
    }
}
