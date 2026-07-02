<?php

declare(strict_types=1);

namespace App\Domain\Ship;

use Symfony\Component\Uid\Uuid;

final readonly class ShipId
{
    private function __construct(
        private Uuid $value,
    ) {}

    public static function generate(): self
    {
        return new self(Uuid::v7());
    }
}
