<?php

declare(strict_types=1);

namespace App\Application\Ship\Query;

interface ShipFinder
{
    /** @return ShipView[] */
    public function findAll(): array;
}
