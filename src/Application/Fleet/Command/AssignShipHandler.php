<?php

declare(strict_types=1);

namespace App\Application\Fleet\Command;

use App\Domain\Fleet\FleetRepository;
use App\Domain\Fleet\ShipAvailability;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class AssignShipHandler
{
    public function __construct(
        private FleetRepository $repository,
        private ShipAvailability $shipAvailability,
    ) {}

    public function __invoke(AssignShip $command): void
    {
        $fleet = $this->repository->get($command->fleetId);
        $this->shipAvailability->ensureUnassigned([$command->shipId], $fleet);
        $fleet->assign($command->shipId);

        $this->repository->save($fleet);
    }
}
