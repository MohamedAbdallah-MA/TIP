<?php

use Livewire\Volt\Component;

new class extends Component {
    public array $board;
    public array $tasks = [];

    public function mount(array $board, array $tasks = []): void
    {
        $this->board = $board;
        $this->tasks = $tasks;
    }
}; ?>

<div
    class="board-container bg-gray-50 dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col h-full min-h-[400px]"
    wire:key="board-{{ $board['id'] }}"
>
    <!-- Board Header -->
    <div class="board-header px-4 py-3 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                @php
                    $colorClasses = [
                        'blue' => 'bg-blue-500',
                        'yellow' => 'bg-yellow-500',
                        'red' => 'bg-red-500',
                        'green' => 'bg-green-500',
                    ];
                    $colorClass = $colorClasses[$board['color']] ?? 'bg-gray-500';
                @endphp
                <div class="w-3 h-3 rounded-full {{ $colorClass }}"></div>
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $board['title'] }}</h3>
                <span class="px-2 py-0.5 text-xs font-medium bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full">
                    {{ count($tasks) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Board Content -->
    <div class="board-content flex-1 overflow-y-auto px-3 py-4 space-y-3" data-board-id="{{ $board['id'] }}" data-status="{{ $board['status'] }}">
        @forelse ($tasks as $task)
            <livewire:components.task-card
                wire:key="task-{{ $task['id'] }}"
                :task="$task"
            />
        @empty
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No tasks yet</p>
            </div>
        @endforelse
    </div>
</div>
