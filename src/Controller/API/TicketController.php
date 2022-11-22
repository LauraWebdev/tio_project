<?php

namespace App\Controller\API;

use App\Entity\Ticket;
use App\Exception\EventDoesNotExistException;
use App\Exception\TicketDoesNotExistException;
use App\Service\TicketService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class TicketController extends AbstractController
{
    #[Route('/api/tickets', name: 'app_tickets', methods: ['GET', 'HEAD'])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $tickets = $entityManager->getRepository(Ticket::class)->findBy([], ['id' => 'ASC']);

        $minimalTickets = array_map(function ($item) {
                return $item->toMinimalArray(true);
        }, $tickets);

        return $this->json([
            'count' => count($minimalTickets),
            'tickets' => $minimalTickets
        ]);
    }

    #[Route('/api/tickets', name: 'app_tickets_create', methods: ['POST'])]
    public function create(TicketService $ticketService, Request $request): JsonResponse
    {
        $parameters = json_decode($request->getContent(), true);

        if(!isset($parameters['eventId']) || !isset($parameters['firstName']) || !isset($parameters['lastName'])) {
            return $this->json([
                'slug' => 'parameters_missing',
                'message' => 'Not all required parameters were set',
            ], 400);
        }

        try {
            $newTicket = $ticketService->create(
                $parameters['eventId'],
                $parameters['barcode'] ?? '',
                $parameters['firstName'],
                $parameters['lastName'],
            );
        } catch(EventDoesNotExistException $e) {
            return $this->json([
                'slug' => 'event_not_found',
                'message' => 'There is no event with this ID',
            ], 404);
        }

        return $this->json($newTicket->toMinimalArray(true));
    }

    #[Route('/api/ticket/checkBarcode', name: 'app_tickets_checkBarCode', methods: ['POST'])]
    public function checkBarcode(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $parameters = json_decode($request->getContent(), true);
        $ticket = $entityManager->getRepository(Ticket::class)->findOneBy(['barcode' => $parameters['barcode']]);

        if($ticket === null) {
            return $this->json([
                'slug' => 'ticket_not_found',
                'message' => 'There is no ticket with this ID',
            ], 404);
        }

        return $this->json($ticket->toMinimalArray(true));
    }

    #[Route('/api/ticket/{ticketId}', name: 'app_tickets_detail', methods: ['GET', 'HEAD'])]
    public function detail(EntityManagerInterface $entityManager, int $ticketId): JsonResponse
    {
        $ticket = $entityManager->getRepository(Ticket::class)->findOneBy(['id' => $ticketId]);

        if($ticket === null) {
            return $this->json([
                'slug' => 'ticket_not_found',
                'message' => 'There is no ticket with this ID',
            ], 404);
        }

        return $this->json($ticket->toMinimalArray(true));
    }

    #[Route('/api/ticket/{ticketId}', name: 'app_tickets_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, TicketService $ticketService, int $ticketId): JsonResponse
    {
        $ticket = $entityManager->getRepository(Ticket::class)->findOneBy(['id' => $ticketId]);

        if($ticket === null) {
            return $this->json([
                'slug' => 'ticket_not_found',
                'message' => 'There is no ticket with this ID',
            ], 404);
        }

        try {
            $ticketService->remove($ticketId);
        } catch(TicketDoesNotExistException $e) {
            return $this->json([
                'slug' => 'ticket_not_found',
                'message' => 'There is no ticket with this ID',
            ], 404);
        }

        return $this->json([
            'slug' => 'ticket_deleted',
            'message' => 'Ticket was successfully deleted!',
        ]);
    }

    #[Route('/api/ticket/{ticketId}', name: 'app_tickets_update', methods: ['PUT'])]
    public function update(EntityManagerInterface $entityManager, Request $request, TicketService $ticketService, int $ticketId): JsonResponse
    {
        $ticket = $entityManager->getRepository(Ticket::class)->findOneBy(['id' => $ticketId]);
        $parameters = json_decode($request->getContent(), true);

        if($ticket === null) {
            return $this->json([
                'slug' => 'ticket_not_found',
                'message' => 'There is no ticket with this ID',
            ], 404);
        }

        try {
            $ticket = $ticketService->update(
                $ticketId,
                $parameters['barcode'] ?? $ticket->getBarcode(),
                $parameters['firstName'] ?? $ticket->getFirstName(),
                $parameters['lastName'] ?? $ticket->getLastName()
            );
        } catch(TicketDoesNotExistException $e) {
            return $this->json([
                'slug' => 'ticket_not_found',
                'message' => 'There is no ticket with this ID',
            ], 404);
        }

        return $this->json($ticket->toMinimalArray(true));
    }
}
