<?php

declare(strict_types=1);

namespace App\Infrastructure\ShipDesign;

use App\Application\ShipDesign\Query\ShipDesignFinder;
use App\Application\ShipDesign\Query\ShipDesignView;
use App\Domain\ShipDesign\ShipDesign;
use App\Domain\ShipDesign\ShipDesignId;
use App\Domain\ShipDesign\ShipDesignRepository;
use App\Infrastructure\Ship\DoctrineShipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\QueryBuilder;

/**
 * @see DoctrineShipRepository for information.
 */
final readonly class DoctrineShipDesignRepository implements ShipDesignRepository, ShipDesignFinder
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function save(ShipDesign $shipDesign): void
    {
        $this->entityManager->persist($shipDesign);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function get(ShipDesignId $id): ShipDesign
    {
        $shipDesign = $this->entityManager->find(ShipDesign::class, $id);
        if (!$shipDesign) {
            throw new \DomainException(sprintf('No ship design with id "%s"', $id->getValue()));
        }

        return $shipDesign;
    }

    /** @return list<ShipDesignView> */
    public function findAll(): array
    {
        $qb = $this->designQueryBuilder()
            ->addOrderBy('sd.name.value', 'ASC')
            ->addOrderBy('sd.id', 'ASC');

        return $qb->getQuery()->getResult();
    }

    private function designQueryBuilder(): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder()
            ->select(sprintf('NEW %s(sd.id, sd.name.value, sd.class, sd.revision.value)', ShipDesignView::class))
            ->from(ShipDesign::class, 'sd');
    }
}
