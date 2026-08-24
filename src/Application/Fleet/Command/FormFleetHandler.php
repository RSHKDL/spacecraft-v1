<?php

declare(strict_types=1);

namespace App\Application\Fleet\Command;

use App\Domain\Fleet\Fleet;
use App\Domain\Fleet\FleetRepository;
use App\Domain\Fleet\ShipAvailability;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class FormFleetHandler
{
    public function __construct(
        private FleetRepository $repository,
        private ShipAvailability $shipAvailability,
    ) {}

    public function __invoke(FormFleet $command): void
    {
        $this->shipAvailability->ensureUnassigned($command->shipIds);
        $fleet = Fleet::form($command->fleetId, $command->fleetName, $command->shipIds, $command->flagshipId);
        $this->repository->save($fleet);
    }
}
