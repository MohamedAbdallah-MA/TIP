<?php

namespace App\DTOs;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;

readonly class CreateTaskDTO
{
    public function __construct(
        public int $userId,
        public string $title,
        public ?string $description,
        public TaskStatus $status,
        public TaskPriority $priority,
    ) {}
}
