<?php

declare(strict_types=1);

namespace App\Infrastructure\ShipDesign\Doctrine;

use App\Domain\Component\ComponentModelId;
use App\Domain\Component\ComponentType;
use App\Domain\Ship\ShipClass;
use App\Domain\ShipDesign\Component;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Exception\SerializationFailed;
use Doctrine\DBAL\Types\JsonbType;

final class ComponentsType extends JsonbType
{
    public const string NAME = 'components';
    private const string KEY_MODEL_ID = 'model_id';
    private const string KEY_TYPE = 'type';
    private const string KEY_SHIP_CLASSES = 'ship_classes';
    private const string KEY_POWER = 'power';

    /**
     * @param list<Component>|null $value
     * @throws SerializationFailed
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        $componentsAsArray = [];

        foreach ($value as $component) {
            $componentsAsArray[] = [
                self::KEY_MODEL_ID => $component->getModelId()->getValue(),
                self::KEY_TYPE => $component->getType()->value,
                self::KEY_SHIP_CLASSES => array_map(static fn (ShipClass $class): string => $class->value, $component->getSupportedShipClasses()),
                self::KEY_POWER => $component->getPower()->getValue(),
            ];
        }

        return parent::convertToDatabaseValue($componentsAsArray, $platform);
    }

    /**
     * @return list<Component>|null
     * @throws ConversionException
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?array
    {
        $rows = parent::convertToPHPValue($value, $platform);
        if (null === $rows) {
            return null;
        }

        $components = [];

        foreach ($rows as $row) {
            $type = ComponentType::from($row[self::KEY_TYPE]);

            $components[] = Component::createFromStorage(
                ComponentModelId::fromString($row[self::KEY_MODEL_ID]),
                $type,
                array_map(ShipClass::from(...), $row[self::KEY_SHIP_CLASSES]),
                $type->powerOf($row[self::KEY_POWER]),
            );
        }

        return $components;
    }
}
