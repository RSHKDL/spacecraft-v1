<?php

declare(strict_types=1);

namespace App\Domain\Ship;

use App\Domain\Component\ComponentType;

final readonly class HullLayout
{
    public function __construct(
        private int $weaponSlots,
        private int $auxiliarySlots,
    ) {}

    public function getSlotsFor(ComponentType $type): int
    {
        if (!$type->allowsMultiple()) {
            return 1;
        }

        return match ($type) {
            ComponentType::Auxiliary => $this->auxiliarySlots,
            ComponentType::Weapon => $this->weaponSlots,
            default => throw new \LogicException(sprintf('No slot count defined for %s', $type->value)),
        };
    }
}
