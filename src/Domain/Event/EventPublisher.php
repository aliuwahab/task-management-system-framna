<?php

declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\Entity\Task;

interface EventPublisher
{
    public function publishEventsFrom(Task $task): void;
}
