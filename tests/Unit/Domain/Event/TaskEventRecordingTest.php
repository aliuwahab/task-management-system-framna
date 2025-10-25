<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Event;

use App\Domain\Entity\Task;
use App\Domain\Event\TaskCreatedEvent;
use App\Domain\Event\TaskDeletedEvent;
use App\Domain\Event\TaskStatusChangedEvent;
use App\Domain\Event\TaskUpdatedEvent;
use App\Domain\ValueObject\TaskId;
use App\Domain\ValueObject\TaskStatus;
use PHPUnit\Framework\TestCase;

class TaskEventRecordingTest extends TestCase
{
    public function testTaskCreationRecordsTaskCreatedEvent(): void
    {
        $id = TaskId::generate();
        $title = 'My Task';
        $description = 'Task description';

        $task = Task::create($id, $title, $description);

        $events = $task->getRecordedEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(TaskCreatedEvent::class, $events[0]);

        /** @var TaskCreatedEvent $event */
        $event = $events[0];
        $this->assertEquals($id->getValue(), $event->getAggregateId());
        $this->assertEquals($title, $event->getTitle());
        $this->assertEquals($description, $event->getDescription());
        $this->assertEquals(TaskStatus::TODO, $event->getStatus());
    }

    public function testTaskUpdateRecordsTaskUpdatedEvent(): void
    {
        $task = Task::create(TaskId::generate(), 'Original Title', 'Original Description');
        $task->clearRecordedEvents(); // Clear creation event

        $newTitle = 'Updated Title';
        $newDescription = 'Updated Description';
        $task->update($newTitle, $newDescription);

        $events = $task->getRecordedEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(TaskUpdatedEvent::class, $events[0]);

        /** @var TaskUpdatedEvent $event */
        $event = $events[0];
        $this->assertEquals($task->getId()->getValue(), $event->getAggregateId());
        $this->assertEquals($newTitle, $event->getTitle());
        $this->assertEquals($newDescription, $event->getDescription());
    }

    public function testTaskStatusChangeRecordsTaskStatusChangedEvent(): void
    {
        $task = Task::create(TaskId::generate(), 'My Task', null);
        $task->clearRecordedEvents(); // Clear creation event

        $task->changeStatus(TaskStatus::inProgress());

        $events = $task->getRecordedEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(TaskStatusChangedEvent::class, $events[0]);

        /** @var TaskStatusChangedEvent $event */
        $event = $events[0];
        $this->assertEquals($task->getId()->getValue(), $event->getAggregateId());
        $this->assertEquals(TaskStatus::TODO, $event->getOldStatus());
        $this->assertEquals(TaskStatus::IN_PROGRESS, $event->getNewStatus());
    }

    public function testTaskDeletionRecordsTaskDeletedEvent(): void
    {
        $id = TaskId::generate();
        $title = 'Task to Delete';
        $task = Task::create($id, $title, null);
        $task->clearRecordedEvents(); // Clear creation event

        $task->delete();

        $events = $task->getRecordedEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(TaskDeletedEvent::class, $events[0]);

        /** @var TaskDeletedEvent $event */
        $event = $events[0];
        $this->assertEquals($id->getValue(), $event->getAggregateId());
        $this->assertEquals($title, $event->getTitle());
        $this->assertEquals(TaskStatus::TODO, $event->getStatus());
    }

    public function testMultipleOperationsRecordMultipleEvents(): void
    {
        $task = Task::create(TaskId::generate(), 'My Task', 'Description');

        // Should have 1 event (creation)
        $this->assertCount(1, $task->getRecordedEvents());

        $task->update('Updated Task', 'Updated Description');

        // Should have 2 events (creation + update)
        $this->assertCount(2, $task->getRecordedEvents());

        $task->changeStatus(TaskStatus::inProgress());

        // Should have 3 events (creation + update + status change)
        $this->assertCount(3, $task->getRecordedEvents());
    }

    public function testClearRecordedEventsRemovesAllEvents(): void
    {
        $task = Task::create(TaskId::generate(), 'My Task', null);
        $task->update('Updated', null);

        $this->assertCount(2, $task->getRecordedEvents());

        $task->clearRecordedEvents();

        $this->assertCount(0, $task->getRecordedEvents());
    }

    public function testEventsContainCorrectAggregateId(): void
    {
        $id = TaskId::generate();
        $task = Task::create($id, 'My Task', null);

        foreach ($task->getRecordedEvents() as $event) {
            $this->assertEquals($id->getValue(), $event->getAggregateId());
        }
    }
}
