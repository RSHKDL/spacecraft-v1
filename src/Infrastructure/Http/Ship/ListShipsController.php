<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Ship;

use App\Application\Query\QueryBus;
use App\Application\Ship\Query\ListShips;
use App\Application\Ship\Query\ShipView;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ListShipsController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {}

    #[Route('/ships', name: 'ship_list', methods: ['GET'])]
    public function __invoke(): Response
    {
        /** @var ShipView[] $ships */
        $ships = $this->queryBus->ask(new ListShips());

        return $this->render('ship/list.html.twig', [
            'ships' => $ships,
        ]);
    }
}
