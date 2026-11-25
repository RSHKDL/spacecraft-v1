<?php

declare(strict_types=1);

namespace App\Application\Ship\Command;

use App\Domain\Ship\ShipId;
use App\Domain\ShipDesign\ShipDesignId;

final class BuildShip
{
    public function __construct(
        public ShipId $shipId,
        public ShipDesignId $shipDesignId,
    ) {}
}
