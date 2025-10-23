<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Application\DTO\DeleteTaskData;
use App\Domain\Exception\TaskNotFoundException;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Domain\ValueObject\TaskId;

final readonly class DeleteTaskCommand
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {
    }

    public function handle(DeleteTaskData $data): void
    {
        $taskId = TaskId::fromString($data->id);
        
        $task = $this->taskRepository->findById($taskId);
        
        if ($task === null) {
            throw new TaskNotFoundException('Task not found');
        }
        
        $task->delete();
        
        $this->taskRepository->delete($task);
    }
}
