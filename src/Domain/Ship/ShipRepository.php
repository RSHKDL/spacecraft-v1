<?php

declare(strict_types=1);

namespace App\Domain\Ship;

interface ShipRepository
{
    public function save(Ship $ship): void;
}
