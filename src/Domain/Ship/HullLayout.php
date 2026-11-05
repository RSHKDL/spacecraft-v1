<?php

declare(strict_types=1);

namespace App\Domain\Ship;

final readonly class HullLayout
{
    public function __construct(
        private int $auxiliarySlots
    ) {}

    public function getAuxiliarySlots(): int
    {
        return $this->auxiliarySlots;
    }
}
