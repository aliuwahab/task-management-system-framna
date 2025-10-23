<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Entity\Task;

final readonly class TaskResponse
{
    public function __construct(
        public string $id,
        public string $title,
        public ?string $description,
        public string $status,
        public string $createdAt,
        public string $updatedAt
    ) {
    }

    public static function fromTask(Task $task): self
    {
        return new self(
            id: $task->getId()->getValue(),
            title: $task->getTitle(),
            description: $task->getDescription(),
            status: $task->getStatus()->getValue(),
            createdAt: $task->getCreatedAt()->format('Y-m-d H:i:s'),
            updatedAt: $task->getUpdatedAt()->format('Y-m-d H:i:s')
        );
    }
}
