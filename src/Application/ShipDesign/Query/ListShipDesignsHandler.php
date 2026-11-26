<?php

declare(strict_types=1);

namespace App\Application\ShipDesign\Query;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class ListShipDesignsHandler
{
    public function __construct(
        private ShipDesignFinder $finder
    ) {}

    /**
     * @return list<ShipDesignView>
     */
    public function __invoke(ListShipDesigns $query): array
    {
        return $this->finder->findAll();
    }
}
