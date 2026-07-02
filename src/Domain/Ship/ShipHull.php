<?php

declare(strict_types=1);

namespace App\Domain\Ship;

final readonly class ShipHull
{
    private function __construct(
        private int $current,
        private int $max,
    ) {
        if ($max <= 0) {
            throw new \InvalidArgumentException(
                sprintf('Hull max value (%d) should be a positive integer.', $max),
            );
        }

        if ($current > $max) {
            throw new \InvalidArgumentException(
                sprintf('Hull current value (%d) cannot exceed max value (%d).', $current, $max),
            );
        }

        if ($current < 0) {
            throw new \InvalidArgumentException(
                sprintf('Hull current value (%d) cannot be less than 0.', $current),
            );
        }
    }

    public function current(): int
    {
        return $this->current;
    }

    public function max(): int
    {
        return $this->max;
    }

    public static function create(int $current, int $max): self
    {
        return new self($current, $max);
    }

    public static function createNew(int $max): self
    {
        return new self($max, $max);
    }
}
