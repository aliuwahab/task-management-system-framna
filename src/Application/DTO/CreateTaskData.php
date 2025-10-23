<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class CreateTaskData
{
    public function __construct(
        public string $title,
        public ?string $description = null
    ) {
    }
}
