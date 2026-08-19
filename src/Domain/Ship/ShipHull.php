<?php

declare(strict_types=1);

namespace App\Domain\Ship;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class ShipHull
{
    private function __construct(
        #[ORM\Column]
        private int $current,
        #[ORM\Column]
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

    public function getCurrent(): int
    {
        return $this->current;
    }

    public function getMax(): int
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

    /**
     * A Ship is considered "destroyed" when its hull value is lower than
     * 10% of the max value.
     */
    public function getStatus(): HullStatus
    {
        return match (true) {
            $this->current === $this->max => HullStatus::Intact,
            $this->current * 10 < $this->max => HullStatus::Destroyed,
            default => HullStatus::Damaged,
        };
    }
}
