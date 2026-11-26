<?php

declare(strict_types=1);

namespace App\Application\Fleet\Query;

use App\Domain\Fleet\FleetId;

interface FleetFinder
{
    /** @return list<FleetListView> */
    public function findAll(): array;
    public function findById(FleetId $id): ?FleetView;
}
