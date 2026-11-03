<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Fleet;

use App\Application\Fleet\Command\DisbandFleet;
use App\Domain\Fleet\FleetId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

final class DisbandFleetController extends AbstractController
{
    public function __construct(
        #[Autowire(service: 'command.bus')]
        private readonly MessageBusInterface $commandBus,
    ) {}

    /**
     * @throws ExceptionInterface
     */
    #[Route('/fleets/{id}/disband', name: 'fleet_disband', requirements: ['id' => Requirement::UUID], methods: ['POST'])]
    public function __invoke(Request $request, string $id): Response
    {
        if (!$this->isCsrfTokenValid('submit', $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $this->commandBus->dispatch(new DisbandFleet(FleetId::fromString($id)));

        $this->addFlash('success', 'fleet.disband.success');

        return new Response(headers: ['HX-Redirect' => $this->generateUrl('fleet_list')]);
    }
}
