<?php

declare(strict_types=1);

namespace App\Tests\Integration\Ship;

use App\Domain\Component\ComponentType;
use App\Domain\Component\PowerConsumption;
use App\Domain\Component\PowerOutput;
use App\Domain\Ship\Ship;
use App\Domain\Ship\ShipClass;
use App\Domain\Ship\ShipId;
use App\Domain\ShipDesign\Component;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Unit\Domain\ComponentModelTestHelper;
use App\Tests\Unit\Domain\ShipDesignTestHelper;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

/**
 * Write-path seam of the Ship mapping.
 *
 * The design is not persisted: a ship references it by id only, with no
 * foreign key, so its row is not needed to store or read the ship back.
 */
final class ShipMappingTest extends IntegrationTestCase
{
    use ComponentModelTestHelper;
    use ShipDesignTestHelper;

    /**
     * The loadout is a collection of value objects, each holding its own list of
     * ship classes and a power whose direction is derived from the component
     * type on read. Two weapons of the same model check that duplicates
     * survive; the ship is retrofitted to revision 2 so that a default value
     * cannot pass for a stored one.
     *
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function testAShipIsReadBackAsItWasRetrofitted(): void
    {
        $weaponModel = self::releaseModel(ComponentType::Weapon, [ShipClass::Frigate], PowerConsumption::create(20));

        $design = self::drawWith(ShipClass::Frigate, [
            self::createComponent(ComponentType::Reactor, [ShipClass::Frigate], PowerOutput::create(60)),
            Component::createFrom($weaponModel),
        ]);

        $ship = Ship::build(ShipId::generate(), $design);

        $design->revise([
            self::createComponent(ComponentType::Reactor, [ShipClass::Corvette, ShipClass::Frigate], PowerOutput::create(90)),
            Component::createFrom($weaponModel),
            Component::createFrom($weaponModel),
            self::createComponent(ComponentType::Shield, [ShipClass::Frigate, ShipClass::Destroyer], PowerConsumption::create(0)),
        ]);

        $ship->retrofit($design);

        $this->entityManager->persist($ship);
        $this->entityManager->flush();
        $this->entityManager->clear();

        self::assertEquals($ship, $this->entityManager->find(Ship::class, $ship->id));
    }
}
