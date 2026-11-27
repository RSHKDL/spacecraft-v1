<?php

declare(strict_types=1);

namespace App\Application\Ship\Query;

use App\Application\Query\Query;
use App\Domain\Ship\ShipId;

/**
 * @implements Query<?ShipView>
 */
final readonly class GetShip implements Query
{
    public function __construct(
        public ShipId $shipId,
    ) {}
}
