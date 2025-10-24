<?php

declare(strict_types=1);

namespace App\Infrastructure\Event;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'stored_events')]
#[ORM\Index(columns: ['aggregate_id'], name: 'idx_aggregate_id')]
#[ORM\Index(columns: ['event_name'], name: 'idx_event_name')]
class StoredEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 36)]
    private string $aggregateId;

    #[ORM\Column(type: 'string', length: 255)]
    private string $eventName;

    #[ORM\Column(type: 'json')]
    private array $payload;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $occurredOn;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $storedOn;

    public function __construct(
        string $aggregateId,
        string $eventName,
        array $payload,
        \DateTimeImmutable $occurredOn
    ) {
        $this->aggregateId = $aggregateId;
        $this->eventName = $eventName;
        $this->payload = $payload;
        $this->occurredOn = $occurredOn;
        $this->storedOn = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAggregateId(): string
    {
        return $this->aggregateId;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getOccurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function getStoredOn(): \DateTimeImmutable
    {
        return $this->storedOn;
    }
}
