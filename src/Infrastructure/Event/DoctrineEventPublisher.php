<?php

declare(strict_types=1);

namespace App\Infrastructure\Event;

use App\Domain\Entity\Task;
use App\Domain\Event\EventPublisher as EventPublisherInterface;
use App\Domain\Event\EventStore;

final readonly class DoctrineEventPublisher implements EventPublisherInterface
{
    public function __construct(
        private EventStore $eventStore
    ) {
    }

    public function publishEventsFrom(Task $task): void
    {
        $events = $task->getRecordedEvents();

        foreach ($events as $event) {
            $this->eventStore->append($event);
        }

        $task->clearRecordedEvents();
    }
}
