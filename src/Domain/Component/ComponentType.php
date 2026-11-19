<?php

declare(strict_types=1);

namespace App\Domain\Component;

enum ComponentType: string
{
    case Auxiliary = 'auxiliary';
    case Reactor = 'reactor';
    case Shield = 'shield';
    case Weapon = 'weapon';

    public function allowsMultiple(): bool
    {
        return match ($this) {
            self::Auxiliary,
            self::Weapon => true,
            self::Reactor,
            self::Shield => false,
        };
    }

    public function suppliesPower(): bool
    {
        return match ($this) {
            self::Reactor => true,
            default => false,
        };
    }
}
