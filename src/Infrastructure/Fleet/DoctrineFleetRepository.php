<?php

declare(strict_types=1);

namespace App\Infrastructure\Fleet;

use App\Domain\Fleet\Fleet;
use App\Domain\Fleet\FleetAssignment;
use App\Domain\Fleet\FleetRepository;
use App\Domain\Ship\ShipId;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Persistent adapter for the FleetRepository port.
 *
 * Deliberately does not flush: the `doctrine_transaction` middleware on
 * command.bus opens the transaction, flushes once every handler has run, then
 * commits or rolls back if one throws. One command, one transaction.
 */
final readonly class DoctrineFleetRepository implements FleetRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function save(Fleet $fleet): void
    {
        $this->entityManager->persist($fleet);
    }

    public function findAlreadyAssignedShipIds(array $shipIds): array
    {
        if ([] === $shipIds) {
            return [];
        }

        $rows = $this->entityManager->createQueryBuilder()
            ->select('fa.shipId')
            ->from(FleetAssignment::class, 'fa')
            ->where('fa.shipId IN (:shipIds)')
            ->setParameter(
                'shipIds',
                array_map(static fn (ShipId $shipId): string => $shipId->getValue(), $shipIds),
                ArrayParameterType::STRING,
            )
            ->getQuery()
            ->getResult();

        return array_column($rows, 'shipId');

    }
}
