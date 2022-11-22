<?php
namespace App\Service;

use App\Entity\Ticket;
use App\Exception\EventDoesNotExistException;
use App\Exception\TicketDoesNotExistException;
use App\Repository\EventRepository;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;

class TicketService {
    public EntityManagerInterface $em;
    public EventRepository $eventRepository;
    public TicketRepository $ticketRepository;

    public function __construct(EntityManagerInterface $em, EventRepository $eventRepository, TicketRepository $ticketRepository)
    {
        $this->em = $em;
        $this->eventRepository = $eventRepository;
        $this->ticketRepository = $ticketRepository;
    }

    public function create(int $eventId, string $barcode, string $firstName, string $lastName) : Ticket {
        $event = $this->eventRepository->findOneBy(['id' => $eventId]);

        if($event === null) {
            throw new EventDoesNotExistException("There is no event with this ID");
        }

        // Create a unique generated barcode if empty
        if(empty($barcode)) {
            $barcode = $this->generateBarcode();
        }

        $newTicket = new Ticket();
        $newTicket->setFirstName($firstName);
        $newTicket->setLastName($lastName);
        $newTicket->setEvent($event);
        $newTicket->setBarcode($barcode);

        $this->em->persist($newTicket);
        $this->em->flush();

        return $newTicket;
    }

    public function update(int $ticketId, string $newBarcode, string $newFirstName, string $newLastName) : Ticket {
        $ticket = $this->ticketRepository->findOneBy(['id' => $ticketId]);

        if($ticket === null) {
            throw new TicketDoesNotExistException("There is no ticket with this ID");
        }

        $ticket->setBarcode($newBarcode);
        $ticket->setFirstName($newFirstName);
        $ticket->setLastName($newLastName);

        $this->em->persist($ticket);
        $this->em->flush();

        return $ticket;
    }

    public function regenerateBarcode(int $ticketId) : Ticket {
        $ticket = $this->ticketRepository->findOneBy(['id' => $ticketId]);

        if($ticket === null) {
            throw new TicketDoesNotExistException("There is no ticket with this ID");
        }

        $ticket->setBarcode($this->generateBarcode());

        $this->em->persist($ticket);
        $this->em->flush();

        return $ticket;
    }

    public function remove(int $ticketId) : void {
        $ticket = $this->ticketRepository->findOneBy(['id' => $ticketId]);

        if($ticket === null) {
            throw new TicketDoesNotExistException("There is no ticket with this ID");
        }

        $this->em->remove($ticket);
        $this->em->flush();
    }

    public function generateBarcode() : string {
        $potentialBarcode = "";
        $isUnique = false;

        while(!$isUnique) {
            $potentialBarcode = bin2hex(random_bytes(4));

            // Check against collisions
            $isUnique = $this->ticketRepository->count(['barcode' => $potentialBarcode]) == 0;
        }

        return $potentialBarcode;
    }
}
