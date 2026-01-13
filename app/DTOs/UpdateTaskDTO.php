<?php

namespace App\DTOs;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;

readonly class UpdateTaskDTO
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?TaskStatus $status = null,
        public ?TaskPriority $priority = null,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            title: $request->get('title'),
            description: $request->get('description'),
            status: TaskStatus::tryFrom($request->get('status')),
            priority:TaskPriority::tryFrom($request->get('priority')),
        );
    }
}
