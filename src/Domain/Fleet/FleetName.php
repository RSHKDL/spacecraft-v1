<?php

declare(strict_types=1);

namespace App\Domain\Fleet;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;

#[Embeddable]
final readonly class FleetName
{
    #[Column]
    private string $value;

    private function __construct(
        string $value,
    ) {
        $value = trim($value);

        if ($value === '') {
            throw new \InvalidArgumentException('A fleet name must not be empty.');
        }

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public static function create(string $name): self
    {
        return new self($name);
    }
}
