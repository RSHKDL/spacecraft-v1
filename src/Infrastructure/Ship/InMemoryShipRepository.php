<?php

declare(strict_types=1);

namespace App\Infrastructure\Ship;

use App\Domain\Ship\Ship;
use App\Domain\Ship\ShipId;
use App\Domain\Ship\ShipRepository;

/**
 * Volatile adapter for demonstration purpose: state does not survive the request.
 * Can be used in unit test.
 */
final class InMemoryShipRepository implements ShipRepository
{
    /** @var array<string, Ship> */
    private array $ships = [];

    public function save(Ship $ship): void
    {
        $this->ships[$ship->id->getValue()] = $ship;
    }

    public function get(ShipId $id): Ship
    {
        $ship = $this->ships[$id->getValue()] ?? null;
        if (!$ship) {
            throw new \DomainException(sprintf('No ship with id "%s"', $id->getValue()));
        }

        return $ship;
    }

    public function getShips(): array
    {
        return array_values($this->ships);
    }
}
