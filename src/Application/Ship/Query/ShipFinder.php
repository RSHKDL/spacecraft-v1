<?php

declare(strict_types=1);

namespace App\Application\Ship\Query;

use App\Domain\Ship\ShipId;

interface ShipFinder
{
    /** @return ShipView[] */
    public function findAll(): array;
    /** @return ShipView[] */
    public function findAvailable(): array;
    public function findById(ShipId $id): ?ShipView;
}
