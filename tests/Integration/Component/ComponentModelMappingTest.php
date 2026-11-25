<?php

declare(strict_types=1);

namespace App\Tests\Integration\Component;

use App\Domain\Component\ComponentModel;
use App\Domain\Component\ComponentModelId;
use App\Domain\Component\ComponentType;
use App\Domain\Component\PowerConsumption;
use App\Domain\Component\PowerOutput;
use App\Domain\Ship\ShipClass;
use App\Tests\Integration\IntegrationTestCase;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

/**
 * Write-path seam of the ComponentModel mapping.
 */
final class ComponentModelMappingTest extends IntegrationTestCase
{
    /**
     * Two models, one per power direction: the database stores a bare integer and
     * the component type decides on read whether it is an output or a consumption.
     * The weapon is discontinued so that the state survives the round trip too.
     *
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function testAComponentModelIsReadBackAsItWasReleased(): void
    {
        $reactor = ComponentModel::release(
            ComponentModelId::generate(),
            ComponentType::Reactor,
            [ShipClass::Corvette, ShipClass::Frigate],
            PowerOutput::create(75),
        );

        $weapon = ComponentModel::release(
            ComponentModelId::generate(),
            ComponentType::Weapon,
            [ShipClass::Destroyer],
            PowerConsumption::create(25),
        );
        $weapon->discontinue();

        $this->entityManager->persist($reactor);
        $this->entityManager->persist($weapon);
        $this->entityManager->flush();
        $this->entityManager->clear();

        self::assertEquals($reactor, $this->entityManager->find(ComponentModel::class, $reactor->getId()));
        self::assertEquals($weapon, $this->entityManager->find(ComponentModel::class, $weapon->getId()));
    }
}
