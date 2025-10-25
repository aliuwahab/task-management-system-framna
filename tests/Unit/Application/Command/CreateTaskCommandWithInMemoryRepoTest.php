<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command;

use App\Application\Command\CreateTaskCommand;
use App\Application\DTO\CreateTaskData;
use App\Domain\Event\EventPublisher;
use App\Domain\ValueObject\TaskId;
use App\Domain\ValueObject\TaskStatus;
use App\Infrastructure\Repository\InMemoryTaskRepository;
use PHPUnit\Framework\TestCase;

/**
 * Example of testing with InMemoryRepository instead of mocks.
 *
 * Benefits:
 * - No need to set up mock expectations
 * - Tests actual repository behavior (more realistic)
 * - Cleaner, more readable test code
 * - Easier to maintain
 */
class CreateTaskCommandWithInMemoryRepoTest extends TestCase
{
    public function testHandleCreatesTaskSuccessfully(): void
    {
        // Setup - much simpler than mocking!
        $repository = new InMemoryTaskRepository();
        $eventPublisher = $this->createMock(EventPublisher::class);
        $command = new CreateTaskCommand($repository, $eventPublisher);

        $data = new CreateTaskData(
            title: 'My New Task',
            description: 'Task description'
        );

        // Execute
        $taskId = $command->handle($data);

        // Verify - we can directly query the repository!
        $this->assertNotNull($taskId);
        $this->assertEquals(1, $repository->count());

        $savedTask = $repository->findById(TaskId::fromString($taskId));
        $this->assertNotNull($savedTask);
        $this->assertEquals('My New Task', $savedTask->getTitle());
        $this->assertEquals('Task description', $savedTask->getDescription());
        $this->assertEquals(TaskStatus::TODO, $savedTask->getStatus()->getValue());
    }

    public function testHandleCreatesTaskWithoutDescription(): void
    {
        $repository = new InMemoryTaskRepository();
        $eventPublisher = $this->createMock(EventPublisher::class);
        $command = new CreateTaskCommand($repository, $eventPublisher);

        $data = new CreateTaskData(title: 'Task without description');

        $taskId = $command->handle($data);

        // Direct verification
        $savedTask = $repository->findById(TaskId::fromString($taskId));
        $this->assertEquals('Task without description', $savedTask->getTitle());
        $this->assertNull($savedTask->getDescription());
    }

    public function testHandlePublishesEvents(): void
    {
        $repository = new InMemoryTaskRepository();
        $eventPublisher = $this->createMock(EventPublisher::class);

        // We still verify event publishing behavior
        $eventPublisher->expects($this->once())
            ->method('publishEventsFrom');

        $command = new CreateTaskCommand($repository, $eventPublisher);

        $data = new CreateTaskData(title: 'Task');
        $command->handle($data);
    }

    public function testMultipleTasksCanBeCreated(): void
    {
        $repository = new InMemoryTaskRepository();
        $eventPublisher = $this->createMock(EventPublisher::class);
        $command = new CreateTaskCommand($repository, $eventPublisher);

        // Create multiple tasks
        $command->handle(new CreateTaskData(title: 'Task 1'));
        $command->handle(new CreateTaskData(title: 'Task 2'));
        $command->handle(new CreateTaskData(title: 'Task 3'));

        // Verify all are saved
        $this->assertEquals(3, $repository->count());

        $allTasks = $repository->findAll();
        $titles = array_map(fn($task) => $task->getTitle(), $allTasks);

        $this->assertContains('Task 1', $titles);
        $this->assertContains('Task 2', $titles);
        $this->assertContains('Task 3', $titles);
    }

    public function testCreatedTaskHasCorrectInitialState(): void
    {
        $repository = new InMemoryTaskRepository();
        $eventPublisher = $this->createMock(EventPublisher::class);
        $command = new CreateTaskCommand($repository, $eventPublisher);

        $beforeCreation = new \DateTimeImmutable();
        $taskId = $command->handle(new CreateTaskData(title: 'Test Task'));
        $afterCreation = new \DateTimeImmutable();

        $task = $repository->findById(TaskId::fromString($taskId));

        // Verify initial state
        $this->assertTrue($task->getStatus()->isTodo());
        $this->assertGreaterThanOrEqual($beforeCreation, $task->getCreatedAt());
        $this->assertLessThanOrEqual($afterCreation, $task->getCreatedAt());
        $this->assertEquals($task->getCreatedAt(), $task->getUpdatedAt());
    }
}
