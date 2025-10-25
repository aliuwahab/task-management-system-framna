<?php

declare(strict_types=1);

namespace App\Domain\Event;

abstract readonly class AbstractDomainEvent implements DomainEvent
{
    private readonly \DateTimeImmutable $occurredOn;

    public function __construct(
        private readonly string $aggregateId,
    ) {
        $this->occurredOn = new \DateTimeImmutable();
    }

    public function getAggregateId(): string
    {
        return $this->aggregateId;
    }

    public function getOccurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function getEventName(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }
}
