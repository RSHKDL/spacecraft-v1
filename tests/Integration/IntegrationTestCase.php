<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Base for tests that hit Postgres.
 *
 * Isolation is a manual truncate rather than a transaction-rollback bundle: one
 * less dependency, and nothing hidden. The trade-off is that a test cannot
 * observe what happens across a real commit boundary, which is precisely what
 * the command bus transaction middleware does, so keep the truncate here.
 */
abstract class IntegrationTestCase extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $this->truncateMappedTables();
    }

    protected function tearDown(): void
    {
        $this->entityManager->close();

        parent::tearDown();
    }

    /**
     * Identifiers cannot be bound as query parameters, so the table names are
     * quoted by the platform instead. They come from the mapping, never from
     * user input, the only place in this codebase where SQL is assembled.
     *
     * @throws Exception
     */
    private function truncateMappedTables(): void
    {
        $connection = $this->entityManager->getConnection();

        $tables = [];

        foreach ($this->entityManager->getMetadataFactory()->getAllMetadata() as $metadata) {
            \assert($metadata instanceof ClassMetadata);

            if ($metadata->isEmbeddedClass || $metadata->isMappedSuperclass) {
                continue;
            }

            $tables[] = $connection->quoteSingleIdentifier($metadata->getTableName());
        }

        if ($tables === []) {
            return;
        }

        $connection->executeStatement(
            \sprintf('TRUNCATE TABLE %s RESTART IDENTITY CASCADE', implode(', ', $tables)),
        );
    }
}
