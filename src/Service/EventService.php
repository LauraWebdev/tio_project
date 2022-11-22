<?php
namespace App\Service;

use App\Entity\Event;
use App\Exception\EventDoesNotExistException;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;

class EventService {
    public EntityManagerInterface $em;
    public EventRepository $eventRepository;

    public function __construct(EntityManagerInterface $em, EventRepository $eventRepository)
    {
        $this->em = $em;
        $this->eventRepository = $eventRepository;
    }

    public function create(string $title, string $date, string $city) : Event {
        $newEvent = new Event();
        $newEvent->setTitle($title);
        $newEvent->setDate(new \DateTime($date));
        $newEvent->setCity($city);

        $this->em->persist($newEvent);
        $this->em->flush();

        return $newEvent;
    }

    public function update(int $eventId, string $newTitle, string $newDate, string $newCity) : Event {
        $event = $this->eventRepository->findOneBy(['id' => $eventId]);

        if($event === null) {
            throw new EventDoesNotExistException("There is no event with this ID");
        }

        $event->setTitle($newTitle);
        $event->setDate(new \DateTime($newDate));
        $event->setCity($newCity);

        $this->em->persist($event);
        $this->em->flush();

        return $event;
    }

    public function remove(int $eventId) : void {
        $event = $this->eventRepository->findOneBy(['id' => $eventId]);

        if($event === null) {
            throw new EventDoesNotExistException("There is no event with this ID");
        }

        $this->em->remove($event);
        $this->em->flush();
    }
}
