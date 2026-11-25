<?php

declare(strict_types=1);

namespace App\Infrastructure\ShipDesign;

use App\Domain\ShipDesign\ShipDesign;
use App\Domain\ShipDesign\ShipDesignId;
use App\Domain\ShipDesign\ShipDesignRepository;
use App\Infrastructure\Ship\InMemoryShipRepository;

/**
 * @see InMemoryShipRepository for information.
 */
final class InMemoryShipDesignRepository implements ShipDesignRepository
{
    /** @var array<string, ShipDesign> */
    private array $shipDesigns = [];

    public function save(ShipDesign $shipDesign): void
    {
        $this->shipDesigns[$shipDesign->getId()->getValue()] = $shipDesign;
    }

    public function get(ShipDesignId $id): ShipDesign
    {
        $shipDesign = $this->shipDesigns[$id->getValue()] ?? null;
        if (!$shipDesign) {
            throw new \DomainException(sprintf('No ship design with id "%s"', $id->getValue()));
        }

        return $shipDesign;
    }

    public function getShipDesigns(): array
    {
        return array_values($this->shipDesigns);
    }
}
