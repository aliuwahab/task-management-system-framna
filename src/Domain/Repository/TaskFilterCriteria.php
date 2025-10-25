<?php

declare(strict_types=1);

namespace App\Domain\Repository;

/**
 * Flexible filter criteria for Task queries.
 * Add new filter fields as needed - repository will automatically apply them.
 */
final readonly class TaskFilterCriteria
{
    public function __construct(
        public ?string $status = null,
        // Future filters can be added here:
        // public ?string $title = null,
        // public ?\DateTimeImmutable $createdAfter = null,
        // public ?\DateTimeImmutable $createdBefore = null,
    ) {
    }

    public function hasFilters(): bool
    {
        return $this->status !== null;
        // Add more conditions as filters are added
    }
}
