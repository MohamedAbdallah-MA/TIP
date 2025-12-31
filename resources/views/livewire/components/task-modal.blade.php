<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Livewire\Volt\Component;

new class extends Component
{
    public ?array $task = null;

    public array $boards = [];

    public bool $show = false;

    public string $title = '';

    public string $description = '';

    public string $status = '';

    public string $priority = '';

    public function mount(?array $task = null, array $boards = [], bool $show = false): void
    {
        $this->task = $task;
        $this->boards = $boards;
        $this->show = $show;

        if ($task) {
            $this->title = $task['title'] ?? '';
            $this->description = $task['description'] ?? '';
            $this->status = $task['status'] ?? TaskStatus::TODO->value;
            $this->priority = $task['priority'] ?? TaskPriority::MEDIUM->value;
        } else {
            $this->status = TaskStatus::TODO->value;
            $this->priority = TaskPriority::MEDIUM->value;
        }
    }

    public function updatedShow(): void
    {
        if (! $this->show) {
            $this->reset(['title', 'description', 'status', 'priority', 'task']);
        }
    }

    public function save(): void
    {
        // Placeholder for Phase 2 - will be handled by service layer
        $this->dispatch('close-modal');
    }

    public function close(): void
    {
        $this->show = false;
        $this->dispatch('modal-closed');
    }

    public function getPriorities(): array
    {
        return TaskPriority::options();
    }

    protected function getListeners(): array
    {
        return [
            'close-modal' => 'close',
        ];
    }
}; ?>

@if ($show)
<div
    class="fixed inset-0 z-50 overflow-y-auto"
    x-data="{ show: true }"
    x-show="show"
    x-init="$watch('$wire.show', value => show = value)"
    x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    wire:key="task-modal"
>
    <!-- Backdrop -->
    <div
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
        wire:click="close"
    ></div>

    <!-- Modal -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div
            class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full transform transition-all"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            @click.away="close()"
        >
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ $task ? 'Edit Task' : 'Create New Task' }}
                    </h3>
                    <button
                        type="button"
                        wire:click="close"
                        class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <form wire:submit="save" class="px-6 py-4">
                <!-- Title -->
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="title"
                        wire:model="title"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                        placeholder="Enter task title"
                        required
                    />
                    @error('title')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Description
                    </label>
                    <textarea
                        id="description"
                        wire:model="description"
                        rows="4"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                        placeholder="Enter task description"
                    ></textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="mb-4">
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Status
                    </label>
                    <select
                        id="status"
                        wire:model="status"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                    >
                        @foreach ($boards as $board)
                            <option value="{{ $board['status'] }}">{{ $board['title'] }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Priority -->
                <div class="mb-6">
                    <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Priority
                    </label>
                    <select
                        id="priority"
                        wire:model="priority"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                    >
                        @foreach ($this->getPriorities() as $priorityOption)
                            <option value="{{ $priorityOption['value'] }}">{{ $priorityOption['label'] }}</option>
                        @endforeach
                    </select>
                    @error('priority')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button
                        type="button"
                        wire:click="close"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove>Save Task</span>
                        <span wire:loading>Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
