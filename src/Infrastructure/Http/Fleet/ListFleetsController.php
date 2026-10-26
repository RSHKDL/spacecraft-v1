<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Fleet;

use App\Application\Fleet\Query\FleetListView;
use App\Application\Fleet\Query\ListFleets;
use App\Application\Query\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ListFleetsController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {}

    #[Route('/fleets', name: 'fleet_list', methods: ['GET'])]
    public function __invoke(): Response
    {
        /** @var FleetListView[] $fleets */
        $fleets = $this->queryBus->ask(new ListFleets());

        return $this->render('fleet/list.html.twig', [
            'fleets' => $fleets,
        ]);
    }
}
