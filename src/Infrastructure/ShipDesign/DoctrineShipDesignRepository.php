<?php

declare(strict_types=1);

namespace App\Infrastructure\ShipDesign;

use App\Domain\ShipDesign\ShipDesign;
use App\Domain\ShipDesign\ShipDesignId;
use App\Domain\ShipDesign\ShipDesignRepository;
use App\Infrastructure\Ship\DoctrineShipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

/**
 * @see DoctrineShipRepository for information.
 */
final readonly class DoctrineShipDesignRepository implements ShipDesignRepository
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
}
