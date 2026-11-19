<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ShipDesign;

use App\Domain\ShipDesign\ShipDesignName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ShipDesignNameTest extends TestCase
{
    public function testNormalizesSurroundingWhitespace(): void
    {
        $name = ShipDesignName::create('     Harbinger    ');

        self::assertSame('Harbinger', $name->getValue());
    }

    #[DataProvider('invalidNameProvider')]
    public function testRejectInvalidState(string $name, string $exceptionMessage): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIs($exceptionMessage);

        ShipDesignName::create($name);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNameProvider(): iterable
    {
        yield 'empty name' => ['', 'A ship design name must not be empty'];
        yield 'blank name' => ['           ', 'A ship design name must not be empty'];
        yield 'name too long' => [
            str_repeat('a', 31),
            'A ship design name must not exceed 30 characters',
        ];
    }

    #[DataProvider('validNameProvider')]
    public function testAcceptsNamesUpToTheLimit(string $name, string $expected): void
    {
        self::assertSame($expected, ShipDesignName::create($name)->getValue());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNameProvider(): iterable
    {
        yield '30 chars ASCII' => [str_repeat('a', 30), str_repeat('a', 30)];
        yield '30 chars ASCII with whitespace' => [' '.str_repeat('a', 30).' ', str_repeat('a', 30)];
        yield '30 chars multi-octets' => [str_repeat('é', 30), str_repeat('é', 30)];
    }
}
