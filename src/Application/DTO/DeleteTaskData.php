<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class DeleteTaskData
{
    public function __construct(
        public string $id
    ) {
    }
}
