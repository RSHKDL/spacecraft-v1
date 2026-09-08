<?php

declare(strict_types=1);

namespace App\Application\Ship\Command;

use App\Domain\Ship\ShipId;
use App\Domain\Ship\ShipName;

final class ChristenShip
{
    public function __construct(
        public ShipId $shipId,
        public ShipName $shipName,
    ) {}
}
