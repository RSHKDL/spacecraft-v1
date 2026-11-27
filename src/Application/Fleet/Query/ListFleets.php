<?php

declare(strict_types=1);

namespace App\Application\Fleet\Query;

use App\Application\Query\Query;

/**
 * @implements Query<list<FleetListView>>
 */
final readonly class ListFleets implements Query {}
