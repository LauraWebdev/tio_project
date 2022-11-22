<?php

namespace App\Tests\Api;


use App\Service\EventService;
use App\Service\TicketService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TicketTest extends WebTestCase
{
    public function testGetTickets(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/tickets');

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetDetail(): void
    {
        // Create Test Event and Ticket
        $client = static::createClient();
        $eventService = static::getContainer()->get(EventService::class);
        $ticketService = static::getContainer()->get(TicketService::class);

        $event = $eventService->create(
            'Test Event',
            '2020-02-02',
            'TestCity'
        );
        $ticket = $ticketService->create(
            $event->getId(),
            'AAAAAAAA',
            'First',
            'Last'
        );

        // Get via API
        $client->request(
            'GET',
            '/api/ticket/'.$ticket->getId()
        );

        $response = $client->getResponse();
        $jsonResponse = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('First', $jsonResponse['firstName']);
        $this->assertEquals('Last', $jsonResponse['lastName']);
        $this->assertEquals($event->getId(), $jsonResponse['event']['id']);

        // Cleanup
        $ticketService->remove($ticket->getId());
        $eventService->remove($event->getId());
    }

    public function testRemove(): void
    {
        $client = static::createClient();
        $eventService = static::getContainer()->get(EventService::class);
        $ticketService = static::getContainer()->get(TicketService::class);

        $event = $eventService->create(
            'Test Event',
            '2020-02-02',
            'TestCity'
        );
        $ticket = $ticketService->create(
            $event->getId(),
            'AAAAAAAA',
            'First',
            'Last'
        );

        $client->request(
            'DELETE',
            '/api/ticket/'.$ticket->getId()
        );

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        // Cleanup
        $eventService->remove($event->getId());
    }

    public function testCreate(): void
    {
        $client = static::createClient();
        $eventService = static::getContainer()->get(EventService::class);
        $ticketService = static::getContainer()->get(TicketService::class);

        $event = $eventService->create(
            'Test Event',
            '2020-02-02',
            'TestCity'
        );

        // Create via API
        $payload = '{"eventId": '.$event->getId().',"firstName": "First","lastName": "Last"}';
        $client->request(
            'POST',
            '/api/tickets',
            [],
            [],
            ['Content-Type' => 'application/json'],
            $payload
        );

        $response = $client->getResponse();
        $jsonResponse = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('First', $jsonResponse['firstName']);
        $this->assertEquals('Last', $jsonResponse['lastName']);
        $this->assertEquals($event->getId(), $jsonResponse['event']['id']);

        // Cleanup
        $ticketService->remove($jsonResponse['id']);
        $eventService->remove($event->getId());
    }

    public function testUpdate(): void
    {
        $client = static::createClient();
        $eventService = static::getContainer()->get(EventService::class);
        $ticketService = static::getContainer()->get(TicketService::class);

        $event = $eventService->create(
            'Test Event',
            '2020-02-02',
            'TestCity'
        );
        $ticket = $ticketService->create(
            $event->getId(),
            'AAAAAAAA',
            'First',
            'Last'
        );

        // Update via API
        $payload = '{"firstName": "First2", "lastName": "Last2"}';
        $client->request(
            'PUT',
            '/api/ticket/'.$ticket->getId(),
            [],
            [],
            ['Content-Type' => 'application/json'],
            $payload
        );

        $response = $client->getResponse();
        $jsonResponse = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('First2', $jsonResponse['firstName']);
        $this->assertEquals('Last2', $jsonResponse['lastName']);

        // Cleanup
        $ticketService->remove($ticket->getId());
        $eventService->remove($event->getId());
    }

    public function testCheckBarcodeCorrect(): void
    {
        $client = static::createClient();
        $eventService = static::getContainer()->get(EventService::class);
        $ticketService = static::getContainer()->get(TicketService::class);

        $event = $eventService->create(
            'Test Event',
            '2020-02-02',
            'TestCity'
        );
        $ticket = $ticketService->create(
            $event->getId(),
            'abcabcab',
            'First',
            'Last'
        );

        $payload = '{"barcode": "abcabcab"}';
        $client->request(
            'POST',
            '/api/ticket/checkBarcode',
            [],
            [],
            ['Content-Type' => 'application/json'],
            $payload
        );

        $response = $client->getResponse();
        $jsonResponse = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('First', $jsonResponse['firstName']);
        $this->assertEquals('Last', $jsonResponse['lastName']);

        // Cleanup
        $ticketService->remove($ticket->getId());
        $eventService->remove($event->getId());
    }

    public function testCheckBarcodeWrong(): void
    {
        $client = static::createClient();
        $eventService = static::getContainer()->get(EventService::class);
        $ticketService = static::getContainer()->get(TicketService::class);

        $event = $eventService->create(
            'Test Event',
            '2020-02-02',
            'TestCity'
        );
        $ticket = $ticketService->create(
            $event->getId(),
            'AAAAAAAA',
            'First',
            'Last'
        );

        $payload = '{"barcode": "TestTest"}';
        $client->request(
            'POST',
            '/api/ticket/checkBarcode',
            [],
            [],
            ['Content-Type' => 'application/json'],
            $payload
        );

        $response = $client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());

        // Cleanup
        $ticketService->remove($ticket->getId());
        $eventService->remove($event->getId());
    }
}
