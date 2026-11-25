<?php

declare(strict_types=1);

namespace App\Infrastructure\ShipDesign\Doctrine;

use App\Domain\ShipDesign\ShipDesignId;
use App\Infrastructure\Doctrine\IdentifierType;

final class ShipDesignIdType extends IdentifierType
{
    public const string NAME = 'ship_design_id';

    protected function typeName(): string
    {
        return self::NAME;
    }

    protected function valueObjectClass(): string
    {
        return ShipDesignId::class;
    }
}
