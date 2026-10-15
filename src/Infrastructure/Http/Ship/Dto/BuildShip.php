<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Ship\Dto;

use App\Application\Ship\Command\BuildShip as BuildShipCommand;
use App\Domain\Ship\ShipClass;
use App\Domain\Ship\ShipId;
use Symfony\Component\Validator\Constraints as Assert;

final class BuildShip
{
    #[Assert\NotNull]
    public ?ShipClass $shipClass = null;

    public function toCommand(): BuildShipCommand
    {
        return new BuildShipCommand(ShipId::generate(), $this->shipClass);
    }
}
