<?php

use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Reactive;
use Livewire\Volt\Component;

new class extends Component
{
    public array $board;

    #[Reactive]
    public Collection $tasks;

    public function mount(array $board, Collection $tasks): void
    {
        $this->board = $board;
        $this->tasks = $tasks;
    }

    public function getBoardId(): string
    {
        return $this->board['id'] ?? '';
    }

    public function getBoardTitle(): string
    {
        return $this->board['title'] ?? '';
    }

    public function getBoardStatus(): string
    {
        return $this->board['status'] ?? '';
    }

    public function getColorClass(): string
    {
        return $this->board['colorClass'] ?? 'bg-gray-500';
    }

    public function getTasksCount(): int
    {
        return $this->tasks->count();
    }

    public function hasTasks(): bool
    {
        return $this->getTasksCount() > 0;
    }
}; ?>

<div
    class="board-container bg-gray-50 dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col h-full min-h-[400px]"
    wire:key="board-{{ $this->getBoardId() }}"
>
    <!-- Board Header -->
    <div class="board-header px-4 py-3 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-full {{ $this->getColorClass() }}"></div>
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $this->getBoardTitle() }}</h3>
                <span class="px-2 py-0.5 text-xs font-medium bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full">
                    {{ $this->getTasksCount() }}
                </span>
            </div>
        </div>
    </div>

    <!-- Board Content -->
    <div class="board-content flex-1 overflow-y-auto px-3 py-4 space-y-3" data-board-id="{{ $this->getBoardId() }}" data-status="{{ $this->getBoardStatus() }}">
        @if ($this->hasTasks())
            @foreach ($tasks as $task)
                <livewire:components.task-card
                    wire:key="task-{{ $task->id }}"
                    :task="$task"
                />
            @endforeach
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No tasks yet</p>
            </div>
        @endif
    </div>
</div>
