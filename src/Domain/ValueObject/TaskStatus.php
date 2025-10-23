<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final readonly class TaskStatus
{
    public const TODO = 'todo';
    public const IN_PROGRESS = 'in_progress';
    public const DONE = 'done';

    private const VALID_STATUSES = [
        self::TODO,
        self::IN_PROGRESS,
        self::DONE,
    ];

    private function __construct(
        private string $value
    ) {
    }

    public static function todo(): self
    {
        return new self(self::TODO);
    }

    public static function inProgress(): self
    {
        return new self(self::IN_PROGRESS);
    }

    public static function done(): self
    {
        return new self(self::DONE);
    }

    public static function fromString(string $value): self
    {
        if (!in_array($value, self::VALID_STATUSES, true)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid task status: %s. Valid statuses are: %s', $value, implode(', ', self::VALID_STATUSES))
            );
        }

        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isTodo(): bool
    {
        return $this->value === self::TODO;
    }

    public function isInProgress(): bool
    {
        return $this->value === self::IN_PROGRESS;
    }

    public function isDone(): bool
    {
        return $this->value === self::DONE;
    }

    public function equals(TaskStatus $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
