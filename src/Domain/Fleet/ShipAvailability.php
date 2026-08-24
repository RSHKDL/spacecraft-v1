<?php

declare(strict_types=1);

namespace App\Domain\Fleet;

use App\Domain\Ship\ShipId;

final readonly class ShipAvailability
{
    public function __construct(
        private FleetRepository $repository,
    ) {}

    public function ensureUnassigned(array $shipIds): void
    {
        $assignedShipIds = $this->repository->findAlreadyAssignedShipIds($shipIds);

        if ([] !== $assignedShipIds) {
            throw new \DomainException(sprintf(
                'These ships already serve in another fleet: %s',
                implode(', ', array_map(static fn (ShipId $shipId): string => $shipId->getValue(), $assignedShipIds)),
            ));
        }
    }
}
