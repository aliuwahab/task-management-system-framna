<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Exception\InvalidTaskStatusTransitionException;
use App\Domain\Exception\TaskCannotBeDeletedException;
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
    private bool $deleted = false;

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

    public function update(string $title, ?string $description): void
    {
        $this->setTitle($title);
        $this->description = $description;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function changeStatus(TaskStatus $newStatus): void
    {
        // Business rule: A task can only be marked as "done" if it was previously in_progress
        if ($newStatus->isDone() && !$this->status->isInProgress()) {
            throw new InvalidTaskStatusTransitionException(
                sprintf(
                    'Cannot change task status from %s to done. Task must be in_progress first.',
                    $this->status->getValue()
                )
            );
        }

        $this->status = $newStatus;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function delete(): void
    {
        // Business rule: A task cannot be deleted if its status is done
        if ($this->status->isDone()) {
            throw new TaskCannotBeDeletedException(
                sprintf('Cannot delete a task with status: %s', $this->status->getValue())
            );
        }

        $this->deleted = true;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
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
