<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Query;

use App\Application\DTO\GetTaskByIdData;
use App\Application\DTO\TaskResponse;
use App\Application\Query\GetTaskByIdQuery;
use App\Domain\Entity\Task;
use App\Domain\Exception\TaskNotFoundException;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Domain\ValueObject\TaskId;
use PHPUnit\Framework\TestCase;

class GetTaskByIdQueryTest extends TestCase
{
    public function testHandleReturnsTaskResponse(): void
    {
        $repository = $this->createMock(TaskRepositoryInterface::class);
        $query = new GetTaskByIdQuery($repository);
        
        $taskId = TaskId::generate();
        $task = Task::create($taskId, 'Test Task', 'Test Description');
        
        $repository->expects($this->once())
            ->method('findById')
            ->with($taskId)
            ->willReturn($task);
        
        $data = new GetTaskByIdData(
            id: $taskId->getValue()
        );
        
        $response = $query->handle($data);
        
        $this->assertInstanceOf(TaskResponse::class, $response);
        $this->assertEquals($taskId->getValue(), $response->id);
        $this->assertEquals('Test Task', $response->title);
        $this->assertEquals('Test Description', $response->description);
        $this->assertEquals('todo', $response->status);
        $this->assertNotNull($response->createdAt);
        $this->assertNotNull($response->updatedAt);
    }
    
    public function testHandleThrowsExceptionWhenTaskNotFound(): void
    {
        $this->expectException(TaskNotFoundException::class);
        $this->expectExceptionMessage('Task not found');
        
        $repository = $this->createMock(TaskRepositoryInterface::class);
        $query = new GetTaskByIdQuery($repository);
        
        $taskId = TaskId::generate();
        
        $repository->expects($this->once())
            ->method('findById')
            ->with($taskId)
            ->willReturn(null);
        
        $data = new GetTaskByIdData(
            id: $taskId->getValue()
        );
        
        $query->handle($data);
    }
}
