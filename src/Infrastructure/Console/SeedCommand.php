<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Application\Fleet\Command\FormFleet;
use App\Application\Ship\Command\BuildShip;
use App\Application\Ship\Command\ChristenShip;
use App\Domain\Component\ComponentModel;
use App\Domain\Component\ComponentModelId;
use App\Domain\Component\ComponentType;
use App\Domain\Fleet\FleetId;
use App\Domain\Fleet\FleetName;
use App\Domain\Ship\ShipClass;
use App\Domain\Ship\ShipId;
use App\Domain\Ship\ShipName;
use App\Domain\ShipDesign\Component;
use App\Domain\ShipDesign\ShipDesign;
use App\Domain\ShipDesign\ShipDesignId;
use App\Domain\ShipDesign\ShipDesignName;
use App\Domain\ShipDesign\ShipDesignRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Fills the database with a small, stable dataset to develop the UI against.
 *
 * Ships and fleets go through command.bus rather than writing rows: the seed
 * then exercises the real write path -- aggregate invariants, ShipAvailability,
 * and one transaction per command -- so a broken model fails here instead of
 * at display time.
 *
 * The catalogue and the designs have no command yet, so they are built through
 * their aggregate factories (release(), draw()) and persisted directly. The
 * invariants still run; only the handler is skipped.
 */
#[AsCommand(
    name: 'app:seed',
    description: 'Seed the database with the catalogue, ship designs, ships and fleets',
)]
final readonly class SeedCommand
{
    public function __construct(
        #[Autowire(service: 'command.bus')]
        private MessageBusInterface $commandBus,
        private Connection $connection,
        private EntityManagerInterface $entityManager,
        private ShipDesignRepository $shipDesignRepository,
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

            $models = $this->seedComponentModels();
            $io->text(sprintf('%d component models released.', \count($models)));
            $designIds = $this->seedShipDesigns($models);
            $io->text(sprintf('%d ship designs drawn.', \count($designIds)));
            // Outside command.bus, nothing flushes on our behalf: write the
            // catalogue and the designs before the first command needs them.
            $this->entityManager->flush();

            $shipIds = $this->seedShips($designIds);
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
     * in this codebase where SQL is assembled. Truncating them together lets
     * Postgres ignore the fleet_assignment -> fleet foreign key.
     * @throws Exception
     */
    private function purge(): void
    {
        $this->connection->executeStatement(
            'TRUNCATE TABLE fleet_assignment, fleet, ship, ship_design, component_model RESTART IDENTITY CASCADE',
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
     * The "name" key of components.json is not read: ComponentModel has no name
     * yet. It only tells a human which model an id stands for.
     *
     * No repository exists for ComponentModel (no handler consumes one), hence
     * the EntityManager.
     *
     * @return array<string, ComponentModel>
     * @throws JsonException
     */
    private function seedComponentModels(): array
    {
        $models = [];

        foreach ($this->readSeedFile('components') as $id => $data) {
            // Value objects are built here, at the boundary: a typo in the JSON
            // is rejected before anything reaches the domain.
            $type = ComponentType::from($data['type']);

            $model = ComponentModel::release(
                ComponentModelId::fromString($id),
                $type,
                array_map(ShipClass::from(...), $data['for']),
                $type->powerOf($data['power']),
            );

            $this->entityManager->persist($model);

            $models[$id] = $model;
        }

        return $models;
    }

    /**
     * @param array<string, ComponentModel> $knownModels
     * @return array<string, ShipDesignId>
     * @throws JsonException
     */
    private function seedShipDesigns(array $knownModels): array
    {
        $designIds = [];

        // A component model id is repeated once per copy fitted to the design.
        $resolve = static fn (string $modelId): Component => Component::createFrom(
            $knownModels[$modelId]
                ?? throw new \DomainException(sprintf('Unknown component model id "%s" in designs.json.', $modelId)),
        );

        foreach ($this->readSeedFile('designs') as $id => $data) {
            $design = ShipDesign::draw(
                ShipDesignId::fromString($id),
                ShipDesignName::create($data['name']),
                ShipClass::from($data['for']),
                array_map($resolve, $data['components']),
            );

            $this->shipDesignRepository->save($design);

            $designIds[$id] = $design->getId();
        }

        return $designIds;
    }

    /**
     * @param array<string, ShipDesignId> $knownDesignIds
     * @return array<string, ShipId>
     * @throws ExceptionInterface
     * @throws JsonException
     */
    private function seedShips(array $knownDesignIds): array
    {
        $shipIds = [];

        foreach ($this->readSeedFile('ships') as $id => $data) {
            $shipId = ShipId::fromString($id);
            $designId = $knownDesignIds[$data['design']]
                ?? throw new \DomainException(sprintf('Unknown ship design id "%s" in ships.json.', $data['design']));

            $this->commandBus->dispatch(new BuildShip($shipId, $designId));

            if (isset($data['name'])) {
                $this->commandBus->dispatch(new ChristenShip($shipId, ShipName::create($data['name'])));
            }

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
