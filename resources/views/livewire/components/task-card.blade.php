<?php

use App\Enums\TaskPriority;
use App\Models\Task;
use App\Services\TaskService;
use Livewire\Volt\Component;

new class extends Component
{
    public Task $task;

    public function mount(Task $task): void
    {
        $this->task = $task;
    }

    public function edit(): void
    {
        $this->dispatch('open-edit-modal', taskId: $this->task->id);
    }

    public function delete(TaskService $taskService): void
    {
        $taskService->delete($this->task->id, auth()->id());
        $this->dispatch('task-deleted');
    }

    public function getTaskId(): int
    {
        return $this->task->id;
    }

    public function getTaskNumber(): string
    {
        return $this->task->task_number;
    }

    public function getTaskTitle(): string
    {
        return $this->task->title;
    }

    public function getTaskDescription(): string
    {
        return $this->task->description ?? '';
    }

    public function hasDescription(): bool
    {
        return ! empty($this->getTaskDescription());
    }

    public function getTaskStatus(): string
    {
        return $this->task->status->value;
    }

    public function getPriority(): TaskPriority
    {
        return $this->task->priority;
    }

    public function getPriorityBorderClass(): string
    {
        return $this->getPriority()->borderColorClass();
    }

    public function getPriorityLabel(): string
    {
        return $this->getPriority()->label();
    }

    public function getPriorityBadgeClass(): string
    {
        return $this->getPriority()->badgeClass();
    }

    public function getPriorityValue(): string
    {
        return $this->getPriority()->value;
    }
}; ?>

<div
    class="task-card bg-white dark:bg-gray-700 rounded-lg shadow-sm border-2 {{ $this->getPriorityBorderClass() }} dark:border-opacity-75 p-4 cursor-pointer hover:shadow-md transition-shadow duration-200"
    wire:key="task-card-{{ $this->getTaskId() }}"
    data-task-id="{{ $this->getTaskId() }}"
    data-status="{{ $this->getTaskStatus() }}"
    data-priority="{{ $this->getPriorityValue() }}"
    draggable="true"
    x-data="{ isHovered: false }"
    @mouseenter="isHovered = true"
    @mouseleave="isHovered = false"
>
    <!-- Task Number and Priority -->
    <div class="flex items-center justify-between mb-2">
        <div class="flex items-center gap-2">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $this->getTaskNumber() }}</span>
            <span class="priority-badge {{ $this->getPriorityBadgeClass() }}">
                {{ $this->getPriorityLabel() }}
            </span>
        </div>
        <div class="flex items-center gap-1 opacity-0 transition-opacity duration-200" :class="{ 'opacity-100': isHovered }">
            <button
                type="button"
                wire:click="edit"
                class="p-1 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                title="Edit task"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </button>
            <button
                type="button"
                wire:click="delete"
                wire:confirm="Are you sure you want to delete this task?"
                class="p-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors"
                title="Delete task"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Task Title -->
    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2 line-clamp-2">
        {{ $this->getTaskTitle() }}
    </h4>

    <!-- Task Description -->
    @if ($this->hasDescription())
        <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-3 mb-3">
            {{ $this->getTaskDescription() }}
        </p>
    @endif

    <!-- Task Footer -->
    <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-600">
        <span class="text-xs text-gray-500 dark:text-gray-400">
            {{ $task->created_at->diffForHumans() }}
        </span>
    </div>
</div>
