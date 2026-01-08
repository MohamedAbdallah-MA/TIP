<?php

namespace App\Repositories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository
{
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(Task $task, array $data): bool
    {
        return $task->update($data);
    }

    public function delete(Task $task): bool
    {
        return $task->delete();
    }

    public function findById(int $id): ?Task
    {
        return Task::find($id);
    }

    public function findByIdAndUser(int $id, int $userId): ?Task
    {
        return Task::where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function getAllByUser(int $userId): Collection
    {
        return Task::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByStatus(int $userId, TaskStatus $status): Collection
    {
        return Task::where('user_id', $userId)
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByPriority(int $userId, TaskPriority $priority): Collection
    {
        return Task::where('user_id', $userId)
            ->where('priority', $priority)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function search(int $userId, string $query): Collection
    {
        return Task::where('user_id', $userId)
            ->where(function ($q) use ($query) {
                $q->where('task_number', 'like', "%{$query}%")
                    ->orWhere('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function updateStatus(Task $task, TaskStatus $status): bool
    {
        return $task->update(['status' => $status]);
    }

    public function updatePriority(Task $task, TaskPriority $priority): bool
    {
        return $task->update(['priority' => $priority]);
    }
}
