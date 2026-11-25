<?php

declare(strict_types=1);

namespace App\Infrastructure\ShipDesign\Doctrine;

use App\Domain\ShipDesign\Component;

final class ComponentsType extends AbstractComponentsType
{
    public const string NAME = 'components';

    /**
     * @param list<Component> $value
     * @return list<Component>
     */
    protected function toComponents(mixed $value): array
    {
        return $value;
    }

    /**
     * @param list<Component> $components
     * @return list<Component>
     */
    protected function fromComponents(array $components): array
    {
        return $components;
    }
}
