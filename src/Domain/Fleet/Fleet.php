<?php

declare(strict_types=1);

namespace App\Domain\Fleet;

use App\Domain\Ship\ShipId;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class Fleet
{
    private const int MINIMUM_SHIPS_TO_FORM_A_FLEET = 2;

    /** @var Collection<int, FleetAssignment> $fleetAssignments */
    #[ORM\OneToMany(
        targetEntity: FleetAssignment::class,
        mappedBy: 'fleet',
        cascade: ['persist'],
        orphanRemoval: true,
    )]
    private Collection $fleetAssignments;

    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'fleet_id', unique: true)]
        private(set) readonly FleetId $id,
        #[ORM\Embedded]
        private readonly FleetName $name,
        #[ORM\Column(type: 'ship_id', nullable: true)]
        private ?ShipId $flagshipId = null,
    ) {
        $this->fleetAssignments = new ArrayCollection();
    }

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

        if ($newFleet->countShips() < self::MINIMUM_SHIPS_TO_FORM_A_FLEET) {
            throw new \DomainException(
                sprintf(
                    'A fleet must have at least %d ships',
                    self::MINIMUM_SHIPS_TO_FORM_A_FLEET,
                )
            );
        }

        $newFleet->promoteToFlagship($flagshipId);

        return $newFleet;
    }

    public function promoteToFlagship(ShipId $newFlagshipId): void
    {
        if (!$this->findFleetAssignment($newFlagshipId)) {
            throw new \DomainException('The flagship must be part of the fleet');
        }

        $this->flagshipId = $newFlagshipId;
    }

    public function assign(ShipId $shipId): void
    {
        if ($this->findFleetAssignment($shipId)) {
            throw new \DomainException('A fleet cannot assign the same ship twice');
        }

        $this->fleetAssignments->add(new FleetAssignment($shipId, $this));
    }

    public function detach(ShipId $shipId): void
    {
        $fleetAssignment = $this->findFleetAssignment($shipId);
        $this->fleetAssignments->removeElement($fleetAssignment);

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
        return $this->fleetAssignments
            ->map(static fn (FleetAssignment $fleetAssignment): ShipId => $fleetAssignment->getShipId())
            ->getValues()
        ;
    }

    public function countShips(): int
    {
        return $this->fleetAssignments->count();
    }

    private function findFleetAssignment(ShipId $shipId): ?FleetAssignment
    {
        return $this->fleetAssignments->findFirst(
            static fn (int|string $key, FleetAssignment $fleetAssignment): bool
            => $fleetAssignment->getShipId()->equals($shipId),
        );
    }
}
