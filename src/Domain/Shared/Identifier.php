<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use Symfony\Component\Uid\Uuid;

abstract readonly class Identifier
{
    private function __construct(
        protected Uuid $value,
    ) {}

    /**
     * Required for Doctrine entity map.
     */
    public function __toString(): string
    {
        return (string) $this->value;
    }

    public static function generate(): static
    {
        return new static(Uuid::v7());
    }

    public static function fromString(string $value): static
    {
        return new static(Uuid::fromString($value));
    }

    public function getValue(): string
    {
        return $this->value->toRfc4122();
    }

    /**
     * Compare the child class AND the value (format Rfc4122)
     */
    public function equals(self $identifier): bool
    {
        return
            static::class === $identifier::class
            && $this->getValue() === $identifier->getValue()
        ;
    }
}
