<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command;

use App\Application\Command\ChangeTaskStatusCommand;
use App\Application\DTO\ChangeTaskStatusData;
use App\Domain\Entity\Task;
use App\Domain\Exception\TaskNotFoundException;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Domain\ValueObject\TaskId;
use App\Domain\ValueObject\TaskStatus;
use PHPUnit\Framework\TestCase;

class ChangeTaskStatusCommandTest extends TestCase
{
    public function testHandleChangesTaskStatus(): void
    {
        $repository = $this->createMock(TaskRepositoryInterface::class);
        $command = new ChangeTaskStatusCommand($repository);
        
        $taskId = TaskId::generate();
        $existingTask = Task::create($taskId, 'Task Title', null);
        
        $repository->expects($this->once())
            ->method('findById')
            ->with($taskId)
            ->willReturn($existingTask);
        
        $repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Task $task) {
                return $task->getStatus()->isInProgress();
            }));
        
        $data = new ChangeTaskStatusData(
            id: $taskId->getValue(),
            status: TaskStatus::IN_PROGRESS
        );
        
        $command->handle($data);
    }
    
    public function testHandleThrowsExceptionWhenTaskNotFound(): void
    {
        $this->expectException(TaskNotFoundException::class);
        $this->expectExceptionMessage('Task not found');
        
        $repository = $this->createMock(TaskRepositoryInterface::class);
        $command = new ChangeTaskStatusCommand($repository);
        
        $taskId = TaskId::generate();
        
        $repository->expects($this->once())
            ->method('findById')
            ->with($taskId)
            ->willReturn(null);
        
        $repository->expects($this->never())
            ->method('save');
        
        $data = new ChangeTaskStatusData(
            id: $taskId->getValue(),
            status: TaskStatus::DONE
        );
        
        $command->handle($data);
    }
}
