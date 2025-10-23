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

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testCreateTaskWithTitleTooLong(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/v1/tasks', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => str_repeat('a', 256),
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }
}
