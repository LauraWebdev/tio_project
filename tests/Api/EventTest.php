<?php

namespace App\Tests\Api;


use App\Service\EventService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EventTest extends WebTestCase
{
    public function testGetEvents(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/events');

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetDetail(): void
    {
        // Create Test Event
        $client = static::createClient();
        $eventService = static::getContainer()->get(EventService::class);

        $event = $eventService->create(
            'Test Event',
            '2020-02-02',
            'TestCity'
        );

        // Get via API
        $client->request(
            'GET',
            '/api/event/'.$event->getId()
        );

        $response = $client->getResponse();
        $jsonResponse = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Test Event', $jsonResponse['title']);
        $this->assertEquals('2020-02-02T00:00:00+00:00', $jsonResponse['date']);
        $this->assertEquals('TestCity', $jsonResponse['city']);
        $this->assertEquals(0, $jsonResponse['tickets_count']);

        // Cleanup
        $eventService->remove($event->getId());
    }

    public function testRemove(): void
    {
        $client = static::createClient();
        $eventService = static::getContainer()->get(EventService::class);

        $event = $eventService->create(
            'Test Event',
            '2020-02-02',
            'TestCity'
        );

        $client->request(
            'DELETE',
            '/api/event/'.$event->getId()
        );

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreate(): void
    {
        $client = static::createClient();
        $eventService = static::getContainer()->get(EventService::class);

        // Create via API
        $payload = '{"title": "Test Event", "city": "TestCity", "date": "2020-02-02T00:00:00.000Z" }';
        $client->request(
            'POST',
            '/api/events',
            [],
            [],
            ['Content-Type' => 'application/json'],
            $payload
        );

        $response = $client->getResponse();
        $jsonResponse = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Test Event', $jsonResponse['title']);
        $this->assertEquals('2020-02-02T00:00:00+00:00', $jsonResponse['date']);
        $this->assertEquals('TestCity', $jsonResponse['city']);
        $this->assertEquals(0, $jsonResponse['tickets_count']);

        // Cleanup
        $eventService->remove($jsonResponse['id']);
    }

    public function testUpdate(): void
    {
        $client = static::createClient();
        $eventService = static::getContainer()->get(EventService::class);

        $event = $eventService->create(
            'Test Event',
            '2020-02-02',
            'TestCity'
        );

        // Create via API
        $payload = '{"title": "Test Event2", "city": "TestCity2", "date": "2020-03-03T00:00:00.000Z" }';
        $client->request(
            'PUT',
            '/api/event/'.$event->getId(),
            [],
            [],
            ['Content-Type' => 'application/json'],
            $payload
        );

        $response = $client->getResponse();
        $jsonResponse = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Test Event2', $jsonResponse['title']);
        $this->assertEquals('2020-03-03T00:00:00+00:00', $jsonResponse['date']);
        $this->assertEquals('TestCity2', $jsonResponse['city']);

        // Cleanup
        $eventService->remove($jsonResponse['id']);
    }
}
