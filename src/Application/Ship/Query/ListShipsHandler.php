<?php

declare(strict_types=1);

namespace App\Application\Ship\Query;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class ListShipsHandler
{
    public function __construct(
        private ShipFinder $finder
    ) {}

    public function __invoke(ListShips $query): array
    {
        return $this->finder->findAll();
    }
}
