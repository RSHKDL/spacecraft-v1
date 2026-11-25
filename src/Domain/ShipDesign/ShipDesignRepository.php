<?php

declare(strict_types=1);

namespace App\Domain\ShipDesign;

interface ShipDesignRepository
{
    public function save(ShipDesign $shipDesign): void;
    public function get(ShipDesignId $id): ShipDesign;
}
