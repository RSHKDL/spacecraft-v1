<?php

declare(strict_types=1);

namespace App\Application\Fleet\Query;

use App\Domain\Fleet\FleetId;
use App\Domain\Ship\ShipId;

final readonly class FleetView
{
    public function __construct(
        public FleetId $id,
        public ?string $name,
        public ?ShipId $flagshipId,
        /** @var FleetShipView[] $ships  */
        public array $ships,
    ) {}
}
