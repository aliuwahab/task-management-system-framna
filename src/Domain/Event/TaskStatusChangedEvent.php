<?php

declare(strict_types=1);

namespace App\Domain\Event;

final readonly class TaskStatusChangedEvent extends AbstractDomainEvent
{
    public function __construct(
        string $aggregateId,
        private string $oldStatus,
        private string $newStatus,
    ) {
        parent::__construct($aggregateId);
    }

    public function getOldStatus(): string
    {
        return $this->oldStatus;
    }

    public function getNewStatus(): string
    {
        return $this->newStatus;
    }

    public function toArray(): array
    {
        return [
            'aggregate_id' => $this->getAggregateId(),
            'event_name' => $this->getEventName(),
            'occurred_on' => $this->getOccurredOn()->format(\DateTimeInterface::ATOM),
            'payload' => [
                'old_status' => $this->oldStatus,
                'new_status' => $this->newStatus,
            ],
        ];
    }
}
