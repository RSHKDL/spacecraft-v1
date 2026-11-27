<?php

declare(strict_types=1);

namespace App\Application\Ship\Query;

use App\Application\Query\Query;

/**
 * @implements Query<list<ShipView>>
 */
final readonly class ListAvailableShips implements Query {}
