<?php

declare(strict_types=1);

namespace App\Tests\Integration\Fleet;

use App\Application\Fleet\Command\FormFleet;
use App\Domain\Fleet\Fleet;
use App\Domain\Fleet\FleetId;
use App\Domain\Fleet\FleetName;
use App\Domain\Ship\ShipClass;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\ShipTestHelper;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

final class FormFleetTest extends IntegrationTestCase
{
    use ShipTestHelper;

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function testAFleetIsPersisted(): void
    {
        $bus = self::getContainer()->get('command.bus');

        $flagshipId = self::buildShip(ShipClass::Cruiser);
        $destroyerId = self::buildShip(ShipClass::Destroyer);

        $fleetId = FleetId::generate();
        $fleetName = FleetName::create('1st Fleet');
        $command = new FormFleet(
            $fleetId,
            $fleetName,
            [$flagshipId, $destroyerId],
            $flagshipId
        );

        $bus->dispatch($command);

        $this->entityManager->clear();
        /** @var Fleet $fleet */
        $fleet = $this->entityManager->find(Fleet::class, $fleetId);

        self::assertEquals($fleetName, $fleet->getName());
        self::assertEquals($flagshipId, $fleet->getFlagshipId());
        self::assertEqualsCanonicalizing([$flagshipId, $destroyerId], $fleet->getShipIds());
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function testAFleetCannotBePersistedWithAnAlreadyAssignedShip(): void
    {
        $bus = self::getContainer()->get('command.bus');

        $flagshipId = self::buildShip(ShipClass::Cruiser);
        $destroyerId = self::buildShip(ShipClass::Destroyer);
        $otherFlagshipId = self::buildShip(ShipClass::Cruiser);

        $firstFleetId = FleetId::generate();
        $firstFleetName = FleetName::create('1st Fleet');
        $formFirstFleetCommand = new FormFleet(
            $firstFleetId,
            $firstFleetName,
            [$flagshipId, $destroyerId],
            $flagshipId,
        );

        $bus->dispatch($formFirstFleetCommand);

        $secondFleetId = FleetId::generate();
        $secondFleetName = FleetName::create('2nd Fleet');

        $formSecondFleetCommand = new FormFleet(
            $secondFleetId,
            $secondFleetName,
            [$otherFlagshipId, $destroyerId],
            $otherFlagshipId,
        );

        try {
            $bus->dispatch($formSecondFleetCommand);
            self::fail('Forming a fleet with an already assigned ship should be rejected.');
        } catch (HandlerFailedException $e) {
            self::assertInstanceOf(\DomainException::class, $e->getPrevious());
        }

        $this->entityManager->clear();
        self::assertNull($this->entityManager->find(Fleet::class, $secondFleetId));
    }
}
