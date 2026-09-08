<?php

declare(strict_types=1);

namespace App\Infrastructure\Fleet;

use App\Application\Fleet\Query\FleetFinder;
use App\Application\Fleet\Query\FleetShipView;
use App\Application\Fleet\Query\FleetView;
use App\Domain\Fleet\Fleet;
use App\Domain\Fleet\FleetAssignment;
use App\Domain\Fleet\FleetId;
use App\Domain\Fleet\FleetRepository;
use App\Domain\Ship\Ship;
use App\Domain\Ship\ShipId;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Persistent adapter for the FleetRepository port.
 *
 * Deliberately does not flush: the `doctrine_transaction` middleware on
 * command.bus opens the transaction, flushes once every handler has run, then
 * commits or rolls back if one throws. One command, one transaction.
 */
final readonly class DoctrineFleetRepository implements FleetRepository, FleetFinder
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function save(Fleet $fleet): void
    {
        $this->entityManager->persist($fleet);
    }

    public function remove(Fleet $fleet): void
    {
        $this->entityManager->remove($fleet);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function get(FleetId $id): Fleet
    {
        $fleet = $this->entityManager->find(Fleet::class, $id);
        if (!$fleet) {
            throw new \DomainException(sprintf('No fleet with id "%s"', $id->getValue()));
        }

        return $fleet;
    }

    public function findById(FleetId $id): ?FleetView
    {
        $fleet = $this->entityManager->createQueryBuilder()
            ->select('f.id AS id', 'f.name.value AS name', 'f.flagshipId AS flagshipId')
            ->from(Fleet::class, 'f')
            ->andWhere('f.id = :id')
            ->setParameter('id', $id->getValue())
            ->getQuery()
            ->getOneOrNullResult();

        if (null === $fleet) {
            return null;
        }

        $qb = $this->entityManager->createQueryBuilder();

        $qb->select(sprintf('NEW %s(s.id, s.name, s.class, s.hull.current, s.hull.max)', FleetShipView::class))
            ->from(Ship::class, 's')
            ->innerJoin(FleetAssignment::class, 'fa', Join::WITH, 'fa.shipId = s.id')
            ->andWhere('fa.fleet = :fleetId')
            ->addOrderBy('s.id', 'ASC')
            ->setParameter('fleetId', $id->getValue());

        $fleetShipViews = $qb->getQuery()->getResult();

        return new FleetView(
            $id,
            $fleet['name'],
            $fleet['flagshipId'],
            $fleetShipViews
        );
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
