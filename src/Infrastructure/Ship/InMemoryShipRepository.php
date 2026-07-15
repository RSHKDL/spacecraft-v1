<?php

declare(strict_types=1);

namespace App\Infrastructure\Ship;

use App\Domain\Ship\Ship;
use App\Domain\Ship\ShipRepository;

/**
 * Volatile adapter for demonstration purpose: state does not survive the request.
 * Can be used in unit test.
 */
class InMemoryShipRepository implements ShipRepository
{
    /** @var list<Ship> */
    private array $ships = [];

    public function save(Ship $ship): void
    {
        $this->ships[] = $ship;
    }

    public function getShips(): array
    {
        return $this->ships;
    }
}
