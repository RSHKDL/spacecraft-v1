<?php

declare(strict_types=1);

namespace App\Infrastructure\Ship\Doctrine;

use App\Domain\Ship\ShipId;
use App\Infrastructure\Doctrine\IdentifierType;

final class ShipIdType extends IdentifierType
{
    public const string NAME = 'ship_id';

    protected function typeName(): string
    {
        return self::NAME;
    }

    protected function valueObjectClass(): string
    {
        return ShipId::class;
    }
}
