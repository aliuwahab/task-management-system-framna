<?php

declare(strict_types=1);

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseApiController extends AbstractController
{
    protected function successResponse(
        mixed $data = null,
        int $statusCode = Response::HTTP_OK
    ): JsonResponse {
        return $this->json($data, $statusCode);
    }

    protected function createdResponse(mixed $data): JsonResponse
    {
        return $this->json($data, Response::HTTP_CREATED);
    }

    protected function noContentResponse(): JsonResponse
    {
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    protected function errorResponse(
        string $message,
        int $statusCode = Response::HTTP_BAD_REQUEST
    ): JsonResponse {
        return $this->json([
            'error' => $message
        ], $statusCode);
    }

    protected function validationErrorResponse(array $errors): JsonResponse
    {
        return $this->json([
            'error' => 'Validation failed',
            'details' => $errors
        ], Response::HTTP_BAD_REQUEST);
    }

    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->json([
            'error' => $message
        ], Response::HTTP_NOT_FOUND);
    }
}
