<?php

namespace App\Tests\Service;

use App\Repository\EventRepository;
use App\Repository\TicketRepository;
use App\Service\EventService;
use App\Service\TicketService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TicketServiceTest extends KernelTestCase
{
    public function testCreate(): void {
        self::bootKernel();
        $eventService = static::getContainer()->get(EventService::class);
        $ticketService = static::getContainer()->get(TicketService::class);

        $event = $eventService->create('Test Event', 'now', 'TestCity');
        $ticket = $ticketService->create(
            $event->getId(),
            "AAAAAAAA",
            "FirstName",
            "LastName"
        );

        $this->assertNotNull($ticket);
        $this->assertEquals("AAAAAAAA", $ticket->getBarCode());
        $this->assertEquals("FirstName", $ticket->getFirstName());
        $this->assertEquals("LastName", $ticket->getLastName());
        $this->assertEquals($event->getId(), $ticket->getEvent()->getId());

        // Cleanup
        $ticketService->remove($ticket->getId());
        $eventService->remove($event->getId());
    }

    public function testUpdate(): void {
        self::bootKernel();
        $eventService = static::getContainer()->get(EventService::class);
        $ticketService = static::getContainer()->get(TicketService::class);

        $event = $eventService->create('Test Event', 'now', 'TestCity');
        $ticket = $ticketService->create(
            $event->getId(),
            "AAAAAAAA",
            "FirstName",
            "LastName"
        );

        $this->assertNotNull($ticket);
        $this->assertEquals("AAAAAAAA", $ticket->getBarCode());
        $this->assertEquals("FirstName", $ticket->getFirstName());
        $this->assertEquals("LastName", $ticket->getLastName());
        $this->assertEquals($event->getId(), $ticket->getEvent()->getId());

        $updatedTicket = $ticketService->update(
            $ticket->getId(),
            "BBBBBBBB",
            "FirstName2",
            "LastName2"
        );

        $this->assertNotNull($updatedTicket);
        $this->assertEquals("BBBBBBBB", $updatedTicket->getBarCode());
        $this->assertEquals("FirstName2", $updatedTicket->getFirstName());
        $this->assertEquals("LastName2", $updatedTicket->getLastName());

        // Cleanup
        $ticketService->remove($updatedTicket->getId());
        $eventService->remove($event->getId());
    }

    public function testGenerateBarcode(): void {
        self::bootKernel();
        $ticketRepository = static::getContainer()->get(TicketRepository::class);
        $ticketService = static::getContainer()->get(TicketService::class);

        $barcode = $ticketService->generateBarcode();

        // Check if 8 characters long
        $this->assertEquals(8, strlen($barcode));

        // Check if unique
        $this->assertEquals(0, $ticketRepository->count(['barcode' => $barcode]));
    }
}
