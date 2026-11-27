<?php

declare(strict_types=1);

namespace App\Application\Fleet\Query;

use App\Application\Query\Query;
use App\Domain\Fleet\FleetId;

/**
 * @implements Query<?FleetView>
 */
final readonly class GetFleet implements Query
{
    public function __construct(
        public FleetId $fleetId,
    ) {}
}
