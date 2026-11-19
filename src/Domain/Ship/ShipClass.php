<?php

declare(strict_types=1);

namespace App\Domain\Ship;

enum ShipClass: string
{
    case Corvette = 'corvette';
    case Frigate = 'frigate';
    case Destroyer = 'destroyer';
    case Cruiser = 'cruiser';
    case Battleship = 'battleship';

    public function baseHull(): int
    {
        return match ($this) {
            self::Corvette => 100,
            self::Frigate => 300,
            self::Destroyer => 400,
            self::Cruiser => 600,
            self::Battleship => 1200,
        };
    }

    public function hullLayout(): HullLayout
    {
        return match ($this) {
            self::Corvette => new HullLayout(weaponSlots: 2, auxiliarySlots: 1),
            self::Frigate => new HullLayout(weaponSlots: 3, auxiliarySlots: 2),
            self::Destroyer => new HullLayout(weaponSlots: 5, auxiliarySlots: 2),
            self::Cruiser => new HullLayout(weaponSlots: 8, auxiliarySlots: 3),
            self::Battleship => new HullLayout(weaponSlots: 10, auxiliarySlots: 4),
        };
    }
}
