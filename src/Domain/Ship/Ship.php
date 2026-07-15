<?php

declare(strict_types=1);

namespace App\Domain\Ship;

final class Ship
{
    private(set) ShipHull $hull;
    private(set) ?ShipName $name = null;

    private function __construct(
        private ShipId $id,
        private ShipClass $class,
    ) {}

    public static function build(ShipId $id, ShipClass $class): self
    {
        $ship = new self($id, $class);
        $ship->hull = ShipHull::createNew($class->baseHull());

        return $ship;
    }

    public function christen(ShipName $shipName): void
    {
        if (null !== $this->name) {
            throw new \DomainException('This ship has already been christened.');
        }

        $this->name = $shipName;
    }

}
