<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\Application\Command\ChangeTaskStatusCommand;
use App\Application\Command\CreateTaskCommand;
use App\Application\Command\DeleteTaskCommand;
use App\Application\Command\UpdateTaskCommand;
use App\Application\DTO\ChangeTaskStatusData;
use App\Application\DTO\CreateTaskData;
use App\Application\DTO\DeleteTaskData;
use App\Application\DTO\GetTaskByIdData;
use App\Application\DTO\UpdateTaskData;
use App\Application\Query\GetAllTasksQuery;
use App\Application\Query\GetTaskByIdQuery;
use App\Controller\Api\BaseApiController;
use App\Domain\Exception\InvalidTaskStatusTransitionException;
use App\Domain\Exception\TaskCannotBeDeletedException;
use App\Domain\Exception\TaskNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/tasks')]
class TaskController extends BaseApiController
{
    public function __construct(
        private readonly CreateTaskCommand $createTaskCommand,
        private readonly UpdateTaskCommand $updateTaskCommand,
        private readonly ChangeTaskStatusCommand $changeTaskStatusCommand,
        private readonly DeleteTaskCommand $deleteTaskCommand,
        private readonly GetTaskByIdQuery $getTaskByIdQuery,
        private readonly GetAllTasksQuery $getAllTasksQuery
    ) {
    }

    #[Route('', name: 'api_v1_tasks_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['title']) || trim($data['title']) === '') {
                return $this->errorResponse('Title is required and cannot be empty');
            }
            
            if (mb_strlen($data['title']) > 255) {
                return $this->errorResponse('Title cannot exceed 255 characters');
            }
            
            $createData = new CreateTaskData(
                title: $data['title'],
                description: $data['description'] ?? null
            );
            
            $taskId = $this->createTaskCommand->handle($createData);
            
            // Fetch the created task to return full response
            $getTaskData = new GetTaskByIdData(id: $taskId);
            $task = $this->getTaskByIdQuery->handle($getTaskData);
            
            return $this->createdResponse($task);
            
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    #[Route('', name: 'api_v1_tasks_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $tasks = $this->getAllTasksQuery->handle();
        
        return $this->successResponse($tasks);
    }

    #[Route('/{id}', name: 'api_v1_tasks_show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        try {
            $data = new GetTaskByIdData(id: $id);
            $task = $this->getTaskByIdQuery->handle($data);
            
            return $this->successResponse($task);
            
        } catch (TaskNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        }
    }

    #[Route('/{id}', name: 'api_v1_tasks_update', methods: ['PATCH'])]
    public function update(string $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['title']) || trim($data['title']) === '') {
                return $this->errorResponse('Title is required and cannot be empty');
            }
            
            if (mb_strlen($data['title']) > 255) {
                return $this->errorResponse('Title cannot exceed 255 characters');
            }
            
            $updateData = new UpdateTaskData(
                id: $id,
                title: $data['title'],
                description: $data['description'] ?? null
            );
            
            $this->updateTaskCommand->handle($updateData);
            
            // Fetch updated task
            $getTaskData = new GetTaskByIdData(id: $id);
            $task = $this->getTaskByIdQuery->handle($getTaskData);
            
            return $this->successResponse($task);
            
        } catch (TaskNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    #[Route('/{id}/status', name: 'api_v1_tasks_change_status', methods: ['PATCH'])]
    public function changeStatus(string $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['status'])) {
                return $this->errorResponse('Status is required');
            }
            
            $statusData = new ChangeTaskStatusData(
                id: $id,
                status: $data['status']
            );
            
            $this->changeTaskStatusCommand->handle($statusData);
            
            // Fetch updated task
            $getTaskData = new GetTaskByIdData(id: $id);
            $task = $this->getTaskByIdQuery->handle($getTaskData);
            
            return $this->successResponse($task);
            
        } catch (TaskNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (InvalidTaskStatusTransitionException $e) {
            return $this->errorResponse($e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    #[Route('/{id}', name: 'api_v1_tasks_delete', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        try {
            $deleteData = new DeleteTaskData(id: $id);
            
            $this->deleteTaskCommand->handle($deleteData);
            
            return $this->noContentResponse();
            
        } catch (TaskNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (TaskCannotBeDeletedException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
