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

    public static function fromRequest($request): self
    {
        return new self(
            userId: $request->user()->id,
            title: $request->get('title'),
            description: $request->get('description'),
            status: TaskStatus::tryFrom($request->get('status')),
            priority: TaskPriority::tryFrom($request->get('priority')),
        );
    }
}
