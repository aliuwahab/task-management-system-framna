<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Repository;

use App\Domain\Entity\Task;
use App\Domain\Repository\TaskFilterCriteria;
use App\Domain\ValueObject\TaskId;
use App\Domain\ValueObject\TaskStatus;
use App\Infrastructure\Repository\DoctrineTaskRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DoctrineTaskRepositoryTest extends KernelTestCase
{
    private DoctrineTaskRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->repository = self::getContainer()->get(DoctrineTaskRepository::class);
        
        // Clear database before each test
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->createQuery('DELETE FROM App\Domain\Entity\Task')->execute();
    }

    public function testCanSaveAndFindTask(): void
    {
        $taskId = TaskId::generate();
        $task = Task::create($taskId, 'Test Task', 'Test Description');

        $this->repository->save($task);

        $foundTask = $this->repository->findById($taskId);

        $this->assertNotNull($foundTask);
        $this->assertEquals($taskId->getValue(), $foundTask->getId()->getValue());
        $this->assertEquals('Test Task', $foundTask->getTitle());
        $this->assertEquals('Test Description', $foundTask->getDescription());
        $this->assertTrue($foundTask->getStatus()->isTodo());
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
        $task = Task::create($taskId, 'Original Title', 'Original Description');
        $this->repository->save($task);

        $task->update('Updated Title', 'Updated Description');
        $this->repository->save($task);

        $foundTask = $this->repository->findById($taskId);

        $this->assertNotNull($foundTask);
        $this->assertEquals('Updated Title', $foundTask->getTitle());
        $this->assertEquals('Updated Description', $foundTask->getDescription());
    }

    public function testCanChangeTaskStatus(): void
    {
        $taskId = TaskId::generate();
        $task = Task::create($taskId, 'Task', null);
        $this->repository->save($task);

        $task->changeStatus(TaskStatus::inProgress());
        $this->repository->save($task);

        $foundTask = $this->repository->findById($taskId);

        $this->assertNotNull($foundTask);
        $this->assertTrue($foundTask->getStatus()->isInProgress());
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

        $task->delete();
        $this->repository->delete($task);

        $foundTask = $this->repository->findById($taskId);

        $this->assertNull($foundTask);
    }

    public function testFindAllWithStatusFilterReturnOnlyMatchingTasks(): void
    {
        // Create tasks with different statuses
        $todoTask1 = Task::create(TaskId::generate(), 'Todo Task 1', null);
        $todoTask2 = Task::create(TaskId::generate(), 'Todo Task 2', null);

        $inProgressTask = Task::create(TaskId::generate(), 'In Progress Task', null);
        $inProgressTask->changeStatus(TaskStatus::inProgress());

        $doneTask = Task::create(TaskId::generate(), 'Done Task', null);
        $doneTask->changeStatus(TaskStatus::inProgress());
        $doneTask->changeStatus(TaskStatus::done());

        $this->repository->save($todoTask1);
        $this->repository->save($todoTask2);
        $this->repository->save($inProgressTask);
        $this->repository->save($doneTask);

        // Filter by 'todo' status
        $criteria = new TaskFilterCriteria(status: TaskStatus::TODO);
        $todoTasks = $this->repository->findAll($criteria);

        $this->assertCount(2, $todoTasks);
        foreach ($todoTasks as $task) {
            $this->assertTrue($task->getStatus()->isTodo());
        }

        // Filter by 'in_progress' status
        $criteria = new TaskFilterCriteria(status: TaskStatus::IN_PROGRESS);
        $inProgressTasks = $this->repository->findAll($criteria);

        $this->assertCount(1, $inProgressTasks);
        $this->assertTrue($inProgressTasks[0]->getStatus()->isInProgress());

        // Filter by 'done' status
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
}
