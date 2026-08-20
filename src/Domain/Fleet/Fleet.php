<?php

declare(strict_types=1);

namespace App\Domain\Fleet;

use App\Domain\Ship\ShipId;


final class Fleet
{
    private function __construct(
        private(set) readonly FleetId $id,
        private FleetName $name,
        private array $shipIds,
        private ShipId $flagshipId,
    ) {}

    /**
     * @param ShipId[] $shipIds
     */
    public static function form(
        FleetId $fleetId,
        FleetName $fleetName,
        array $shipIds,
        ShipId $flagshipId,
    ): self
    {
        $shipIdsByValue = [];
        foreach ($shipIds as $shipId) {
            $shipIdsByValue[$shipId->getValue()] = $shipId;
        }

        // This is checked first before because two identical ids collapse into
        // a single entry, so the next guard would answer "at least two ships" to
        // a caller that did pass two identical ids. We report the most specific
        // cause first. Both guards have the same cost 0(1).
        if (count($shipIdsByValue) !== count($shipIds)) {
            throw new \DomainException('A fleet cannot enlist the same ship twice');
        }

        if (count($shipIdsByValue) < 2) {
            throw new \DomainException('A fleet must have at least two ships');
        }

        if (!isset($shipIdsByValue[$flagshipId->getValue()])) {
            throw new \DomainException('The flagship must be part of the fleet');
        }

        return new self($fleetId, $fleetName, $shipIdsByValue, $flagshipId);
    }

    public function getName(): FleetName
    {
        return $this->name;
    }

    public function getFlagshipId(): ShipId
    {
        return $this->flagshipId;
    }

    /**
     * @return ShipId[]
     */
    public function getShipIds(): array
    {
        return array_values($this->shipIds);
    }

    public function countShips(): int
    {
        return count($this->shipIds);
    }
}
