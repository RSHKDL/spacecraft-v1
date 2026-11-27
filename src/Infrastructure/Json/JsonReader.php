<?php

declare(strict_types=1);

namespace App\Infrastructure\Json;

/**
 * Typed reads over decoded JSON (json_decode() yields mixed).
 *
 * Decoded JSON comes from outside the type system -- a seed file, a jsonb
 * column another writer may have touched -- so every value is checked before
 * it reaches a value object. A malformed document fails here with a message
 * naming the key, instead of a TypeError deep in a factory.
 */
final class JsonReader
{
    /**
     * A JSON array of objects, e.g. the rows of a jsonb collection.
     *
     * @return list<array<mixed>>
     */
    public static function readObjectList(mixed $value): array
    {
        if (!\is_array($value) || !array_is_list($value)) {
            throw new \UnexpectedValueException('JSON value must be a list of objects.');
        }

        $objects = [];

        foreach ($value as $item) {
            if (!\is_array($item)) {
                throw new \UnexpectedValueException('JSON value must be a list of objects.');
            }

            $objects[] = $item;
        }

        return $objects;
    }

    /**
     * A JSON object whose values are objects, keyed by id.
     *
     * @return array<string, array<mixed>>
     */
    public static function readObjectMap(mixed $value): array
    {
        if (!\is_array($value)) {
            throw new \UnexpectedValueException('JSON value must be an object keyed by id.');
        }

        $objects = [];

        foreach ($value as $key => $item) {
            if (!\is_string($key) || !\is_array($item)) {
                throw new \UnexpectedValueException('JSON value must map each id to an object.');
            }

            $objects[$key] = $item;
        }

        return $objects;
    }

    /**
     * @param array<mixed> $data
     */
    public static function readString(array $data, string $key): string
    {
        $value = $data[$key] ?? null;

        if (!\is_string($value)) {
            throw new \UnexpectedValueException(sprintf('JSON key "%s" must be a string.', $key));
        }

        return $value;
    }

    /**
     * An absent key and an explicit null both read as null.
     *
     * @param array<mixed> $data
     */
    public static function readOptionalString(array $data, string $key): ?string
    {
        return isset($data[$key]) ? self::readString($data, $key) : null;
    }

    /**
     * @param array<mixed> $data
     */
    public static function readInt(array $data, string $key): int
    {
        $value = $data[$key] ?? null;

        if (!\is_int($value)) {
            throw new \UnexpectedValueException(sprintf('JSON key "%s" must be an integer.', $key));
        }

        return $value;
    }

    /**
     * @param array<mixed> $data
     * @return list<string>
     */
    public static function readStringList(array $data, string $key): array
    {
        $value = $data[$key] ?? null;

        if (!\is_array($value) || !array_is_list($value)) {
            throw new \UnexpectedValueException(sprintf('JSON key "%s" must be a list.', $key));
        }

        $strings = [];

        foreach ($value as $item) {
            if (!\is_string($item)) {
                throw new \UnexpectedValueException(sprintf('JSON key "%s" must only hold strings.', $key));
            }

            $strings[] = $item;
        }

        return $strings;
    }
}
