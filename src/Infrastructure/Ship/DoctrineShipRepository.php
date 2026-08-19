<?php

declare(strict_types=1);

namespace App\Infrastructure\Ship;

use App\Application\Ship\Query\ShipFinder;
use App\Application\Ship\Query\ShipView;
use App\Domain\Ship\Ship;
use App\Domain\Ship\ShipId;
use App\Domain\Ship\ShipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Persistent adapter for the ShipRepository and ShipFinder ports.
 *
 * Deliberately does not flush: the `doctrine_transaction` middleware on
 * command.bus opens the transaction, flushes once every handler has run, then
 * commits or rolls back if one throws. One command, one transaction.
 */
final readonly class DoctrineShipRepository implements ShipRepository, ShipFinder
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function save(Ship $ship): void
    {
        $this->entityManager->persist($ship);
    }

    public function findAll(): array
    {
        $qb = $this->shipQueryBuilder()->orderBy('s.id', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findById(ShipId $id): ?ShipView
    {
        $qb = $this->shipQueryBuilder()
            ->andWhere('s.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    private function shipQueryBuilder(): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder()
            ->select(sprintf('NEW %s(s.id, s.name, s.class, s.hull.current, s.hull.max)', ShipView::class))
            ->from(Ship::class, 's');
    }
}
