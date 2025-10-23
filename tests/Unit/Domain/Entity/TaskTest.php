<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\Task;
use App\Domain\ValueObject\TaskId;
use App\Domain\ValueObject\TaskStatus;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testCanCreateTask(): void
    {
        $id = TaskId::generate();
        $title = 'My First Task';
        $description = 'This is a test task';
        
        $task = Task::create($id, $title, $description);
        
        $this->assertEquals($id, $task->getId());
        $this->assertEquals($title, $task->getTitle());
        $this->assertEquals($description, $task->getDescription());
        $this->assertEquals(TaskStatus::TODO, $task->getStatus()->getValue());
        $this->assertNotNull($task->getCreatedAt());
        $this->assertNotNull($task->getUpdatedAt());
    }
    
    public function testCanCreateTaskWithoutDescription(): void
    {
        $id = TaskId::generate();
        $title = 'My First Task';
        
        $task = Task::create($id, $title, null);
        
        $this->assertEquals($id, $task->getId());
        $this->assertEquals($title, $task->getTitle());
        $this->assertNull($task->getDescription());
    }
    
    public function testTitleCannotExceed255Characters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Task title cannot exceed 255 characters');
        
        $id = TaskId::generate();
        $title = str_repeat('a', 256);
        
        Task::create($id, $title, null);
    }
    
    public function testTitleCannotBeEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Task title cannot be empty');
        
        $id = TaskId::generate();
        
        Task::create($id, '', null);
    }
}
