<?php

declare(strict_types=1);

namespace App\Application\Ship\Command;

use App\Domain\Ship\Ship;
use App\Domain\Ship\ShipRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class BuildShipHandler
{
    public function __construct(
        private ShipRepository $repository
    ) {}

    public function __invoke(BuildShip $command): void
    {
        $ship = Ship::build($command->shipId, $command->shipClass);
        $this->repository->save($ship);
    }
}
