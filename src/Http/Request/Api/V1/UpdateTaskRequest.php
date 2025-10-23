<?php

declare(strict_types=1);

namespace App\Http\Request\Api\V1;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateTaskRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Title is required and cannot be empty')]
        #[Assert\Length(
            max: 255,
            maxMessage: 'Title cannot exceed {{ limit }} characters'
        )]
        public string $title,

        public ?string $description = null
    ) {
    }
}
