<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\TaskId;
use App\Domain\ValueObject\TaskStatus;

class Task
{
    private TaskId $id;
    private string $title;
    private ?string $description;
    private TaskStatus $status;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    private function __construct(
        TaskId $id,
        string $title,
        ?string $description,
        TaskStatus $status,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt
    ) {
        $this->id = $id;
        $this->setTitle($title);
        $this->description = $description;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function create(TaskId $id, string $title, ?string $description): self
    {
        $now = new \DateTimeImmutable();

        return new self(
            $id,
            $title,
            $description,
            TaskStatus::todo(),
            $now,
            $now
        );
    }

    public function getId(): TaskId
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getStatus(): TaskStatus
    {
        return $this->status;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function setTitle(string $title): void
    {
        $trimmedTitle = trim($title);
        
        if ($trimmedTitle === '') {
            throw new \InvalidArgumentException('Task title cannot be empty');
        }

        if (mb_strlen($trimmedTitle) > 255) {
            throw new \InvalidArgumentException('Task title cannot exceed 255 characters');
        }

        $this->title = $trimmedTitle;
    }
}
