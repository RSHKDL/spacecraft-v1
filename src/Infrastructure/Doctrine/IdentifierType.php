<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine;

use App\Domain\Shared\Identifier;
use Doctrine\DBAL\Platforms\AbstractPlatform;

abstract class IdentifierType extends ValueObjectType
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        // `uuid` on PostgreSQL: a real 128-bit type, not a 36-char string.
        return $platform->getGuidTypeDeclarationSQL($column);
    }

    protected function fromDatabase(string $value): Identifier
    {
        return $this->valueObjectClass()::fromString($value);
    }

    protected function toDatabase(object $valueObject): string
    {
        \assert($valueObject instanceof Identifier);

        return $valueObject->getValue();
    }
}
