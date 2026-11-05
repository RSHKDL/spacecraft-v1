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
}
