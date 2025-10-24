<?php

declare(strict_types=1);

namespace App\Domain\Event;

interface DomainEvent
{
    public function getAggregateId(): string;

    public function getOccurredOn(): \DateTimeImmutable;

    public function getEventName(): string;

    public function toArray(): array;
}
