<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Query;

use App\Application\DTO\TaskResponse;
use App\Application\Query\GetAllTasksQuery;
use App\Domain\Entity\Task;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Domain\ValueObject\TaskId;
use PHPUnit\Framework\TestCase;

class GetAllTasksQueryTest extends TestCase
{
    public function testHandleReturnsArrayOfTaskResponses(): void
    {
        $repository = $this->createMock(TaskRepositoryInterface::class);
        $query = new GetAllTasksQuery($repository);
        
        $task1 = Task::create(TaskId::generate(), 'Task 1', 'Description 1');
        $task2 = Task::create(TaskId::generate(), 'Task 2', null);
        $task3 = Task::create(TaskId::generate(), 'Task 3', 'Description 3');
        
        $repository->expects($this->once())
            ->method('findAll')
            ->willReturn([$task1, $task2, $task3]);
        
        $responses = $query->handle();
        
        $this->assertIsArray($responses);
        $this->assertCount(3, $responses);
        $this->assertContainsOnlyInstancesOf(TaskResponse::class, $responses);
        $this->assertEquals('Task 1', $responses[0]->title);
        $this->assertEquals('Task 2', $responses[1]->title);
        $this->assertEquals('Task 3', $responses[2]->title);
    }
    
    public function testHandleReturnsEmptyArrayWhenNoTasks(): void
    {
        $repository = $this->createMock(TaskRepositoryInterface::class);
        $query = new GetAllTasksQuery($repository);
        
        $repository->expects($this->once())
            ->method('findAll')
            ->willReturn([]);
        
        $responses = $query->handle();
        
        $this->assertIsArray($responses);
        $this->assertCount(0, $responses);
    }
}
