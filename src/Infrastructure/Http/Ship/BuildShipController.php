<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Ship;

use App\Application\Query\QueryBus;
use App\Application\ShipDesign\Query\ListShipDesigns;
use App\Application\ShipDesign\Query\ShipDesignView;
use App\Infrastructure\Http\Ship\Form\BuildShipType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

final class BuildShipController extends AbstractController
{
    public function __construct(
        #[Autowire(service: 'command.bus')]
        private readonly MessageBusInterface $commandBus,
        private readonly QueryBus $queryBus,
    ) {}

    /**
     * @throws ExceptionInterface
     */
    #[Route('/ships/new', name: 'ship_build', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        /** @var list<ShipDesignView> $designs */
        $designs = $this->queryBus->ask(new ListShipDesigns());
        $form = $this->createForm(BuildShipType::class, options: ['designs' => $designs]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->dispatch($form->getData()->toCommand());

            $this->addFlash('success', 'ship.build.success');

            return $this->redirectToRoute('ship_list');
        }

        return $this->render('ship/build.html.twig', [
            'form' => $form,
        ]);
    }
}
