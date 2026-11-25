<?php

declare(strict_types=1);

namespace App\Domain\ShipDesign;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class ShipDesignRevision
{
    private function __construct(
        #[ORM\Column(name: 'revision', type: Types::INTEGER)]
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
