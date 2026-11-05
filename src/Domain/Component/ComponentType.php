<?php

declare(strict_types=1);

namespace App\Domain\Component;

enum ComponentType: string
{
    case Reactor = 'reactor';
    case Weapon = 'weapon';
}
