<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class ChangeTaskStatusData
{
    public function __construct(
        public string $id,
        public string $status
    ) {
    }
}
