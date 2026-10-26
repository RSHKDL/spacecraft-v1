<?php

declare(strict_types=1);

namespace App\Infrastructure\Twig\Components;

use App\Infrastructure\Twig\MenuItem;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final readonly class Sidebar
{
    /**
     * @return list<MenuItem>
     */
    public function getMenuItems(): array
    {
        return [
            new MenuItem('fleet_list', 'nav.fleet_list', 'fa7-solid:flag'),
            new MenuItem('ship_list', 'nav.ship_list', 'fa7-solid:rocket'),
            new MenuItem(null, 'nav.ship_component_list', 'fa7-solid:toolbox'),
            new MenuItem(null, 'nav.crew_list', 'fa7-solid:users'),
        ];
    }
}
