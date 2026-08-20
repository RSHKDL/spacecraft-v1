<?php

declare(strict_types=1);

namespace App\Domain\Ship;

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
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'ship_id', unique: true)]
        private(set) readonly ShipId $id,
        #[ORM\Column(type: 'string', enumType: ShipClass::class)]
        private(set) readonly ShipClass $class,
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

        $this->name = $shipName->getValue();
    }

    public function getName(): ?ShipName
    {
        return $this->name === null ? null : ShipName::create($this->name);
    }
}
