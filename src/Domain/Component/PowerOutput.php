<?php

declare(strict_types=1);

namespace App\Domain\Component;

final readonly class PowerOutput
{
    private function __construct(
        private int $value,
    ) {
        if ($value < 1) {
            throw new \InvalidArgumentException('Power output value must be greater than zero');
        }
    }

    public static function create(int $output): self
    {
        return new self($output);
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
