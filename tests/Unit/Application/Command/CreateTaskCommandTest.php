<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command;

use App\Application\Command\CreateTaskCommand;
use App\Application\DTO\CreateTaskData;
use App\Domain\Entity\Task;
use App\Domain\Event\EventPublisher;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Domain\ValueObject\TaskStatus;
use PHPUnit\Framework\TestCase;

class CreateTaskCommandTest extends TestCase
{
    public function testHandleCreatesTaskWithGivenData(): void
    {
        $repository = $this->createMock(TaskRepositoryInterface::class);
        $eventPublisher = $this->createMock(EventPublisher::class);
        $command = new CreateTaskCommand($repository, $eventPublisher);
        
        $data = new CreateTaskData(
            title: 'Test Task',
            description: 'Test Description'
        );
        
        $repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Task $task) {
                return $task->getTitle() === 'Test Task'
                    && $task->getDescription() === 'Test Description'
                    && $task->getStatus()->getValue() === TaskStatus::TODO;
            }));

        $eventPublisher->expects($this->once())
            ->method('publishEventsFrom')
            ->with($this->isInstanceOf(Task::class));

        $taskId = $command->handle($data);
        
        $this->assertNotNull($taskId);
    }
    
    public function testHandleCreatesTaskWithoutDescription(): void
    {
        $repository = $this->createMock(TaskRepositoryInterface::class);
        $eventPublisher = $this->createMock(EventPublisher::class);
        $command = new CreateTaskCommand($repository, $eventPublisher);
        
        $data = new CreateTaskData(
            title: 'Test Task'
        );
        
        $repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Task $task) {
                return $task->getTitle() === 'Test Task'
                    && $task->getDescription() === null;
            }));

        $eventPublisher->expects($this->once())
            ->method('publishEventsFrom')
            ->with($this->isInstanceOf(Task::class));

        $taskId = $command->handle($data);
        
        $this->assertNotNull($taskId);
    }
}
