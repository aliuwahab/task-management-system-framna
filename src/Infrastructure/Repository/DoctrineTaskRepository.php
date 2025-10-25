<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Task;
use App\Domain\Repository\TaskFilterCriteria;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Domain\ValueObject\TaskId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineTaskRepository implements TaskRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function save(Task $task): void
    {
        $this->entityManager->persist($task);
        $this->entityManager->flush();
    }

    public function findById(TaskId $id): ?Task
    {
        return $this->entityManager->find(Task::class, $id->getValue());
    }

    public function findAll(?TaskFilterCriteria $criteria = null): array
    {
        if ($criteria === null || !$criteria->hasFilters()) {
            return $this->entityManager->getRepository(Task::class)->findAll();
        }

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('t')
            ->from(Task::class, 't');

        if ($criteria->status !== null) {
            $qb->andWhere('t.statusString = :status')
                ->setParameter('status', $criteria->status);
        }

        // Future filters can be added here automatically:
        // if ($criteria->title !== null) {
        //     $qb->andWhere('t.title LIKE :title')
        //         ->setParameter('title', '%' . $criteria->title . '%');
        // }
        // if ($criteria->createdAfter !== null) {
        //     $qb->andWhere('t.createdAt >= :createdAfter')
        //         ->setParameter('createdAfter', $criteria->createdAfter);
        // }

        return $qb->getQuery()->getResult();
    }

    public function delete(Task $task): void
    {
        $this->entityManager->remove($task);
        $this->entityManager->flush();
    }
}
