<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Ship\Dto;

use App\Application\Ship\Command\BuildShip as BuildShipCommand;
use App\Domain\Ship\ShipId;
use App\Domain\ShipDesign\ShipDesignId;
use Symfony\Component\Validator\Constraints as Assert;

final class BuildShip
{
    #[Assert\NotBlank]
    public ?string $designId = null;

    public function toCommand(): BuildShipCommand
    {
        return new BuildShipCommand(ShipId::generate(), ShipDesignId::fromString($this->designId));
    }
}
