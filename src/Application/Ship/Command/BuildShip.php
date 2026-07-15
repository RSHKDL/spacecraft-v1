<?php

declare(strict_types=1);

namespace App\Application\Ship\Command;

use App\Domain\Ship\ShipClass;
use App\Domain\Ship\ShipId;

final class BuildShip
{
    public function __construct(
        public ShipId $shipId,
        public ShipClass $shipClass,
    ) {}
}
