<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Fleet;

use App\Application\Query\QueryBus;
use App\Application\Ship\Query\ListAvailableShips;
use App\Application\Ship\Query\ShipView;
use App\Infrastructure\Http\Fleet\Form\FormFleetType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

final class FormFleetController extends AbstractController
{
    public function __construct(
        #[Autowire(service: 'command.bus')]
        private readonly MessageBusInterface $commandBus,
        private readonly QueryBus $queryBus,
    ) {}

    /**
     * @throws ExceptionInterface
     */
    #[Route('/fleets/new', name: 'fleet_form', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        /** @var ShipView[] $ships */
        $ships = $this->queryBus->ask(new ListAvailableShips());
        $form = $this->createForm(FormFleetType::class, null, ['ships' => $ships]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->dispatch($form->getData()->toCommand());

            $this->addFlash('success', 'fleet.form.success');

            return $this->redirectToRoute('fleet_list');
        }

        return $this->render('fleet/form.html.twig', [
            'form' => $form,
        ]);
    }
}
