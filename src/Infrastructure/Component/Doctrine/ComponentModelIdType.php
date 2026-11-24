<?php

declare(strict_types=1);

namespace App\Infrastructure\Component\Doctrine;

use App\Domain\Component\ComponentModelId;
use App\Infrastructure\Doctrine\ValueObjectType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

final class ComponentModelIdType extends ValueObjectType
{
    public const string NAME = 'component_model_id';

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
        return ComponentModelId::class;
    }

    protected function fromDatabase(string $value): ComponentModelId
    {
        return ComponentModelId::fromString($value);
    }

    protected function toDatabase(object $valueObject): string
    {
        \assert($valueObject instanceof ComponentModelId);

        return $valueObject->getValue();
    }
}
