<?php

declare(strict_types=1);

namespace App\Infrastructure\Event;

use App\Domain\Event\DomainEvent;
use App\Domain\Event\EventStore;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineEventStore implements EventStore
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function append(DomainEvent $event): void
    {
        $eventData = $event->toArray();

        $storedEvent = new StoredEvent(
            $event->getAggregateId(),
            $event->getEventName(),
            $eventData['payload'] ?? [],
            $event->getOccurredOn()
        );

        $this->entityManager->persist($storedEvent);
        $this->entityManager->flush();
    }

    public function getEventsForAggregate(string $aggregateId): array
    {
        $storedEvents = $this->entityManager
            ->getRepository(StoredEvent::class)
            ->findBy(
                ['aggregateId' => $aggregateId],
                ['occurredOn' => 'ASC']
            );

        return $storedEvents;
    }

    public function getAllEvents(): array
    {
        return $this->entityManager
            ->getRepository(StoredEvent::class)
            ->findBy([], ['occurredOn' => 'ASC']);
    }
}
