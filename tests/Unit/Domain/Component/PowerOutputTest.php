<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Component;

use App\Domain\Component\PowerOutput;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PowerOutputTest extends TestCase
{
    public function testTheSmallestValidPowerOutputIsOne(): void
    {
        self::assertSame(1, PowerOutput::create(1)->getValue());
    }

    #[DataProvider('invalidOutputProvider')]
    public function testRejectInvalidState(int $output): void
    {
        $this->expectException(\InvalidArgumentException::class);

        PowerOutput::create($output);
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function invalidOutputProvider(): iterable
    {
        yield 'output equals zero' => [0];
        yield 'negative output' => [-1];
    }
}
