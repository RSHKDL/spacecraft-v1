<?php

declare(strict_types=1);

namespace App\Domain\Component;

use App\Domain\Ship\ShipClass;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class ComponentModel
{
    #[ORM\Column]
    private bool $discontinued = false;

    // Stored as a plain column: the VO is rebuilt on read from the type.
    #[ORM\Column]
    private int $power;

    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'component_model_id', unique: true)]
        private readonly ComponentModelId $id,
        #[ORM\Column(type: Types::STRING, enumType: ComponentType::class)]
        private readonly ComponentType $type,
        #[ORM\Column(type: Types::JSONB, enumType: ShipClass::class)]
        private readonly array $shipClasses,
        Power $power,
    ) {
        $this->power = $power->getValue();
    }

    /**
     * @param list<ShipClass> $shipClasses
     */
    public static function release(
        ComponentModelId $id,
        ComponentType $type,
        array $shipClasses,
        Power $power,
    ): self
    {
        if ([] === $shipClasses) {
            throw new \DomainException('A component model must support at least one ship class');
        }

        $powerMatchesType = $type->suppliesPower()
            ? $power instanceof PowerOutput
            : $power instanceof PowerConsumption;

        if (!$powerMatchesType) {
            throw new \DomainException('A component model power must match its type');
        }

        return new self($id, $type, $shipClasses, $power);
    }

    public function discontinue(): void
    {
        $this->discontinued = true;
    }

    public function isDiscontinued(): bool
    {
        return $this->discontinued;
    }

    public function getId(): ComponentModelId
    {
        return $this->id;
    }

    public function getType(): ComponentType
    {
        return $this->type;
    }

    /**
     * @return list<ShipClass>
     */
    public function getSupportedShipClasses(): array
    {
        return $this->shipClasses;
    }

    public function getPower(): Power
    {
        return $this->type->powerOf($this->power);
    }
}
