<?php

declare(strict_types=1);

namespace App\Tests\Integration\ShipDesign;

use App\Application\Query\QueryBus;
use App\Application\ShipDesign\Query\ListShipDesigns;
use App\Application\ShipDesign\Query\ShipDesignView;
use App\Domain\Component\ComponentType;
use App\Domain\Ship\ShipClass;
use App\Domain\ShipDesign\ShipDesign;
use App\Domain\ShipDesign\ShipDesignId;
use App\Domain\ShipDesign\ShipDesignName;
use App\Domain\ShipDesign\ShipDesignRepository;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Unit\Domain\ComponentModelTestHelper;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

final class ListShipDesignsTest extends IntegrationTestCase
{
    use ComponentModelTestHelper;

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testPersistedShipDesignsCanBeQueriedAndListed(): void
    {
        $harbingerFrigateId = ShipDesignId::generate();
        $harbingerCruiserId = ShipDesignId::generate();
        $vindicatorId = ShipDesignId::generate();

        self::drawDesign($harbingerCruiserId, ShipClass::Cruiser, 'Harbinger');
        self::drawDesign($vindicatorId, ShipClass::Frigate, 'Vindicator');
        self::drawDesign($harbingerFrigateId, ShipClass::Frigate, 'Harbinger', isRevised: true);

        $this->entityManager->flush();

        $queryBus = self::getContainer()->get(QueryBus::class);
        $shipDesignsList = $queryBus->ask(new ListShipDesigns());

        // test both count and order of the $shipDesignsList
        self::assertEquals([
            $harbingerFrigateId,
            $harbingerCruiserId,
            $vindicatorId,
        ], array_column($shipDesignsList, 'id'));

        $harbingerFrigate = self::designViewOf($harbingerFrigateId, $shipDesignsList);
        self::assertSame(ShipClass::Frigate, $harbingerFrigate->class);
        self::assertSame('Harbinger', $harbingerFrigate->name);
        self::assertSame(2, $harbingerFrigate->revision);

        $vindicatorFrigate = self::designViewOf($vindicatorId, $shipDesignsList);
        self::assertSame(ShipClass::Frigate, $vindicatorFrigate->class);
        self::assertSame('Vindicator', $vindicatorFrigate->name);
        self::assertSame(1, $vindicatorFrigate->revision);

        $harbingerCruiser = self::designViewOf($harbingerCruiserId, $shipDesignsList);
        self::assertSame(ShipClass::Cruiser, $harbingerCruiser->class);
        self::assertSame('Harbinger', $harbingerCruiser->name);
        self::assertSame(1, $harbingerCruiser->revision);
    }

    private static function drawDesign(
        ShipDesignId $designId,
        ShipClass $shipClass,
        string $name,
        bool $isRevised = false
    ): void
    {
        $design = ShipDesign::draw(
            $designId,
            ShipDesignName::create($name),
            $shipClass,
            [self::createComponent(ComponentType::Reactor)]
        );

        if ($isRevised) {
            $revisedComponents = [
                self::createComponent(ComponentType::Reactor),
                self::createComponent(ComponentType::Weapon),
            ];
            $design->revise($revisedComponents);
        }

        // no command.bus yet so repository it is.
        self::getContainer()->get(ShipDesignRepository::class)->save($design);
    }

    private static function designViewOf(ShipDesignId $designId, array $views): ShipDesignView
    {
        /** @var ShipDesignView $view */
        foreach ($views as $view) {
            if ($view->id->equals($designId)) {
                return $view;
            }
        }

        self::fail(sprintf('No view found for design "%s".', $designId));
    }
}
