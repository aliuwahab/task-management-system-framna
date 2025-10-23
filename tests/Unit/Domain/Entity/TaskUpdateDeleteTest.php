<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\Task;
use App\Domain\Exception\TaskCannotBeDeletedException;
use App\Domain\ValueObject\TaskId;
use App\Domain\ValueObject\TaskStatus;
use PHPUnit\Framework\TestCase;

class TaskUpdateDeleteTest extends TestCase
{
    public function testCanUpdateTaskTitle(): void
    {
        $task = Task::create(TaskId::generate(), 'Original Title', null);
        
        $task->update('Updated Title', null);
        
        $this->assertEquals('Updated Title', $task->getTitle());
    }
    
    public function testCanUpdateTaskDescription(): void
    {
        $task = Task::create(TaskId::generate(), 'Title', 'Original description');
        
        $task->update('Title', 'Updated description');
        
        $this->assertEquals('Updated description', $task->getDescription());
    }
    
    public function testCanRemoveTaskDescription(): void
    {
        $task = Task::create(TaskId::generate(), 'Title', 'Some description');
        
        $task->update('Title', null);
        
        $this->assertNull($task->getDescription());
    }
    
    public function testUpdateChangesUpdatedAtTimestamp(): void
    {
        $task = Task::create(TaskId::generate(), 'Title', null);
        $originalUpdatedAt = $task->getUpdatedAt();
        
        usleep(1000);
        
        $task->update('New Title', null);
        
        $this->assertNotEquals($originalUpdatedAt, $task->getUpdatedAt());
        $this->assertGreaterThan($originalUpdatedAt, $task->getUpdatedAt());
    }
    
    public function testUpdateValidatesTitleLength(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Task title cannot exceed 255 characters');
        
        $task = Task::create(TaskId::generate(), 'Title', null);
        
        $task->update(str_repeat('a', 256), null);
    }
    
    public function testUpdateValidatesEmptyTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Task title cannot be empty');
        
        $task = Task::create(TaskId::generate(), 'Title', null);
        
        $task->update('', null);
    }
    
    public function testCanDeleteTaskWhenNotDone(): void
    {
        $task = Task::create(TaskId::generate(), 'Title', null);
        
        $task->delete();
        
        $this->assertTrue($task->isDeleted());
    }
    
    public function testCannotDeleteTaskWhenStatusIsDone(): void
    {
        $this->expectException(TaskCannotBeDeletedException::class);
        $this->expectExceptionMessage('Cannot delete a task with status: done');
        
        $task = Task::create(TaskId::generate(), 'Title', null);
        $task->changeStatus(TaskStatus::inProgress());
        $task->changeStatus(TaskStatus::done());
        
        $task->delete();
    }
    
    public function testCanDeleteTaskWhenInProgress(): void
    {
        $task = Task::create(TaskId::generate(), 'Title', null);
        $task->changeStatus(TaskStatus::inProgress());
        
        $task->delete();
        
        $this->assertTrue($task->isDeleted());
    }
}
