<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command;

use App\Application\Command\CreateTaskCommand;
use App\Application\Command\CreateTaskCommandHandler;
use App\Domain\Entity\Task;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Domain\ValueObject\TaskStatus;
use PHPUnit\Framework\TestCase;

class CreateTaskCommandHandlerTest extends TestCase
{
    public function testHandleCreatesTaskWithGivenData(): void
    {
        $repository = $this->createMock(TaskRepositoryInterface::class);
        $handler = new CreateTaskCommandHandler($repository);
        
        $command = new CreateTaskCommand(
            'Test Task',
            'Test Description'
        );
        
        $repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Task $task) {
                return $task->getTitle() === 'Test Task'
                    && $task->getDescription() === 'Test Description'
                    && $task->getStatus()->getValue() === TaskStatus::TODO;
            }));
        
        $taskId = $handler->handle($command);
        
        $this->assertNotNull($taskId);
    }
    
    public function testHandleCreatesTaskWithoutDescription(): void
    {
        $repository = $this->createMock(TaskRepositoryInterface::class);
        $handler = new CreateTaskCommandHandler($repository);
        
        $command = new CreateTaskCommand(
            'Test Task',
            null
        );
        
        $repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Task $task) {
                return $task->getTitle() === 'Test Task'
                    && $task->getDescription() === null;
            }));
        
        $taskId = $handler->handle($command);
        
        $this->assertNotNull($taskId);
    }
}
