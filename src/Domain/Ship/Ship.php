<?php

declare(strict_types=1);

namespace App\Domain\Ship;

use App\Domain\ShipDesign\ShipDesign;
use App\Domain\ShipDesign\ShipDesignId;
use App\Domain\ShipDesign\ShipDesignRevision;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class Ship
{
    #[ORM\Embedded]
    private(set) ShipHull $hull;

    // Stored as a plain column: the value object is rebuilt on read and required
    // on write, so its invariants still hold at both boundaries -- without a
    // dedicated DBAL type. An embeddable is not an option here, since an absent
    // name has no representation in a schema made of the embeddable's fields.
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $name = null;

    private ShipDesignId $designId;
    private ShipDesignRevision $designRevision;
    private Loadout $loadout;

    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'ship_id', unique: true)]
        private(set) readonly ShipId $id,
        #[ORM\Column(type: 'string', enumType: ShipClass::class)]
        private(set) readonly ShipClass $class,
    ) {}

    /**
     * @deprecated Will be replaced by buildFrom()
     */
    public static function build(ShipId $id, ShipClass $class): self
    {
        $ship = new self($id, $class);
        $ship->hull = ShipHull::createNew($class->baseHull());

        return $ship;
    }

    public static function buildFrom(ShipId $id, ShipDesign $design): self
    {
        $class = $design->getClass();
        $ship = new self($id, $class);
        $ship->hull = ShipHull::createNew($class->baseHull());
        $ship->copyDesign($design);

        return $ship;
    }

    public function christen(ShipName $shipName): void
    {
        if (null !== $this->name) {
            throw new \DomainException('This ship has already been christened.');
        }

        $this->name = $shipName->getValue();
    }

    public function retrofit(ShipDesign $design): void
    {
        if ($design->getClass() !== $this->class) {
            throw new \DomainException('A ship cannot be retrofitted to a different class');
        }

        $this->copyDesign($design);
    }

    public function getName(): ?ShipName
    {
        return $this->name === null ? null : ShipName::create($this->name);
    }

    public function getClass(): ShipClass
    {
        return $this->class;
    }

    public function getDesignId(): ShipDesignId
    {
        return $this->designId;
    }

    public function getDesignRevision(): ShipDesignRevision
    {
        return $this->designRevision;
    }

    public function getLoadout(): Loadout
    {
        return $this->loadout;
    }

    private function copyDesign(ShipDesign $design): void
    {
        $this->designId = $design->getId();
        $this->designRevision = $design->getRevision();
        $this->loadout = Loadout::of($design->getComponents());
    }
}
