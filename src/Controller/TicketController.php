<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Ticket;
use App\Exception\EventDoesNotExistException;
use App\Exception\TicketDoesNotExistException;
use App\Service\EventService;
use App\Service\TicketService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TicketController extends AbstractController
{

    #[Route('/event/{eventId}/ticket/create', name: 'app_ticket_create', methods: ['GET', 'HEAD'])]
    public function ticketCreate(EntityManagerInterface $entityManager, int $eventId): Response
    {
        $event = $entityManager->getRepository(Event::class)->findOneBy(['id' => $eventId]);

        return $this->render('ticket/create.html.twig', [
            'event' => $event
        ]);
    }

    #[Route('/event/{eventId}/ticket/create', name: 'app_ticket_create_save', methods: ['POST'])]
    public function ticketCreateSave(EntityManagerInterface $entityManager, TicketService $ticketService, Request $request, int $eventId): Response
    {
        $event = $entityManager->getRepository(Event::class)->findOneBy(['id' => $eventId]);

        if($event === null) {
            $this->addFlash(
                'error',
                'Dieses Event existiert nicht.'
            );

            return $this->redirectToRoute('app_index');
        }

        if(!$request->request->get('firstName') || !$request->request->get('lastName')) {
            $this->addFlash(
                'error',
                'Bitte fülle alle Felder aus!'
            );

            return $this->redirectToRoute('app_ticket_create');
        }

        try {
            $newTicket = $ticketService->create(
                $event->getId(),
                $request->request->get('barcode'),
                $request->request->get('firstName'),
                $request->request->get('lastName'),
            );
        } catch(EventDoesNotExistException $e) {
            $this->addFlash(
                'error',
                'Dieses Event existiert nicht.'
            );

            return $this->redirectToRoute('app_index');
        }

        return $this->redirectToRoute('app_event_detail', ['eventId' => $event->getId()]);
    }

    #[Route('/event/{eventId}/ticket/{ticketId}/remove', name: 'app_ticket_remove')]
    public function ticketRemove(EntityManagerInterface $entityManager, TicketService $ticketService, Request $request, int $eventId, int $ticketId): Response
    {
        $event = $entityManager->getRepository(Event::class)->findOneBy(['id' => $eventId]);
        $ticket = $entityManager->getRepository(Ticket::class)->findOneBy(['id' => $ticketId]);

        if($event === null) {
            $this->addFlash(
                'error',
                'Dieses Event existiert nicht.'
            );

            return $this->redirectToRoute('app_index');
        }

        if($ticket === null) {
            $this->addFlash(
                'error',
                'Dieses Ticket existiert nicht.'
            );

            return $this->redirectToRoute('app_index');
        }

        try {
            $ticketService->remove($ticket->getId());
        } catch(TicketDoesNotExistException $e) {
            $this->addFlash(
                'error',
                'Dieses Ticket existiert nicht.'
            );

            return $this->redirectToRoute('app_index');
        }

        return $this->redirectToRoute('app_event_detail', ['eventId' => $event->getId()]);
    }

    #[Route('/event/{eventId}/ticket/{ticketId}/regenerateBarcode', name: 'app_ticket_regenerateBarcode')]
    public function ticketRegenerateBarcode(EntityManagerInterface $entityManager, TicketService $ticketService, Request $request, int $eventId, int $ticketId): Response
    {
        $event = $entityManager->getRepository(Event::class)->findOneBy(['id' => $eventId]);
        $ticket = $entityManager->getRepository(Ticket::class)->findOneBy(['id' => $ticketId]);

        if($event === null) {
            $this->addFlash(
                'error',
                'Dieses Event existiert nicht.'
            );

            return $this->redirectToRoute('app_index');
        }

        if($ticket === null) {
            $this->addFlash(
                'error',
                'Dieses Ticket existiert nicht.'
            );

            return $this->redirectToRoute('app_index');
        }

        try {
            $ticketService->regenerateBarcode($ticket->getId());
        } catch(TicketDoesNotExistException $e) {
            $this->addFlash(
                'error',
                'Dieses Ticket existiert nicht.'
            );

            return $this->redirectToRoute('app_index');
        }

        return $this->redirectToRoute('app_event_detail', ['eventId' => $event->getId()]);
    }

    #[Route('/event/{eventId}/ticket/{ticketId}/edit', name: 'app_ticket_edit', methods: ['GET', 'HEAD'])]
    public function ticketEdit(EntityManagerInterface $entityManager, int $eventId, int $ticketId): Response
    {
        $event = $entityManager->getRepository(Event::class)->findOneBy(['id' => $eventId]);
        $ticket = $entityManager->getRepository(Ticket::class)->findOneBy(['id' => $ticketId]);

        if($event === null) {
            $this->addFlash(
                'error',
                'Dieses Event existiert nicht.'
            );

            return $this->redirectToRoute('app_index');
        }

        if($ticket === null) {
            $this->addFlash(
                'error',
                'Dieses Ticket existiert nicht.'
            );

            return $this->redirectToRoute('app_index');
        }

        return $this->render('ticket/edit.html.twig', [
            'event' => $event,
            'ticket' => $ticket
        ]);
    }

    #[Route('/event/{eventId}/ticket/{ticketId}/edit', name: 'app_ticket_edit_save', methods: ['POST'])]
    public function ticketEditSave(EntityManagerInterface $entityManager, TicketService $ticketService, Request $request, int $eventId, int $ticketId): Response
    {
        $event = $entityManager->getRepository(Event::class)->findOneBy(['id' => $eventId]);
        $ticket = $entityManager->getRepository(Ticket::class)->findOneBy(['id' => $ticketId]);

        if($event === null) {
            $this->addFlash(
                'error',
                'Dieses Event existiert nicht.'
            );

            return $this->redirectToRoute('app_index');
        }

        if($ticket === null) {
            $this->addFlash(
                'error',
                'Dieses Ticket existiert nicht.'
            );

            return $this->redirectToRoute('app_index');
        }

        if(!$request->request->get('firstName') || !$request->request->get('lastName')) {
            $this->addFlash(
                'error',
                'Bitte fülle alle Felder aus!'
            );

            return $this->redirectToRoute('app_ticket_edit', ['eventId' => $event->getId(), 'ticketId' => $ticket->getId()]);
        }

        try {
            $updatedTicket = $ticketService->update(
                $ticket->getId(),
                $request->request->get('barcode'),
                $request->request->get('firstName'),
                $request->request->get('lastName'),
            );
        } catch(TicketDoesNotExistException $e) {
            $this->addFlash(
                'error',
                'Dieses Ticket existiert nicht.'
            );

            return $this->redirectToRoute('app_index');
        }

        return $this->redirectToRoute('app_event_detail', ['eventId' => $event->getId()]);
    }
}
