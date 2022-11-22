<?php

namespace App\Tests\Service;

use App\Repository\EventRepository;
use App\Repository\TicketRepository;
use App\Service\EventService;
use App\Service\TicketService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EventServiceTest extends KernelTestCase
{
    public function testCreate(): void {
        self::bootKernel();
        $eventService = static::getContainer()->get(EventService::class);

        $event = $eventService->create(
            'Test Event',
            '2020-02-02',
            'TestCity'
        );

        $this->assertNotNull($event);
        $this->assertEquals("Test Event", $event->getTitle());
        $this->assertEquals("2020-02-02", $event->getDate()->format("Y-m-d"));
        $this->assertEquals("TestCity", $event->getCity());

        // Cleanup
        $eventService->remove($event->getId());
    }

    public function testUpdate(): void {
        self::bootKernel();
        $eventService = static::getContainer()->get(EventService::class);

        $event = $eventService->create(
            'Test Event',
            '2020-02-02',
            'TestCity'
        );

        $this->assertNotNull($event);
        $this->assertEquals("Test Event", $event->getTitle());
        $this->assertEquals("2020-02-02", $event->getDate()->format("Y-m-d"));
        $this->assertEquals("TestCity", $event->getCity());

        $updatedEvent = $eventService->update(
            $event->getId(),
            'Test Event2',
            '2020-03-03',
            'TestCity2'
        );

        $this->assertNotNull($updatedEvent);
        $this->assertEquals("Test Event2", $updatedEvent->getTitle());
        $this->assertEquals("2020-03-03", $updatedEvent->getDate()->format("Y-m-d"));
        $this->assertEquals("TestCity2", $updatedEvent->getCity());

        // Cleanup
        $eventService->remove($updatedEvent->getId());
    }
}
