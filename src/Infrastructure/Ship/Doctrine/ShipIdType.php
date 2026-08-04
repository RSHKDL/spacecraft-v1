<?php

declare(strict_types=1);

namespace App\Infrastructure\Ship\Doctrine;

use App\Domain\Ship\ShipId;
use App\Infrastructure\Doctrine\ValueObjectType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

final class ShipIdType extends ValueObjectType
{
    public const string NAME = 'ship_id';

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
        return ShipId::class;
    }

    protected function fromDatabase(string $value): ShipId
    {
        return ShipId::fromString($value);
    }

    protected function toDatabase(object $valueObject): string
    {
        \assert($valueObject instanceof ShipId);

        return $valueObject->getValue();
    }
}
