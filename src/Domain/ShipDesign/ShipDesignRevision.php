<?php

declare(strict_types=1);

namespace App\Domain\ShipDesign;

final readonly class ShipDesignRevision
{
    private function __construct(
        private int $value
    ) {}

    public static function first(): self
    {
        return new self(1);
    }

    public function next(): self
    {
        return new self($this->getValue() + 1);
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
