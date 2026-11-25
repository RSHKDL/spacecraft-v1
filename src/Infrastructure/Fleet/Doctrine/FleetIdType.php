<?php

declare(strict_types=1);

namespace App\Infrastructure\Fleet\Doctrine;

use App\Domain\Fleet\FleetId;
use App\Infrastructure\Doctrine\IdentifierType;

final class FleetIdType extends IdentifierType
{
    public const string NAME = 'fleet_id';

    protected function typeName(): string
    {
        return self::NAME;
    }

    protected function valueObjectClass(): string
    {
        return FleetId::class;
    }
}
