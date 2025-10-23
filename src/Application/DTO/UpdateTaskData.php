<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class UpdateTaskData
{
    public function __construct(
        public string $id,
        public string $title,
        public ?string $description = null
    ) {
    }
}
