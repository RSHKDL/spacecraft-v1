<?php

declare(strict_types=1);

namespace App\Application\Ship\Query;

use App\Domain\Ship\ShipId;

final readonly class GetShip
{
    public function __construct(
        public ShipId $shipId,
    ) {}
}
