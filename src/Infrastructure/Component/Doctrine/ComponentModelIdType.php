<?php

declare(strict_types=1);

namespace App\Infrastructure\Component\Doctrine;

use App\Domain\Component\ComponentModelId;
use App\Infrastructure\Doctrine\IdentifierType;

final class ComponentModelIdType extends IdentifierType
{
    public const string NAME = 'component_model_id';

    protected function typeName(): string
    {
        return self::NAME;
    }

    protected function valueObjectClass(): string
    {
        return ComponentModelId::class;
    }
}
