<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\Type;

/**
 * Base for DBAL types backing a single-column value object.
 *
 * Holds everything that is identical from one value object to the next: null
 * handling, already-converted values, and turning a rejected invariant into a
 * Doctrine conversion error. Subclasses only declare what actually differs.
 *
 * DBAL resolves types by name and instantiates them without arguments, so one
 * name means one class: a single generic type cannot infer its target value
 * object. One small subclass per value object is required.
 */
abstract class ValueObjectType extends Type
{
    /** The name this type is registered under in config/packages/doctrine.yaml. */
    abstract protected function typeName(): string;

    /** @return class-string */
    abstract protected function valueObjectClass(): string;

    /** Runs the domain factory, and therefore its invariants, on every read. */
    abstract protected function fromDatabase(string $value): object;

    abstract protected function toDatabase(object $valueObject): string;

    final public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        $class = $this->valueObjectClass();

        if (!$value instanceof $class) {
            throw InvalidType::new($value, $this->typeName(), ['null', $class]);
        }

        return $this->toDatabase((object)$value);
    }

    final public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?object
    {
        $class = $this->valueObjectClass();

        if ($value === null || $value instanceof $class) {
            return $value;
        }

        if (!\is_string($value)) {
            throw InvalidType::new($value, $this->typeName(), ['null', 'string', $class]);
        }

        try {
            return $this->fromDatabase($value);
        } catch (\InvalidArgumentException $e) {
            throw ValueNotConvertible::new($value, $this->typeName(), $e->getMessage(), $e);
        }
    }
}
