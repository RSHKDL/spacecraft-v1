<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Json;

use App\Infrastructure\Json\JsonReader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class JsonReaderTest extends TestCase
{
    public function testAStringListIsReadInOrder(): void
    {
        self::assertSame(['frigate', 'cruiser'], JsonReader::readStringList(['for' => ['frigate', 'cruiser']], 'for'));
    }

    /**
     * @param array<mixed> $data
     */
    #[DataProvider('invalidStringListProvider')]
    public function testRejectsAnInvalidStringList(array $data): void
    {
        $this->expectException(\UnexpectedValueException::class);

        JsonReader::readStringList($data, 'for');
    }

    /**
     * @return iterable<string, array{array<mixed>}>
     */
    public static function invalidStringListProvider(): iterable
    {
        yield 'missing key' => [[]];
        yield 'not an array' => [['for' => 'frigate']];
        yield 'object instead of list' => [['for' => ['a' => 'frigate']]];
        yield 'non-string item' => [['for' => ['frigate', 3]]];
    }

    public function testAnAbsentOrNullOptionalStringReadsAsNull(): void
    {
        self::assertNull(JsonReader::readOptionalString([], 'name'));
        self::assertNull(JsonReader::readOptionalString(['name' => null], 'name'));
    }

    public function testAPresentOptionalStringMustBeAString(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        JsonReader::readOptionalString(['name' => 42], 'name');
    }

    public function testAnIntegerIsNotReadFromANumericString(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        JsonReader::readInt(['power' => '90'], 'power');
    }

    public function testAnObjectListRejectsAnObject(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        JsonReader::readObjectList(['a' => ['type' => 'reactor']]);
    }

    public function testAnObjectMapRejectsAList(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        JsonReader::readObjectMap([['type' => 'reactor']]);
    }
}
