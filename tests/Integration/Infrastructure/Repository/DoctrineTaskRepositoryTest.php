<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Repository;

use App\Domain\Entity\Task;
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
}
