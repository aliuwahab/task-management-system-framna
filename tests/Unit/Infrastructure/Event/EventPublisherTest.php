<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Event;

use App\Domain\Entity\Task;
use App\Domain\Event\EventStore;
use App\Domain\Event\TaskCreatedEvent;
use App\Domain\Event\TaskUpdatedEvent;
use App\Domain\ValueObject\TaskId;
use App\Infrastructure\Event\DoctrineEventPublisher;
use PHPUnit\Framework\TestCase;

class EventPublisherTest extends TestCase
{
    public function testPublishEventsFromTaskAppendsEventsToEventStore(): void
    {
        $eventStore = $this->createMock(EventStore::class);
        $eventPublisher = new DoctrineEventPublisher($eventStore);

        $task = Task::create(TaskId::generate(), 'My Task', 'Description');

        // EventStore should receive exactly 1 event (TaskCreatedEvent)
        $eventStore->expects($this->once())
            ->method('append')
            ->with($this->isInstanceOf(TaskCreatedEvent::class));

        $eventPublisher->publishEventsFrom($task);
    }

    public function testPublishEventsFromTaskClearsRecordedEvents(): void
    {
        $eventStore = $this->createMock(EventStore::class);
        $eventPublisher = new DoctrineEventPublisher($eventStore);

        $task = Task::create(TaskId::generate(), 'My Task', null);

        $this->assertCount(1, $task->getRecordedEvents());

        $eventPublisher->publishEventsFrom($task);

        // Events should be cleared after publishing
        $this->assertCount(0, $task->getRecordedEvents());
    }

    public function testPublishMultipleEvents(): void
    {
        $eventStore = $this->createMock(EventStore::class);
        $eventPublisher = new DoctrineEventPublisher($eventStore);

        $task = Task::create(TaskId::generate(), 'My Task', null);
        $task->update('Updated Task', 'Updated Description');

        // Should have 2 events: TaskCreatedEvent and TaskUpdatedEvent
        $this->assertCount(2, $task->getRecordedEvents());

        // EventStore should receive append() call twice
        $eventStore->expects($this->exactly(2))
            ->method('append')
            ->with($this->logicalOr(
                $this->isInstanceOf(TaskCreatedEvent::class),
                $this->isInstanceOf(TaskUpdatedEvent::class)
            ));

        $eventPublisher->publishEventsFrom($task);

        // All events should be cleared
        $this->assertCount(0, $task->getRecordedEvents());
    }

    public function testPublishEventsPreservesEventOrder(): void
    {
        $eventStore = $this->createMock(EventStore::class);
        $eventPublisher = new DoctrineEventPublisher($eventStore);

        $task = Task::create(TaskId::generate(), 'My Task', null);
        $task->update('Updated Task', null);

        $recordedEvents = $task->getRecordedEvents();

        // Capture the events in the order they're appended
        $appendedEvents = [];
        $eventStore->expects($this->exactly(2))
            ->method('append')
            ->willReturnCallback(function ($event) use (&$appendedEvents) {
                $appendedEvents[] = $event;
            });

        $eventPublisher->publishEventsFrom($task);

        // Verify the events are published in the same order they were recorded
        $this->assertEquals($recordedEvents[0], $appendedEvents[0]);
        $this->assertEquals($recordedEvents[1], $appendedEvents[1]);
    }

    public function testPublishEventsFromTaskWithNoEventsDoesNothing(): void
    {
        $eventStore = $this->createMock(EventStore::class);
        $eventPublisher = new DoctrineEventPublisher($eventStore);

        $task = Task::create(TaskId::generate(), 'My Task', null);
        $task->clearRecordedEvents(); // Clear all events

        // EventStore should not receive any append() calls
        $eventStore->expects($this->never())
            ->method('append');

        $eventPublisher->publishEventsFrom($task);
    }
}
