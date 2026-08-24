<?php

declare(strict_types=1);

namespace App\Domain\Fleet;

use App\Domain\Ship\ShipId;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class FleetAssignment
{
    #[ORM\Id]
    #[ORM\Column(type: 'ship_id')]
    private ShipId $shipId;

    #[ORM\ManyToOne(targetEntity: Fleet::class, inversedBy: 'fleetAssignments')]
    #[ORM\JoinColumn(nullable: false)]
    private readonly Fleet $fleet;

    // private DateTimeImmutable $shipAssignedAt; todo later, just to show why this class can be useful

    public function __construct(
        ShipId $shipId,
        Fleet $fleet,
    ) {
        $this->shipId = $shipId;
        $this->fleet = $fleet;
    }

    public function getShipId(): ShipId
    {
        return $this->shipId;
    }
}
