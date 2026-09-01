<?php

declare(strict_types=1);

namespace App\Application\Fleet\Command;

use App\Domain\Fleet\FleetId;

final class DisbandFleet
{
    public function __construct(public FleetId $fleetId) {}
}
