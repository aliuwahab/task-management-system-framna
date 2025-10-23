<?php

declare(strict_types=1);

namespace App\Http\Request\Api\V1;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ChangeTaskStatusRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Status is required')]
        #[Assert\Choice(
            choices: ['todo', 'in_progress', 'done'],
            message: 'Status must be one of: todo, in_progress, done'
        )]
        public string $status
    ) {
    }
}
