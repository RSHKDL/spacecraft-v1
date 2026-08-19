<?php

declare(strict_types=1);

namespace App\Domain\Ship;

enum HullStatus: string
{
    case Intact = 'intact';
    case Damaged = 'damaged';
    case Destroyed = 'destroyed';
}
