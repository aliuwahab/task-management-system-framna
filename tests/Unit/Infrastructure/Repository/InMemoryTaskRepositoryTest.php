<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Repository;

use App\Domain\Entity\Task;
use App\Domain\Repository\TaskFilterCriteria;
use App\Domain\ValueObject\TaskId;
use App\Domain\ValueObject\TaskStatus;
use App\Infrastructure\Repository\InMemoryTaskRepository;
use PHPUnit\Framework\TestCase;

class InMemoryTaskRepositoryTest extends TestCase
{
    private InMemoryTaskRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new InMemoryTaskRepository();
    }

    public function testCanSaveAndFindTask(): void
    {
        $taskId = TaskId::generate();
        $task = Task::create($taskId, 'Test Task', 'Description');

        $this->repository->save($task);

        $foundTask = $this->repository->findById($taskId);

        $this->assertNotNull($foundTask);
        $this->assertEquals($taskId->getValue(), $foundTask->getId()->getValue());
        $this->assertEquals('Test Task', $foundTask->getTitle());
        $this->assertEquals('Description', $foundTask->getDescription());
    }

    public function testFindByIdReturnsNullWhenTaskDoesNotExist(): void
    {
        $taskId = TaskId::generate();

        $foundTask = $this->repository->findById($taskId);

        $this->assertNull($foundTask);
    }

    public function testCanUpdateTask(): void
    {
        $taskId = TaskId::generate();
        $task = Task::create($taskId, 'Original', 'Original Description');
        $this->repository->save($task);

        $task->update('Updated', 'Updated Description');
        $this->repository->save($task);

        $foundTask = $this->repository->findById($taskId);

        $this->assertEquals('Updated', $foundTask->getTitle());
        $this->assertEquals('Updated Description', $foundTask->getDescription());
    }

    public function testFindAllReturnsAllTasks(): void
    {
        $task1 = Task::create(TaskId::generate(), 'Task 1', null);
        $task2 = Task::create(TaskId::generate(), 'Task 2', null);
        $task3 = Task::create(TaskId::generate(), 'Task 3', null);

        $this->repository->save($task1);
        $this->repository->save($task2);
        $this->repository->save($task3);

        $tasks = $this->repository->findAll();

        $this->assertCount(3, $tasks);
    }

    public function testCanDeleteTask(): void
    {
        $taskId = TaskId::generate();
        $task = Task::create($taskId, 'Task to delete', null);
        $this->repository->save($task);

        $this->repository->delete($task);

        $foundTask = $this->repository->findById($taskId);
        $this->assertNull($foundTask);
    }

    public function testFindAllWithStatusFilterReturnsOnlyMatchingTasks(): void
    {
        // Create tasks with different statuses
        $todoTask1 = Task::create(TaskId::generate(), 'Todo 1', null);
        $todoTask2 = Task::create(TaskId::generate(), 'Todo 2', null);

        $inProgressTask = Task::create(TaskId::generate(), 'In Progress', null);
        $inProgressTask->changeStatus(TaskStatus::inProgress());

        $doneTask = Task::create(TaskId::generate(), 'Done', null);
        $doneTask->changeStatus(TaskStatus::inProgress());
        $doneTask->changeStatus(TaskStatus::done());

        $this->repository->save($todoTask1);
        $this->repository->save($todoTask2);
        $this->repository->save($inProgressTask);
        $this->repository->save($doneTask);

        // Filter by 'todo'
        $criteria = new TaskFilterCriteria(status: TaskStatus::TODO);
        $todoTasks = $this->repository->findAll($criteria);

        $this->assertCount(2, $todoTasks);
        foreach ($todoTasks as $task) {
            $this->assertTrue($task->getStatus()->isTodo());
        }

        // Filter by 'in_progress'
        $criteria = new TaskFilterCriteria(status: TaskStatus::IN_PROGRESS);
        $inProgressTasks = $this->repository->findAll($criteria);

        $this->assertCount(1, $inProgressTasks);
        $this->assertTrue($inProgressTasks[0]->getStatus()->isInProgress());

        // Filter by 'done'
        $criteria = new TaskFilterCriteria(status: TaskStatus::DONE);
        $doneTasks = $this->repository->findAll($criteria);

        $this->assertCount(1, $doneTasks);
        $this->assertTrue($doneTasks[0]->getStatus()->isDone());
    }

    public function testFindAllWithNullCriteriaReturnsAllTasks(): void
    {
        $task1 = Task::create(TaskId::generate(), 'Task 1', null);
        $task2 = Task::create(TaskId::generate(), 'Task 2', null);
        $task2->changeStatus(TaskStatus::inProgress());

        $this->repository->save($task1);
        $this->repository->save($task2);

        $allTasks = $this->repository->findAll(null);

        $this->assertCount(2, $allTasks);
    }

    public function testFindAllWithEmptyCriteriaReturnsAllTasks(): void
    {
        $task1 = Task::create(TaskId::generate(), 'Task 1', null);
        $task2 = Task::create(TaskId::generate(), 'Task 2', null);

        $this->repository->save($task1);
        $this->repository->save($task2);

        $criteria = new TaskFilterCriteria(); // No filters set
        $allTasks = $this->repository->findAll($criteria);

        $this->assertCount(2, $allTasks);
    }

    public function testClearRemovesAllTasks(): void
    {
        $task1 = Task::create(TaskId::generate(), 'Task 1', null);
        $task2 = Task::create(TaskId::generate(), 'Task 2', null);

        $this->repository->save($task1);
        $this->repository->save($task2);

        $this->assertCount(2, $this->repository->findAll());

        $this->repository->clear();

        $this->assertCount(0, $this->repository->findAll());
    }

    public function testCountReturnsCorrectNumber(): void
    {
        $this->assertEquals(0, $this->repository->count());

        $task1 = Task::create(TaskId::generate(), 'Task 1', null);
        $this->repository->save($task1);

        $this->assertEquals(1, $this->repository->count());

        $task2 = Task::create(TaskId::generate(), 'Task 2', null);
        $this->repository->save($task2);

        $this->assertEquals(2, $this->repository->count());

        $this->repository->delete($task1);

        $this->assertEquals(1, $this->repository->count());
    }

    public function testSavingExistingTaskUpdatesIt(): void
    {
        $taskId = TaskId::generate();
        $task = Task::create($taskId, 'Original Title', null);

        $this->repository->save($task);
        $this->assertEquals(1, $this->repository->count());

        $task->update('Updated Title', 'New Description');
        $this->repository->save($task);

        // Should still have only 1 task (updated, not duplicated)
        $this->assertEquals(1, $this->repository->count());

        $foundTask = $this->repository->findById($taskId);
        $this->assertEquals('Updated Title', $foundTask->getTitle());
        $this->assertEquals('New Description', $foundTask->getDescription());
    }

    public function testRepositoryIsIsolatedBetweenInstances(): void
    {
        $repo1 = new InMemoryTaskRepository();
        $repo2 = new InMemoryTaskRepository();

        $task = Task::create(TaskId::generate(), 'Task', null);
        $repo1->save($task);

        $this->assertEquals(1, $repo1->count());
        $this->assertEquals(0, $repo2->count());
    }
}
