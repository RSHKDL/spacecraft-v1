<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Component;

use App\Domain\Component\PowerConsumption;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PowerConsumptionTest extends TestCase
{
    #[DataProvider('validPowerConsumptionProvider')]
    public function testAPowerConsumptionOfZeroOrMoreIsValid(int $consumption): void
    {
        self::assertSame($consumption, PowerConsumption::create($consumption)->getValue());
    }

    public function testANegativePowerConsumptionIsNotValid(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        PowerConsumption::create(-1);
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function validPowerConsumptionProvider(): iterable
    {
        yield 'consumption equals zero' => [0];
        yield 'positive consumption' => [1];
    }
}
