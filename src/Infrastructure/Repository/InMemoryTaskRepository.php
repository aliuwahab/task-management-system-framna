<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Task;
use App\Domain\Repository\TaskFilterCriteria;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Domain\ValueObject\TaskId;

/**
 * In-memory implementation of TaskRepository for testing purposes.
 * Provides fast, database-free testing without mocking.
 */
final class InMemoryTaskRepository implements TaskRepositoryInterface
{
    /**
     * @var array<string, Task>
     */
    private array $tasks = [];

    public function save(Task $task): void
    {
        $this->tasks[$task->getId()->getValue()] = $task;
    }

    public function findById(TaskId $id): ?Task
    {
        return $this->tasks[$id->getValue()] ?? null;
    }

    public function findAll(?TaskFilterCriteria $criteria = null): array
    {
        $tasks = array_values($this->tasks);

        // If no criteria or no filters, return all tasks
        if ($criteria === null || !$criteria->hasFilters()) {
            return $tasks;
        }

        // Apply filters
        return array_values(array_filter($tasks, function (Task $task) use ($criteria) {
            // Filter by status if specified
            if ($criteria->status !== null) {
                if ($task->getStatus()->getValue() !== $criteria->status) {
                    return false;
                }
            }

            // Future filters can be added here:
            // if ($criteria->title !== null) {
            //     if (stripos($task->getTitle(), $criteria->title) === false) {
            //         return false;
            //     }
            // }

            return true;
        }));
    }

    public function delete(Task $task): void
    {
        unset($this->tasks[$task->getId()->getValue()]);
    }

    /**
     * Clear all tasks (useful for test setup/teardown)
     */
    public function clear(): void
    {
        $this->tasks = [];
    }

    /**
     * Get count of tasks (useful for assertions)
     */
    public function count(): int
    {
        return count($this->tasks);
    }
}
