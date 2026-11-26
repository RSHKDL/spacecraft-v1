<?php

declare(strict_types=1);

namespace App\Application\ShipDesign\Query;

interface ShipDesignFinder
{
    /** @return list<ShipDesignView> */
    public function findAll(): array;
}
