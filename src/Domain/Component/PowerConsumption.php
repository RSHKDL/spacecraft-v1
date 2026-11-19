<?php

declare(strict_types=1);

namespace App\Domain\Component;

final readonly class PowerConsumption implements Power
{
    private function __construct(
        private int $value,
    ) {
        if ($value < 0) {
            throw new \InvalidArgumentException('Power consumption value cannot be negative');
        }
    }

    public static function create(int $consumption): self
    {
        return new self($consumption);
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
