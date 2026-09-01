<?php

declare(strict_types=1);

namespace App\Application\Fleet\Command;

use App\Domain\Fleet\FleetRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class DetachShipHandler
{
    public function __construct(
        private FleetRepository $repository,
    ) {}

    public function __invoke(DetachShip $command): void
    {
        $fleet = $this->repository->get($command->fleetId);
        $fleet->detach($command->shipId);

        // This save is not useful here since it's the "orphanRemoval" that do the real work.
        // However, it's nice to keep it for readability since this handler can be read without
        // guessing Doctrine, and we understand what it does (charge, do, save).
        $this->repository->save($fleet);
    }
}
