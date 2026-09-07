<?php

declare(strict_types=1);

namespace App\Application\Fleet\Query;

use App\Domain\Fleet\FleetId;

final readonly class GetFleet
{
    public function __construct(
        public FleetId $fleetId,
    ) {}
}
