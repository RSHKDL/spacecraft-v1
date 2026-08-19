<?php

declare(strict_types=1);

namespace App\Tests\Integration\Ship;

use App\Application\Ship\Command\BuildShip;
use App\Application\Ship\Query\ShipView;
use App\Domain\Ship\ShipClass;
use App\Domain\Ship\ShipId;

trait ShipTestHelper
{
    protected static function buildShip(ShipClass $shipClass): ShipId
    {
        $shipId = ShipId::generate();
        self::getContainer()->get('command.bus')->dispatch(new BuildShip($shipId, $shipClass));

        return $shipId;
    }

    protected static function viewOf(ShipId $shipId, array $views): ShipView
    {
        foreach ($views as $view) {
            if ((string) $view->id === (string) $shipId) {
                return $view;
            }
        }

        self::fail(sprintf('No view found for ship "%s".', $shipId));
    }
}
