<?php

declare(strict_types=1);

namespace App\Application\Fleet\Command;

use App\Domain\Fleet\FleetId;
use App\Domain\Ship\ShipId;

final class AssignShip
{
    public function __construct(
        public FleetId $fleetId,
        public ShipId $shipId,
    ) {}
}
