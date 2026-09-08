<?php

declare(strict_types=1);

namespace App\Domain\Ship;

enum ShipClass: string
{
    case Corvette = 'corvette';
    case Destroyer = 'destroyer';
    case Frigate = 'frigate';
    case Cruiser = 'cruiser';
    case Battleship = 'battleship';

    public function baseHull(): int
    {
        return match ($this) {
            self::Corvette => 100,
            self::Destroyer => 300,
            self::Frigate => 400,
            self::Cruiser => 600,
            self::Battleship => 1200,
        };
    }
}
