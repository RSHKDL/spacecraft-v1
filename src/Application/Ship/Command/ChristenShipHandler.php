<?php

declare(strict_types=1);

namespace App\Application\Ship\Command;

use App\Domain\Ship\ShipRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class ChristenShipHandler
{
    public function __construct(
        private ShipRepository $repository
    ) {}

    public function __invoke(ChristenShip $command): void
    {
        $ship = $this->repository->get($command->shipId);
        $ship->christen($command->shipName);
        $this->repository->save($ship);
    }
}
