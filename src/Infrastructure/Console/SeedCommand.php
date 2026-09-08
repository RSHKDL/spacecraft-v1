<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Application\Fleet\Command\FormFleet;
use App\Application\Ship\Command\BuildShip;
use App\Application\Ship\Command\ChristenShip;
use App\Domain\Fleet\FleetId;
use App\Domain\Fleet\FleetName;
use App\Domain\Ship\ShipClass;
use App\Domain\Ship\ShipId;
use App\Domain\Ship\ShipName;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use JsonException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Fills the database with a small, stable dataset to develop the UI against.
 *
 * Goes through command.bus rather than writing rows: the seed then exercises the
 * real write path -- aggregate invariants, ShipAvailability, and one transaction
 * per command -- so a broken model fails here instead of at display time.
 */
#[AsCommand(
    name: 'app:seed',
    description: 'Seed the database with ships and fleets',
)]
final readonly class SeedCommand
{
    public function __construct(
        #[Autowire(service: 'command.bus')]
        private MessageBusInterface $commandBus,
        private Connection $connection,
        #[Autowire('%kernel.environment%')]
        private string $environment,
        #[Autowire('%kernel.project_dir%/data/seed')]
        private string $seedDir,
        private Filesystem $filesystem,
    ) {}

    public function __invoke(
        SymfonyStyle $io,
        #[Option(description: 'Empty the tables before seeding')]
        bool $purge = false,
    ): int {
        if (!\in_array($this->environment, ['dev', 'test'], true)) {
            $io->error(sprintf('Refusing to seed in the "%s" environment.', $this->environment));

            return Command::FAILURE;
        }

        try {
            if ($purge) {
                $this->purge();
                $io->text('Tables emptied.');
            }

            $shipIds = $this->seedShips();
            $io->text(sprintf('%d ships built and christened.', \count($shipIds)));
            $fleetIds = $this->seedFleets($shipIds);
            $io->text(sprintf('%d fleets formed.', \count($fleetIds)));

            $io->success('Seed complete.');
        } catch (\Throwable $throwable) {
            $io->error($throwable->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Table names come from the migrations, never from input -- the only place
     * in this codebase where SQL is assembled. Truncating the three together
     * lets Postgres ignore the fleet_assignment -> fleet foreign key.
     * @throws Exception
     */
    private function purge(): void
    {
        $this->connection->executeStatement(
            'TRUNCATE TABLE fleet_assignment, fleet, ship RESTART IDENTITY CASCADE',
        );
    }

    /**
     * Reads one of the JSON files under data/seed, keyed by aggregate id.
     *
     * The ids are pinned in the file rather than generated: the URLs of the
     * pages built against this dataset then survive a `--purge`.
     *
     * @return array<string, array<string, mixed>>
     *
     * @throws JsonException
     */
    private function readSeedFile(string $name): array
    {
        $json = $this->filesystem->readFile(sprintf('%s/%s.json', $this->seedDir, $name));

        return json_decode($json, true, flags: \JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, ShipId>
     * @throws ExceptionInterface
     * @throws JsonException
     */
    private function seedShips(): array
    {
        $shipIds = [];

        foreach ($this->readSeedFile('ships') as $id => $data) {
            $shipId = ShipId::fromString($id);
            // Value objects are built here, at the boundary: a typo in the JSON
            // is rejected before any command reaches the domain.
            $shipClass = ShipClass::from($data['class']);
            $shipName = ShipName::create($data['name']);

            $this->commandBus->dispatch(new BuildShip($shipId, $shipClass));
            $this->commandBus->dispatch(new ChristenShip($shipId, $shipName));

            $shipIds[$shipId->getValue()] = $shipId;
        }

        return $shipIds;
    }

    /**
     * @param array<string, ShipId> $knownShipIds
     * @return array<string, FleetId>
     * @throws ExceptionInterface
     * @throws JsonException
     */
    private function seedFleets(array $knownShipIds): array
    {
        $fleetIds = [];

        // There is no foreign key from fleet_assignment to ship: an unknown id
        // would be assigned without complaint and only fail at display time.
        $resolve = static fn (string $shipId): ShipId => $knownShipIds[$shipId]
            ?? throw new \DomainException(sprintf('Unknown ship id "%s" in fleets.json.', $shipId));

        foreach ($this->readSeedFile('fleets') as $id => $data) {
            $fleetId = FleetId::fromString($id);
            // Value objects are built here, at the boundary: a typo in the JSON
            // is rejected before any command reaches the domain.
            $fleetName = FleetName::create($data['name']);
            $flagshipId = $resolve($data['flagshipId']);
            $shipIds = [$flagshipId, ...array_map($resolve, $data['otherShipsId'])];

            $formFleetCommand = new FormFleet(
                $fleetId,
                $fleetName,
                $shipIds,
                $flagshipId,
            );

            $this->commandBus->dispatch($formFleetCommand);

            $fleetIds[$fleetId->getValue()] = $fleetId;
        }

        return $fleetIds;
    }
}
