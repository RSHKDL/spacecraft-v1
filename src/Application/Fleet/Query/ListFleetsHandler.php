<?php

declare(strict_types=1);

namespace App\Application\Fleet\Query;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class ListFleetsHandler
{
    public function __construct(
        private FleetFinder $finder
    ) {}

    public function __invoke(ListFleets $query): array
    {
        return $this->finder->findAll();
    }
}
