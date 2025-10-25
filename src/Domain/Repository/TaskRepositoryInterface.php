<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Task;
use App\Domain\ValueObject\TaskId;

interface TaskRepositoryInterface
{
    public function save(Task $task): void;

    public function findById(TaskId $id): ?Task;

    /**
     * @return Task[]
     */
    public function findAll(?TaskFilterCriteria $criteria = null): array;

    public function delete(Task $task): void;
}
