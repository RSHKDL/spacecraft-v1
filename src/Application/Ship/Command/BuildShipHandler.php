<?php

declare(strict_types=1);

namespace App\Application\Ship\Command;

use App\Domain\Ship\Ship;
use App\Domain\Ship\ShipRepository;
use App\Domain\ShipDesign\ShipDesignRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class BuildShipHandler
{
    public function __construct(
        private ShipRepository $repository,
        private ShipDesignRepository $shipDesignRepository,
    ) {}

    public function __invoke(BuildShip $command): void
    {
        $design = $this->shipDesignRepository->get($command->shipDesignId);
        $ship = Ship::build($command->shipId, $design);
        $this->repository->save($ship);
    }
}
