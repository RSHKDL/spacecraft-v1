<?php

declare(strict_types=1);

namespace App\Application\Fleet\Query;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class GetFleetHandler
{
    public function __construct(
        private FleetFinder $finder
    ) {}

    public function __invoke(GetFleet $query): ?FleetView
    {
        return $this->finder->findById($query->fleetId);
    }
}
