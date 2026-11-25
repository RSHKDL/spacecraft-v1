<?php

declare(strict_types=1);

namespace App\Tests\Integration\ShipDesign;

use App\Domain\Component\ComponentModel;
use App\Domain\Component\ComponentModelId;
use App\Domain\Component\ComponentType;
use App\Domain\Component\Power;
use App\Domain\Component\PowerConsumption;
use App\Domain\Component\PowerOutput;
use App\Domain\Ship\ShipClass;
use App\Domain\ShipDesign\Component;
use App\Domain\ShipDesign\ShipDesign;
use App\Domain\ShipDesign\ShipDesignId;
use App\Domain\ShipDesign\ShipDesignName;
use App\Tests\Integration\IntegrationTestCase;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

/**
 * Write-path seam of the ShipDesign mapping.
 */
final class ShipDesignMappingTest extends IntegrationTestCase
{
    /**
     * The components column is a collection of value objects, each holding
     * its own list of ship classes and a power whose direction is not stored
     * but derived from the component type on read. Two weapons of the same model
     * check that duplicates survive; the revision is taken past 1 so that a
     * default value cannot pass for a stored one.
     *
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function testAShipDesignIsReadBackAsItWasRevised(): void
    {
        $weaponModel = self::release(ComponentType::Weapon, [ShipClass::Frigate], PowerConsumption::create(20));

        $design = ShipDesign::draw(
            ShipDesignId::generate(),
            ShipDesignName::create('Harbinger'),
            ShipClass::Frigate,
            [
                self::componentOf(ComponentType::Reactor, [ShipClass::Frigate], PowerOutput::create(60)),
                Component::createFrom($weaponModel),
            ],
        );

        $design->revise([
            self::componentOf(ComponentType::Reactor, [ShipClass::Corvette, ShipClass::Frigate], PowerOutput::create(90)),
            Component::createFrom($weaponModel),
            Component::createFrom($weaponModel),
            self::componentOf(ComponentType::Shield, [ShipClass::Frigate, ShipClass::Destroyer], PowerConsumption::create(0)),
        ]);

        $this->entityManager->persist($design);
        $this->entityManager->flush();
        $this->entityManager->clear();

        self::assertEquals($design, $this->entityManager->find(ShipDesign::class, $design->getId()));
    }

    /**
     * @param list<ShipClass> $shipClasses
     */
    private static function componentOf(ComponentType $type, array $shipClasses, Power $power): Component
    {
        return Component::createFrom(self::release($type, $shipClasses, $power));
    }

    /**
     * @param list<ShipClass> $shipClasses
     */
    private static function release(ComponentType $type, array $shipClasses, Power $power): ComponentModel
    {
        return ComponentModel::release(ComponentModelId::generate(), $type, $shipClasses, $power);
    }
}
