<?php

declare(strict_types=1);

namespace App\Domain\ShipDesign;

final readonly class PowerBalance
{
    private function __construct(
        private int $value,
    ) {}

    /**
     * @param list<Component> $components
     */
    public static function of(array $components): self
    {
        $powerBalance = 0;
        foreach ($components as $component) {
            if ($component->getType()->suppliesPower()) {
                $powerBalance += $component->getPower()->getValue();
            } else {
                $powerBalance -= $component->getPower()->getValue();
            }
        }

        return new self($powerBalance);
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
