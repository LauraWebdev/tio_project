<?php

namespace App\Controller\API;

use App\Entity\Event;
use App\Exception\EventDoesNotExistException;
use App\Service\EventService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class EventController extends AbstractController
{
    #[Route('/api/events', name: 'app_events', methods: ['GET', 'HEAD'])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $events = $entityManager->getRepository(Event::class)->findBy([], ['id' => 'ASC']);

        $minimalEvents = array_map(function ($item) {
            return $item->toMinimalArray();
        }, $events);

        return $this->json([
            'count' => count($minimalEvents),
            'events' => $minimalEvents
        ]);
    }

    #[Route('/api/event/{eventId}', name: 'app_events_detail', methods: ['GET', 'HEAD'])]
    public function detail(EntityManagerInterface $entityManager, int $eventId): JsonResponse
    {
        $event = $entityManager->getRepository(Event::class)->findOneBy(['id' => $eventId]);

        if($event === null) {
            return $this->json([
                'slug' => 'event_not_found',
                'message' => 'There is no event with this ID',
            ], 404);
        }

        return $this->json($event->toMinimalArray(true));
    }

    #[Route('/api/events', name: 'app_events_create', methods: ['POST'])]
    public function create(EventService $eventService, Request $request): JsonResponse
    {
        $parameters = json_decode($request->getContent(), true);

        if(!isset($parameters['title']) || !isset($parameters['date']) || !isset($parameters['city'])) {
            return $this->json([
                'slug' => 'parameters_missing',
                'message' => 'Not all required parameters were set',
            ], 400);
        }

        $newEvent = $eventService->create(
            $parameters['title'],
            $parameters['date'],
            $parameters['city'],
        );

        return $this->json($newEvent->toMinimalArray(false));
    }

    #[Route('/api/event/{eventId}', name: 'app_events_update', methods: ['PUT'])]
    public function update(EntityManagerInterface $entityManager, Request $request, EventService $eventService, int $eventId): JsonResponse
    {
        $event = $entityManager->getRepository(Event::class)->findOneBy(['id' => $eventId]);
        $parameters = json_decode($request->getContent(), true);

        if($event === null) {
            return $this->json([
                'slug' => 'event_not_found',
                'message' => 'There is no event with this ID',
            ], 404);
        }

        try {
            $event = $eventService->update(
                $eventId,
                $parameters['title'] ?? $event->getTitle(),
                $parameters['date'] ?? $event->getDate()->format("c"),
                $parameters['city'] ?? $event->getCity()
            );
        } catch(EventDoesNotExistException $e) {
            return $this->json([
                'slug' => 'event_not_found',
                'message' => 'There is no event with this ID',
            ], 404);
        }

        return $this->json($event->toMinimalArray(false));
    }

    #[Route('/api/event/{eventId}', name: 'app_events_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, EventService $eventService, int $eventId): JsonResponse
    {
        $event = $entityManager->getRepository(Event::class)->findOneBy(['id' => $eventId]);

        if($event === null) {
            return $this->json([
                'slug' => 'event_not_found',
                'message' => 'There is no event with this ID',
            ], 404);
        }

        try {
            $eventService->remove($eventId);
        } catch(EventDoesNotExistException $e) {
            return $this->json([
                'slug' => 'event_not_found',
                'message' => 'There is no event with this ID',
            ], 404);
        }

        return $this->json([
            'slug' => 'event_deleted',
            'message' => 'Event was successfully deleted!',
        ]);
    }
}
