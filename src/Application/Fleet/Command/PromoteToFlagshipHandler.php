<?php

declare(strict_types=1);

namespace App\Application\Fleet\Command;

use App\Domain\Fleet\FleetRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class PromoteToFlagshipHandler
{
    public function __construct(
        private FleetRepository $repository,
    ) {}

    public function __invoke(PromoteToFlagship $command): void
    {
        $fleet = $this->repository->get($command->fleetId);
        $fleet->promoteToFlagship($command->newFlagshipId);

        $this->repository->save($fleet);
    }
}
