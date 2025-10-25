<?php

declare(strict_types=1);

namespace App\Application\Query;

use App\Application\DTO\TaskResponse;
use App\Domain\Repository\TaskFilterCriteria;
use App\Domain\Repository\TaskRepositoryInterface;

final readonly class GetAllTasksQuery
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {
    }

    /**
     * @return TaskResponse[]
     */
    public function handle(?TaskFilterCriteria $criteria = null): array
    {
        $tasks = $this->taskRepository->findAll($criteria);

        return array_map(
            fn($task) => TaskResponse::fromTask($task),
            $tasks
        );
    }
}
