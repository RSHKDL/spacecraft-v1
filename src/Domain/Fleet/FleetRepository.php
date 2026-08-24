<?php

declare(strict_types=1);

namespace App\Domain\Fleet;

use App\Domain\Ship\ShipId;

interface FleetRepository
{
    public function save(Fleet $fleet): void;

    /**
     * @param ShipId[] $shipIds
     * @return ShipId[] Ids of ships already serving in a fleet
     */
    public function findAlreadyAssignedShipIds(array $shipIds): array;
}
