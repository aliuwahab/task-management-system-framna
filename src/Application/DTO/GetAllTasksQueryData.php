<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class GetAllTasksQueryData
{
    public function __construct(
        public ?string $status = null
    ) {
    }
}
