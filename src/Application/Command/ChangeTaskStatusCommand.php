<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Application\DTO\ChangeTaskStatusData;
use App\Domain\Event\EventPublisher;
use App\Domain\Exception\TaskNotFoundException;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Domain\ValueObject\TaskId;
use App\Domain\ValueObject\TaskStatus;

final readonly class ChangeTaskStatusCommand
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
        private EventPublisher $eventPublisher
    ) {
    }

    public function handle(ChangeTaskStatusData $data): void
    {
        $taskId = TaskId::fromString($data->id);

        $task = $this->taskRepository->findById($taskId);

        if ($task === null) {
            throw new TaskNotFoundException('Task not found');
        }

        $newStatus = TaskStatus::fromString($data->status);
        $task->changeStatus($newStatus);

        $this->taskRepository->save($task);
        $this->eventPublisher->publishEventsFrom($task);
    }
}
