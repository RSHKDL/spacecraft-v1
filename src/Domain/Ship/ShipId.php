<?php

declare(strict_types=1);

namespace App\Domain\Ship;

use Symfony\Component\Uid\Uuid;

final readonly class ShipId
{
    private function __construct(
        private Uuid $value,
    ) {}

    /**
     * Required for Doctrine entity map.
     */
    public function __toString(): string
    {
        return (string) $this->value;
    }

    public static function generate(): self
    {
        return new self(Uuid::v7());
    }

    public static function fromString(string $value): self
    {
        return new self(Uuid::fromString($value));
    }

    public function getValue(): string
    {
        return $this->value->toRfc4122();
    }
}
