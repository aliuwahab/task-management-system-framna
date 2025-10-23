<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class GetTaskByIdData
{
    public function __construct(
        public string $id
    ) {
    }
}
