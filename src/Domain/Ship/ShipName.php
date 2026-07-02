<?php

declare(strict_types=1);

namespace App\Domain\Ship;

final readonly class ShipName
{
    private string $value;

    private function __construct(
        string $value,
    ) {
        $value = trim($value);

        if ($value === '') {
            throw new \InvalidArgumentException('A ship name must not be empty.');
        }

        $this->value = $value;
    }

    public function value(): ?string
    {
        return $this->value;
    }

    public static function create(string $name): self
    {
        return new self($name);
    }
}
