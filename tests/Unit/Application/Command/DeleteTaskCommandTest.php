<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command;

use App\Application\Command\DeleteTaskCommand;
use App\Application\DTO\DeleteTaskData;
use App\Domain\Entity\Task;
use App\Domain\Event\EventPublisher;
use App\Domain\Exception\TaskNotFoundException;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Domain\ValueObject\TaskId;
use PHPUnit\Framework\TestCase;

class DeleteTaskCommandTest extends TestCase
{
    public function testHandleDeletesTask(): void
    {
        $repository = $this->createMock(TaskRepositoryInterface::class);
        $eventPublisher = $this->createMock(EventPublisher::class);
        $command = new DeleteTaskCommand($repository, $eventPublisher);

        $taskId = TaskId::generate();
        $existingTask = Task::create($taskId, 'Task Title', null);

        $repository->expects($this->once())
            ->method('findById')
            ->with($taskId)
            ->willReturn($existingTask);

        $repository->expects($this->once())
            ->method('delete')
            ->with($this->callback(function (Task $task) {
                return $task->isDeleted();
            }));

        $eventPublisher->expects($this->once())
            ->method('publishEventsFrom')
            ->with($this->isInstanceOf(Task::class));

        $data = new DeleteTaskData(
            id: $taskId->getValue()
        );

        $command->handle($data);
    }
    
    public function testHandleThrowsExceptionWhenTaskNotFound(): void
    {
        $this->expectException(TaskNotFoundException::class);
        $this->expectExceptionMessage('Task not found');

        $repository = $this->createMock(TaskRepositoryInterface::class);
        $eventPublisher = $this->createMock(EventPublisher::class);
        $command = new DeleteTaskCommand($repository, $eventPublisher);
        
        $taskId = TaskId::generate();
        
        $repository->expects($this->once())
            ->method('findById')
            ->with($taskId)
            ->willReturn(null);
        
        $repository->expects($this->never())
            ->method('delete');
        
        $data = new DeleteTaskData(
            id: $taskId->getValue()
        );
        
        $command->handle($data);
    }
}
