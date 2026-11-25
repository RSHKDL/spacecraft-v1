<?php

declare(strict_types=1);

namespace App\Infrastructure\Ship\Doctrine;

use App\Domain\Ship\Loadout;
use App\Domain\ShipDesign\Component;
use App\Infrastructure\ShipDesign\Doctrine\AbstractComponentsType;

final class LoadoutType extends AbstractComponentsType
{
    public const string NAME = 'loadout';

    /**
     * @param Loadout $value
     * @return list<Component>
     */
    protected function toComponents(mixed $value): array
    {
        return $value->getComponents();
    }

    protected function fromComponents(array $components): Loadout
    {
        return Loadout::of($components);
    }
}
