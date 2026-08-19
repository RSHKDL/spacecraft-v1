<?php

declare(strict_types=1);

namespace App\Application\Ship\Query;

use App\Domain\Ship\HullStatus;
use App\Domain\Ship\ShipClass;
use App\Domain\Ship\ShipHull;
use App\Domain\Ship\ShipId;

final readonly class ShipView
{
    public HullStatus $hullStatus;

    public function __construct(
        public ShipId $id,
        public ?string $name,
        public ShipClass $class,
        int $hullCurrent,
        int $hullMax,
    ) {
        $this->hullStatus =  ShipHull::create($hullCurrent, $hullMax)->getStatus();
    }
}
