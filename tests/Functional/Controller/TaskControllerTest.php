<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TaskControllerTest extends WebTestCase
{

    public function testCreateTaskWithValidData(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/v1/tasks', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'My New Task',
            'description' => 'Task description',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHasHeader('Content-Type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('title', $responseData);
        $this->assertArrayHasKey('description', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('createdAt', $responseData);
        $this->assertArrayHasKey('updatedAt', $responseData);
        
        $this->assertEquals('My New Task', $responseData['title']);
        $this->assertEquals('Task description', $responseData['description']);
        $this->assertEquals('todo', $responseData['status']);
    }

    public function testCreateTaskWithoutDescription(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/v1/tasks', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'Task without description',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertEquals('Task without description', $responseData['title']);
        $this->assertNull($responseData['description']);
    }

    public function testCreateTaskWithEmptyTitle(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/v1/tasks', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => '',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testCreateTaskWithTitleTooLong(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/v1/tasks', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => str_repeat('a', 256),
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testListTasksReturnsAllTasks(): void
    {
        $client = static::createClient();

        // Create some tasks
        $client->request('POST', '/api/v1/tasks', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['title' => 'Task 1']));

        $client->request('POST', '/api/v1/tasks', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['title' => 'Task 2']));

        // List all tasks
        $client->request('GET', '/api/v1/tasks');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertGreaterThanOrEqual(2, count($responseData));
    }

    public function testListTasksFilteredByStatusTodo(): void
    {
        $client = static::createClient();

        // Create tasks with different statuses
        // Create a todo task
        $client->request('POST', '/api/v1/tasks', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['title' => 'Todo Task']));

        $todoResponse = json_decode($client->getResponse()->getContent(), true);
        $todoTaskId = $todoResponse['id'];

        // Create another task and change its status
        $client->request('POST', '/api/v1/tasks', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['title' => 'InProgress Task']));

        $inProgressResponse = json_decode($client->getResponse()->getContent(), true);
        $inProgressTaskId = $inProgressResponse['id'];

        // Change status to in_progress
        $client->request('PATCH', "/api/v1/tasks/{$inProgressTaskId}/status", [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['status' => 'in_progress']));

        // Filter by 'todo' status
        $client->request('GET', '/api/v1/tasks?status=todo');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);

        // Verify all returned tasks have 'todo' status
        foreach ($responseData as $task) {
            $this->assertEquals('todo', $task['status']);
        }
    }

    public function testListTasksFilteredByStatusInProgress(): void
    {
        $client = static::createClient();

        // Create a task and change status to in_progress
        $client->request('POST', '/api/v1/tasks', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['title' => 'Task for filtering']));

        $response = json_decode($client->getResponse()->getContent(), true);
        $taskId = $response['id'];

        $client->request('PATCH', "/api/v1/tasks/{$taskId}/status", [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['status' => 'in_progress']));

        // Filter by 'in_progress' status
        $client->request('GET', '/api/v1/tasks?status=in_progress');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        // Verify all returned tasks have 'in_progress' status
        foreach ($responseData as $task) {
            $this->assertEquals('in_progress', $task['status']);
        }
    }

    public function testListTasksFilteredByStatusDone(): void
    {
        $client = static::createClient();

        // Create a task and change status to done
        $client->request('POST', '/api/v1/tasks', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['title' => 'Task for done status']));

        $response = json_decode($client->getResponse()->getContent(), true);
        $taskId = $response['id'];

        // Change to in_progress first (business rule)
        $client->request('PATCH', "/api/v1/tasks/{$taskId}/status", [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['status' => 'in_progress']));

        // Then change to done
        $client->request('PATCH', "/api/v1/tasks/{$taskId}/status", [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['status' => 'done']));

        // Filter by 'done' status
        $client->request('GET', '/api/v1/tasks?status=done');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        // Verify all returned tasks have 'done' status
        foreach ($responseData as $task) {
            $this->assertEquals('done', $task['status']);
        }
    }
}
