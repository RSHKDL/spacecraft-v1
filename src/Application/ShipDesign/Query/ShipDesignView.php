<?php

declare(strict_types=1);

namespace App\Application\ShipDesign\Query;

use App\Domain\Ship\ShipClass;
use App\Domain\ShipDesign\ShipDesignId;

final readonly class ShipDesignView
{
    public function __construct(
        public ShipDesignId $id,
        public string $name,
        public ShipClass $class,
        public int $revision,
    ) {}
}
