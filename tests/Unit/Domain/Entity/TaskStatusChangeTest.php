<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\Task;
use App\Domain\Exception\InvalidTaskStatusTransitionException;
use App\Domain\ValueObject\TaskId;
use App\Domain\ValueObject\TaskStatus;
use PHPUnit\Framework\TestCase;

class TaskStatusChangeTest extends TestCase
{
    public function testCanChangeStatusFromTodoToInProgress(): void
    {
        $task = Task::create(TaskId::generate(), 'My Task', null);
        
        $task->changeStatus(TaskStatus::inProgress());
        
        $this->assertTrue($task->getStatus()->isInProgress());
    }
    
    public function testCanChangeStatusFromInProgressToDone(): void
    {
        $task = Task::create(TaskId::generate(), 'My Task', null);
        $task->changeStatus(TaskStatus::inProgress());
        
        $task->changeStatus(TaskStatus::done());
        
        $this->assertTrue($task->getStatus()->isDone());
    }
    
    public function testCannotChangeStatusFromTodoToDone(): void
    {
        $this->expectException(InvalidTaskStatusTransitionException::class);
        $this->expectExceptionMessage('Cannot change task status from todo to done. Task must be in_progress first.');
        
        $task = Task::create(TaskId::generate(), 'My Task', null);
        
        $task->changeStatus(TaskStatus::done());
    }
    
    public function testCanChangeStatusFromInProgressBackToTodo(): void
    {
        $task = Task::create(TaskId::generate(), 'My Task', null);
        $task->changeStatus(TaskStatus::inProgress());
        
        $task->changeStatus(TaskStatus::todo());
        
        $this->assertTrue($task->getStatus()->isTodo());
    }
    
    public function testUpdatedAtChangesWhenStatusChanges(): void
    {
        $task = Task::create(TaskId::generate(), 'My Task', null);
        $originalUpdatedAt = $task->getUpdatedAt();
        
        usleep(1000); // Small delay to ensure time difference
        
        $task->changeStatus(TaskStatus::inProgress());
        
        $this->assertNotEquals($originalUpdatedAt, $task->getUpdatedAt());
        $this->assertGreaterThan($originalUpdatedAt, $task->getUpdatedAt());
    }
}
