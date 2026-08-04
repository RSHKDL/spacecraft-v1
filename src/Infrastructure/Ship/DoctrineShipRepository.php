<?php

declare(strict_types=1);

namespace App\Infrastructure\Ship;

use App\Domain\Ship\Ship;
use App\Domain\Ship\ShipRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Persistent adapter for the ShipRepository port.
 *
 * Deliberately does not flush: the `doctrine_transaction` middleware on
 * command.bus opens the transaction, flushes once every handler has run, then
 * commits -- or rolls back if one throws. One command, one transaction.
 */
final readonly class DoctrineShipRepository implements ShipRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function save(Ship $ship): void
    {
        $this->entityManager->persist($ship);
    }
}