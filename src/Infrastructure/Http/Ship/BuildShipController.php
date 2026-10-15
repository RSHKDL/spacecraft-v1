<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Ship;

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
    ) {}

    /**
     * @throws ExceptionInterface
     */
    #[Route('/ships/new', name: 'ship_build', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(BuildShipType::class);
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
