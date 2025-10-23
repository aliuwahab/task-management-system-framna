<?php

declare(strict_types=1);

namespace App\Application\Query;

use App\Application\DTO\GetTaskByIdData;
use App\Application\DTO\TaskResponse;
use App\Domain\Exception\TaskNotFoundException;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Domain\ValueObject\TaskId;

final readonly class GetTaskByIdQuery
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {
    }

    public function handle(GetTaskByIdData $data): TaskResponse
    {
        $taskId = TaskId::fromString($data->id);
        
        $task = $this->taskRepository->findById($taskId);
        
        if ($task === null) {
            throw new TaskNotFoundException('Task not found');
        }
        
        return TaskResponse::fromTask($task);
    }
}
