<?php

declare(strict_types=1);

namespace App\Application\Fleet\Query;

use App\Domain\Fleet\FleetId;

interface FleetFinder
{
    public function findById(FleetId $id): ?FleetView;
}
