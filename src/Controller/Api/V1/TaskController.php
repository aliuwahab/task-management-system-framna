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
use App\Application\DTO\TaskResponse;
use App\Application\DTO\UpdateTaskData;
use App\Application\Query\GetAllTasksQuery;
use App\Application\Query\GetTaskByIdQuery;
use App\Domain\Repository\TaskFilterCriteria;
use App\Controller\Api\BaseApiController;
use App\Domain\Exception\InvalidTaskStatusTransitionException;
use App\Domain\Exception\TaskCannotBeDeletedException;
use App\Domain\Exception\TaskNotFoundException;
use App\Http\Request\Api\V1\ChangeTaskStatusRequest;
use App\Http\Request\Api\V1\CreateTaskRequest;
use App\Http\Request\Api\V1\UpdateTaskRequest;
use InvalidArgumentException;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/tasks')]
#[OA\Tag(name: 'Tasks')]
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
    #[OA\Post(
        summary: 'Create a new task',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: CreateTaskRequest::class))
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Task created successfully',
                content: new OA\JsonContent(ref: new Model(type: TaskResponse::class))
            ),
            new OA\Response(response: 422, description: 'Validation error')
        ]
    )]
    public function create(
        #[MapRequestPayload] CreateTaskRequest $request
    ): JsonResponse {
        try {
            $createData = new CreateTaskData(
                title: $request->title,
                description: $request->description
            );

            $taskId = $this->createTaskCommand->handle($createData);

            // Fetch the created task to return a full response
            $getTaskData = new GetTaskByIdData(id: $taskId);
            $task = $this->getTaskByIdQuery->handle($getTaskData);

            return $this->createdResponse($task);

        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    #[Route('', name: 'api_v1_tasks_list', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get all tasks',
        parameters: [
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: 'Filter tasks by status (todo, in_progress, done)',
                schema: new OA\Schema(type: 'string', enum: ['todo', 'in_progress', 'done'])
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of all tasks',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: TaskResponse::class))
                )
            )
        ]
    )]
    public function list(Request $request): JsonResponse
    {
        // Build filter criteria from query parameters
        $status = $request->query->get('status');

        $criteria = null;
        if ($status !== null) {
            $criteria = new TaskFilterCriteria(status: $status);
        }

        $tasks = $this->getAllTasksQuery->handle($criteria);

        return $this->successResponse($tasks);
    }

    #[Route('/{id}', name: 'api_v1_tasks_show', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get a single task by ID',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Task found',
                content: new OA\JsonContent(ref: new Model(type: TaskResponse::class))
            ),
            new OA\Response(response: 404, description: 'Task not found')
        ]
    )]
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
        } catch (InvalidTaskStatusTransitionException|InvalidArgumentException $e) {
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
