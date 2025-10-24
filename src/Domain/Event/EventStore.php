<?php

declare(strict_types=1);

namespace App\Domain\Event;

interface EventStore
{
    public function append(DomainEvent $event): void;

    /**
     * @return DomainEvent[]
     */
    public function getEventsForAggregate(string $aggregateId): array;

    /**
     * @return DomainEvent[]
     */
    public function getAllEvents(): array;
}
