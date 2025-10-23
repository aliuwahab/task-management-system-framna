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
use App\Http\Request\Api\V1\ChangeTaskStatusRequest;
use App\Http\Request\Api\V1\CreateTaskRequest;
use App\Http\Request\Api\V1\UpdateTaskRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
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
    public function create(
        #[MapRequestPayload] CreateTaskRequest $request
    ): JsonResponse {
        try {
            $createData = new CreateTaskData(
                title: $request->title,
                description: $request->description
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
    public function update(
        string $id,
        #[MapRequestPayload] UpdateTaskRequest $request
    ): JsonResponse {
        try {
            $updateData = new UpdateTaskData(
                id: $id,
                title: $request->title,
                description: $request->description
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
    public function changeStatus(
        string $id,
        #[MapRequestPayload] ChangeTaskStatusRequest $request
    ): JsonResponse {
        try {
            $statusData = new ChangeTaskStatusData(
                id: $id,
                status: $request->status
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
