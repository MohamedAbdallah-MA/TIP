<?php

namespace App\Services;

use App\DTOs\CreateTaskDTO;
use App\DTOs\UpdateTaskDTO;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Repositories\TaskRepository;
use Illuminate\Database\Eloquent\Collection;

class TaskService
{
    public function __construct(
        private TaskRepository $repository
    ) {}

    public function create(CreateTaskDTO $dto): Task
    {
        return $this->repository->create([
            'user_id' => $dto->userId,
            'title' => $dto->title,
            'description' => $dto->description,
            'status' => $dto->status,
            'priority' => $dto->priority,
        ]);
    }

    public function update(int $taskId, int $userId, UpdateTaskDTO $dto): Task
    {
        $task = $this->repository->findByIdAndUser($taskId, $userId);

        if (! $task) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Task not found');
        }

        $updateData = [];

        if ($dto->title !== null) {
            $updateData['title'] = $dto->title;
        }

        if ($dto->description !== null) {
            $updateData['description'] = $dto->description;
        }

        if ($dto->status !== null) {
            $updateData['status'] = $dto->status;
        }

        if ($dto->priority !== null) {
            $updateData['priority'] = $dto->priority;
        }

        $this->repository->update($task, $updateData);

        return $task->fresh();
    }

    public function delete(int $taskId, int $userId): bool
    {
        $task = $this->repository->findByIdAndUser($taskId, $userId);

        if (! $task) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Task not found');
        }

        return $this->repository->delete($task);
    }

    public function getAllByUser(int $userId): Collection
    {
        return $this->repository->getAllByUser($userId);
    }

    public function getByStatus(int $userId, TaskStatus $status): Collection
    {
        return $this->repository->getByStatus($userId, $status);
    }

    public function search(int $userId, string $query): Collection
    {
        return $this->repository->search($userId, $query);
    }

    public function updateStatus(int $taskId, int $userId, TaskStatus $status): Task
    {
        $task = $this->repository->findByIdAndUser($taskId, $userId);

        if (! $task) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Task not found');
        }

        $this->repository->updateStatus($task, $status);

        return $task->fresh();
    }

    public function updatePriority(int $taskId, int $userId, TaskPriority $priority): Task
    {
        $task = $this->repository->findByIdAndUser($taskId, $userId);

        if (! $task) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Task not found');
        }

        $this->repository->updatePriority($task, $priority);

        return $task->fresh();
    }

    public function getByIdAndUserId(int $taskId, int $userId): Task
    {
        return $this->repository->findByIdAndUser($taskId, $userId);
    }
}
