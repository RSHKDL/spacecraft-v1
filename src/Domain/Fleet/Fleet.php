<?php

declare(strict_types=1);

namespace App\Domain\Fleet;

use App\Domain\Ship\ShipId;

final class Fleet
{
    private function __construct(
        private(set) readonly FleetId $id,
        private readonly FleetName $name,
        /** @var array<string, ShipId> */
        private array $shipIds = [],
        private ?ShipId $flagshipId = null,
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
        $newFleet = new self($fleetId, $fleetName);
        foreach ($shipIds as $shipId) {
            $newFleet->assign($shipId);
        }

        if ($newFleet->countShips() < 2) {
            throw new \DomainException('A fleet must have at least two ships');
        }

        $newFleet->promoteToFlagship($flagshipId);

        return $newFleet;
    }

    public function promoteToFlagship(ShipId $newFlagshipId): void
    {
        if (!isset($this->shipIds[$newFlagshipId->getValue()])) {
            throw new \DomainException('The flagship must be part of the fleet');
        }

        $this->flagshipId = $newFlagshipId;
    }

    public function assign(ShipId $shipId): void
    {
        if (isset($this->shipIds[$shipId->getValue()])) {
            throw new \DomainException('A fleet cannot assign the same ship twice');
        }

        $this->shipIds[$shipId->getValue()] = $shipId;
    }

    public function detach(ShipId $shipId): void
    {
        unset($this->shipIds[$shipId->getValue()]);

        if ($this->flagshipId?->equals($shipId)) {
            $this->flagshipId = null;
        }
    }

    public function getName(): FleetName
    {
        return $this->name;
    }

    public function getFlagshipId(): ?ShipId
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
