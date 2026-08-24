<?php

declare(strict_types=1);

namespace App\Infrastructure\Fleet\Doctrine;

use App\Domain\Fleet\FleetId;
use App\Infrastructure\Doctrine\ValueObjectType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

final class FleetIdType extends ValueObjectType
{
    public const string NAME = 'fleet_id';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        // `uuid` on PostgreSQL: a real 128-bit type, not a 36-char string.
        return $platform->getGuidTypeDeclarationSQL($column);
    }

    protected function typeName(): string
    {
        return self::NAME;
    }

    protected function valueObjectClass(): string
    {
        return FleetId::class;
    }

    protected function fromDatabase(string $value): FleetId
    {
        return FleetId::fromString($value);
    }

    protected function toDatabase(object $valueObject): string
    {
        \assert($valueObject instanceof FleetId);

        return $valueObject->getValue();
    }
}
