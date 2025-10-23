<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Application\DTO\CreateTaskData;
use App\Domain\Entity\Task;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Domain\ValueObject\TaskId;

final readonly class CreateTaskCommand
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {
    }

    public function handle(CreateTaskData $data): string
    {
        $taskId = TaskId::generate();
        
        $task = Task::create(
            $taskId,
            $data->title,
            $data->description
        );

        $this->taskRepository->save($task);

        return $taskId->getValue();
    }
}
