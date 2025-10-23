<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Symfony\Component\Uid\Uuid;

final readonly class TaskId
{
    private function __construct(
        private string $value
    ) {
    }

    public static function generate(): self
    {
        return new self(Uuid::v4()->toRfc4122());
    }

    public static function fromString(string $value): self
    {
        if (!Uuid::isValid($value)) {
            throw new \InvalidArgumentException('Invalid UUID format');
        }

        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(TaskId $other): bool
    {
        return $this->value === $other->value;
    }
}
