<?php

declare(strict_types=1);

namespace App\Application\Fleet\Command;

use App\Domain\Fleet\FleetId;
use App\Domain\Fleet\FleetName;
use App\Domain\Ship\ShipId;

final class FormFleet
{
    public function __construct(
        public FleetId $fleetId,
        public FleetName $fleetName,
        /** @var ShipId[] */
        public array $shipIds,
        public ShipId $flagshipId,
    ) {}
}
