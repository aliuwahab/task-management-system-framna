<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command;

use App\Application\Command\UpdateTaskCommand;
use App\Application\DTO\UpdateTaskData;
use App\Domain\Entity\Task;
use App\Domain\Exception\TaskNotFoundException;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Domain\ValueObject\TaskId;
use PHPUnit\Framework\TestCase;

class UpdateTaskCommandTest extends TestCase
{
    public function testHandleUpdatesTaskWithGivenData(): void
    {
        $repository = $this->createMock(TaskRepositoryInterface::class);
        $command = new UpdateTaskCommand($repository);
        
        $taskId = TaskId::generate();
        $existingTask = Task::create($taskId, 'Old Title', 'Old Description');
        
        $repository->expects($this->once())
            ->method('findById')
            ->with($taskId)
            ->willReturn($existingTask);
        
        $repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Task $task) {
                return $task->getTitle() === 'Updated Title'
                    && $task->getDescription() === 'Updated Description';
            }));
        
        $data = new UpdateTaskData(
            id: $taskId->getValue(),
            title: 'Updated Title',
            description: 'Updated Description'
        );
        
        $command->handle($data);
    }
    
    public function testHandleCanRemoveDescription(): void
    {
        $repository = $this->createMock(TaskRepositoryInterface::class);
        $command = new UpdateTaskCommand($repository);
        
        $taskId = TaskId::generate();
        $existingTask = Task::create($taskId, 'Title', 'Some Description');
        
        $repository->expects($this->once())
            ->method('findById')
            ->with($taskId)
            ->willReturn($existingTask);
        
        $repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Task $task) {
                return $task->getTitle() === 'Title'
                    && $task->getDescription() === null;
            }));
        
        $data = new UpdateTaskData(
            id: $taskId->getValue(),
            title: 'Title',
            description: null
        );
        
        $command->handle($data);
    }
    
    public function testHandleThrowsExceptionWhenTaskNotFound(): void
    {
        $this->expectException(TaskNotFoundException::class);
        $this->expectExceptionMessage('Task not found');
        
        $repository = $this->createMock(TaskRepositoryInterface::class);
        $command = new UpdateTaskCommand($repository);
        
        $taskId = TaskId::generate();
        
        $repository->expects($this->once())
            ->method('findById')
            ->with($taskId)
            ->willReturn(null);
        
        $repository->expects($this->never())
            ->method('save');
        
        $data = new UpdateTaskData(
            id: $taskId->getValue(),
            title: 'Updated Title',
            description: null
        );
        
        $command->handle($data);
    }
}
