<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Application\Ship\Command\BuildShip;
use App\Application\Ship\Query\ShipView;
use App\Domain\Component\ComponentType;
use App\Domain\Ship\ShipClass;
use App\Domain\Ship\ShipId;
use App\Domain\ShipDesign\ShipDesignRepository;
use App\Tests\Unit\Domain\ComponentModelTestHelper;
use App\Tests\Unit\Domain\ShipDesignTestHelper;

trait ShipTestHelper
{
    use ComponentModelTestHelper;
    use ShipDesignTestHelper;

    protected static function buildShip(ShipClass $shipClass): ShipId
    {
        $design = self::drawWith($shipClass, [self::createComponent(ComponentType::Reactor)]);
        self::getContainer()->get(ShipDesignRepository::class)->save($design);

        $shipId = ShipId::generate();
        self::getContainer()->get('command.bus')->dispatch(new BuildShip($shipId, $design->getId()));

        return $shipId;
    }

    protected static function shipViewOf(ShipId $shipId, array $views): ShipView
    {
        /** @var ShipView $view */
        foreach ($views as $view) {
            if ($view->id->equals($shipId)) {
                return $view;
            }
        }

        self::fail(sprintf('No view found for ship "%s".', $shipId));
    }
}
