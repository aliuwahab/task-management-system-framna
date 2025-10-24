<?php

declare(strict_types=1);

namespace App\Domain\Event;

final readonly class TaskCreatedEvent extends AbstractDomainEvent
{
    public function __construct(
        string $aggregateId,
        private string $title,
        private ?string $description,
        private string $status,
    ) {
        parent::__construct($aggregateId);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function toArray(): array
    {
        return [
            'aggregate_id' => $this->getAggregateId(),
            'event_name' => $this->getEventName(),
            'occurred_on' => $this->getOccurredOn()->format(\DateTimeInterface::ATOM),
            'payload' => [
                'title' => $this->title,
                'description' => $this->description,
                'status' => $this->status,
            ],
        ];
    }
}
