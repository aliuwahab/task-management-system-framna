<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Event;

use App\Domain\Event\EventStore;
use App\Domain\Event\TaskCreatedEvent;
use App\Domain\Event\TaskStatusChangedEvent;
use App\Domain\Event\TaskUpdatedEvent;
use App\Domain\ValueObject\TaskStatus;
use App\Infrastructure\Event\DoctrineEventStore;
use App\Infrastructure\Event\StoredEvent;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DoctrineEventStoreTest extends KernelTestCase
{
    private EventStore $eventStore;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->eventStore = self::getContainer()->get(DoctrineEventStore::class);

        // Clear stored_events table before each test
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->createQuery('DELETE FROM App\Infrastructure\Event\StoredEvent')->execute();
    }

    public function testCanAppendEventToStore(): void
    {
        $aggregateId = 'task-123';
        $event = new TaskCreatedEvent($aggregateId, 'My Task', 'Description', TaskStatus::TODO);

        $this->eventStore->append($event);

        $events = $this->eventStore->getEventsForAggregate($aggregateId);

        $this->assertCount(1, $events);
        $this->assertInstanceOf(StoredEvent::class, $events[0]);

        /** @var StoredEvent $storedEvent */
        $storedEvent = $events[0];
        $this->assertEquals($aggregateId, $storedEvent->getAggregateId());
        $this->assertEquals('TaskCreatedEvent', $storedEvent->getEventName());

        $payload = $storedEvent->getPayload();
        $this->assertEquals('My Task', $payload['title']);
        $this->assertEquals('Description', $payload['description']);
        $this->assertEquals(TaskStatus::TODO, $payload['status']);
    }

    public function testCanRetrieveEventsForSpecificAggregate(): void
    {
        $aggregateId1 = 'task-123';
        $aggregateId2 = 'task-456';

        $event1 = new TaskCreatedEvent($aggregateId1, 'Task 1', null, TaskStatus::TODO);
        $event2 = new TaskCreatedEvent($aggregateId2, 'Task 2', null, TaskStatus::TODO);
        $event3 = new TaskUpdatedEvent($aggregateId1, 'Task 1 Updated', null);

        $this->eventStore->append($event1);
        $this->eventStore->append($event2);
        $this->eventStore->append($event3);

        $events = $this->eventStore->getEventsForAggregate($aggregateId1);

        $this->assertCount(2, $events);

        /** @var StoredEvent $firstEvent */
        $firstEvent = $events[0];
        $this->assertEquals('TaskCreatedEvent', $firstEvent->getEventName());

        /** @var StoredEvent $secondEvent */
        $secondEvent = $events[1];
        $this->assertEquals('TaskUpdatedEvent', $secondEvent->getEventName());
    }

    public function testEventsAreOrderedByOccurredOn(): void
    {
        $aggregateId = 'task-123';

        // Create events with slight time differences
        $event1 = new TaskCreatedEvent($aggregateId, 'Task', null, TaskStatus::TODO);
        $this->eventStore->append($event1);

        usleep(1000); // 1ms delay to ensure different timestamps

        $event2 = new TaskUpdatedEvent($aggregateId, 'Updated Task', null);
        $this->eventStore->append($event2);

        usleep(1000);

        $event3 = new TaskStatusChangedEvent($aggregateId, TaskStatus::TODO, TaskStatus::IN_PROGRESS);
        $this->eventStore->append($event3);

        $events = $this->eventStore->getEventsForAggregate($aggregateId);

        $this->assertCount(3, $events);

        // Verify events are in chronological order
        $this->assertEquals('TaskCreatedEvent', $events[0]->getEventName());
        $this->assertEquals('TaskUpdatedEvent', $events[1]->getEventName());
        $this->assertEquals('TaskStatusChangedEvent', $events[2]->getEventName());

        // Verify timestamps are increasing
        $this->assertLessThanOrEqual(
            $events[1]->getOccurredOn()->getTimestamp(),
            $events[0]->getOccurredOn()->getTimestamp()
        );
        $this->assertLessThanOrEqual(
            $events[2]->getOccurredOn()->getTimestamp(),
            $events[1]->getOccurredOn()->getTimestamp()
        );
    }

    public function testGetAllEventsReturnsAllStoredEvents(): void
    {
        $event1 = new TaskCreatedEvent('task-1', 'Task 1', null, TaskStatus::TODO);
        $event2 = new TaskCreatedEvent('task-2', 'Task 2', null, TaskStatus::TODO);
        $event3 = new TaskUpdatedEvent('task-1', 'Task 1 Updated', null);

        $this->eventStore->append($event1);
        $this->eventStore->append($event2);
        $this->eventStore->append($event3);

        $allEvents = $this->eventStore->getAllEvents();

        $this->assertCount(3, $allEvents);
    }

    public function testGetEventsForNonExistentAggregateReturnsEmptyArray(): void
    {
        $events = $this->eventStore->getEventsForAggregate('non-existent-id');

        $this->assertCount(0, $events);
    }

    public function testStoredEventHasStoredOnTimestamp(): void
    {
        $before = new \DateTimeImmutable();

        $event = new TaskCreatedEvent('task-123', 'Task', null, TaskStatus::TODO);
        $this->eventStore->append($event);

        $after = new \DateTimeImmutable();

        $events = $this->eventStore->getEventsForAggregate('task-123');

        /** @var StoredEvent $storedEvent */
        $storedEvent = $events[0];

        $storedOn = $storedEvent->getStoredOn();
        $this->assertGreaterThanOrEqual($before->getTimestamp(), $storedOn->getTimestamp());
        $this->assertLessThanOrEqual($after->getTimestamp(), $storedOn->getTimestamp());
    }

    public function testEventPayloadIsCorrectlySerializedAndRetrieved(): void
    {
        $aggregateId = 'task-123';
        $title = 'My Task with Special Chars: "quotes" & symbols';
        $description = "Multi\nLine\nDescription";

        $event = new TaskCreatedEvent($aggregateId, $title, $description, TaskStatus::TODO);
        $this->eventStore->append($event);

        $events = $this->eventStore->getEventsForAggregate($aggregateId);

        /** @var StoredEvent $storedEvent */
        $storedEvent = $events[0];
        $payload = $storedEvent->getPayload();

        $this->assertEquals($title, $payload['title']);
        $this->assertEquals($description, $payload['description']);
    }

    public function testMultipleEventsForSameAggregateAreAllRetrieved(): void
    {
        $aggregateId = 'task-123';

        // Simulate a complete task lifecycle
        $event1 = new TaskCreatedEvent($aggregateId, 'New Task', null, TaskStatus::TODO);
        $event2 = new TaskStatusChangedEvent($aggregateId, TaskStatus::TODO, TaskStatus::IN_PROGRESS);
        $event3 = new TaskUpdatedEvent($aggregateId, 'Updated Task', 'Added description');
        $event4 = new TaskStatusChangedEvent($aggregateId, TaskStatus::IN_PROGRESS, TaskStatus::DONE);

        $this->eventStore->append($event1);
        $this->eventStore->append($event2);
        $this->eventStore->append($event3);
        $this->eventStore->append($event4);

        $events = $this->eventStore->getEventsForAggregate($aggregateId);

        $this->assertCount(4, $events);

        // Verify we can reconstruct the task lifecycle from events
        $this->assertEquals('TaskCreatedEvent', $events[0]->getEventName());
        $this->assertEquals('TaskStatusChangedEvent', $events[1]->getEventName());
        $this->assertEquals('TaskUpdatedEvent', $events[2]->getEventName());
        $this->assertEquals('TaskStatusChangedEvent', $events[3]->getEventName());
    }
}
