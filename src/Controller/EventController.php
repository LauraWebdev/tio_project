<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Ticket;
use App\Exception\EventDoesNotExistException;
use App\Service\EventService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{

    #[Route('/event/create', name: 'app_event_create', methods: ['GET', 'HEAD'])]
    public function eventCreate(): Response
    {
        return $this->render('event/create.html.twig');
    }

    #[Route('/event/create', name: 'app_event_create_save', methods: ['POST'])]
    public function eventCreateSave(EventService $eventService, Request $request): Response
    {
        if(!$request->request->get('title') || !$request->request->get('date') || !$request->request->get('city')) {
            $this->addFlash(
                'error',
                'Bitte fülle alle Felder aus!'
            );

            return $this->redirectToRoute('app_event_create');
        }

        $newEvent = $eventService->create(
            $request->request->get('title'),
            $request->request->get('date'),
            $request->request->get('city'),
        );

        return $this->redirectToRoute('app_event_detail', ['eventId' => $newEvent->getId()]);
    }

    #[Route('/event/{eventId}/remove', name: 'app_event_remove')]
    public function eventRemove(EntityManagerInterface $entityManager, EventService $eventService, Request $request, int $eventId): Response
    {
        $event = $entityManager->getRepository(Event::class)->findOneBy(['id' => $eventId]);

        if($event === null) {
            $this->addFlash(
                'error',
                'Dieses Event existiert nicht.'
            );

            return $this->redirectToRoute('app_index');
        }

        try {
            $eventService->remove($event->getId());
        } catch(EventDoesNotExistException $e) {
            $this->addFlash(
                'error',
                'Dieses Event existiert nicht.'
            );

            return $this->redirectToRoute('app_index');
        }

        return $this->redirectToRoute('app_index');
    }

    #[Route('/event/{eventId}', name: 'app_event_detail')]
    public function eventDetail(EntityManagerInterface $entityManager, int $eventId): Response
    {
        $event = $entityManager->getRepository(Event::class)->findOneBy(['id' => $eventId]);
        $tickets = $entityManager->getRepository(Ticket::class)->findBy(['event' => $eventId], ['id' => 'ASC']);

        if($event === null) {
            $this->addFlash(
                'error',
                'Dieses Event existiert nicht.'
            );

            return $this->redirectToRoute('app_index');
        }

        return $this->render('event/detail.html.twig', [
            'event' => $event,
            'tickets' => $tickets
        ]);
    }

    #[Route('/event/{eventId}/edit', name: 'app_event_edit', methods: ['GET', 'HEAD'])]
    public function eventEdit(EntityManagerInterface $entityManager, int $eventId): Response
    {
        $event = $entityManager->getRepository(Event::class)->findOneBy(['id' => $eventId]);

        if($event === null) {
            $this->addFlash(
                'error',
                'Dieses Event existiert nicht.'
            );

            return $this->redirectToRoute('app_index');
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event
        ]);
    }

    #[Route('/event/{eventId}/edit', name: 'app_event_edit_save', methods: ['POST'])]
    public function eventEditSave(EntityManagerInterface $entityManager, EventService $eventService, Request $request, int $eventId): Response
    {
        $event = $entityManager->getRepository(Event::class)->findOneBy(['id' => $eventId]);

        if($event === null) {
            $this->addFlash(
                'error',
                'Dieses Event existiert nicht.'
            );

            return $this->redirectToRoute('app_index');
        }

        if(!$request->request->get('title') || !$request->request->get('date') || !$request->request->get('city')) {
            $this->addFlash(
                'error',
                'Bitte fülle alle Felder aus!'
            );

            return $this->redirectToRoute('app_event_edit', ['eventId' => $event->getId()]);
        }

        try {
            $updatedEvent = $eventService->update(
                $eventId,
                $request->request->get('title'),
                $request->request->get('date'),
                $request->request->get('city'),
            );
        } catch(EventDoesNotExistException $e) {
            $this->addFlash(
                'error',
                'Dieses Event existiert nicht.'
            );

            return $this->redirectToRoute('app_index');
        }

        return $this->redirectToRoute('app_event_detail', ['eventId' => $updatedEvent->getId()]);
    }
}
