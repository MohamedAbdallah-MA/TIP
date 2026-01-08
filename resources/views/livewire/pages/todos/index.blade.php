<?php

use App\DTOs\CreateTaskDTO;
use App\DTOs\UpdateTaskDTO;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    // Task List State
    public array $boards = [];
    public \Illuminate\Database\Eloquent\Collection $tasks;
    public string $searchQuery = '';

    // Modal Form State
    public bool $showModal = false;
    public ?Task $editingTask = null;
    
    public string $formTitle = '';
    public string $formDescription = '';
    public string $formStatus = '';
    public string $formPriority = '';

    public function mount(TaskService $taskService): void
    {
        $this->initializeBoards();
        $this->loadTasks($taskService);
    }

    protected function initializeBoards(): void
    {
        $this->boards = TaskStatus::allBoards();
    }

    protected function loadTasks(TaskService $taskService): void
    {
        $userId = auth()->id();
        
        $this->tasks = $taskService->search(
            $userId,
            $this->searchQuery
        );
    }

    public function openCreateModal(): void
    {
        $this->editingTask = null;
        $this->reset(['formTitle', 'formDescription']);
        $this->formStatus = TaskStatus::TODO->value;
        $this->formPriority = TaskPriority::MEDIUM->value;
        $this->showModal = true;
    }

    public function openEditModal(int $taskId, TaskService $taskService): void
    {
        try {
            $this->editingTask = $taskService->getByIdAndUserId($taskId, auth()->id());
            
            $this->formTitle = $this->editingTask->title;
            $this->formDescription = $this->editingTask->description ?? '';
            $this->formStatus = $this->editingTask->status->value;
            $this->formPriority = $this->editingTask->priority->value;
            
            $this->showModal = true;
        } catch (ModelNotFoundException $e) {
            $this->dispatch('notify', message: 'Task not found or access denied.', type: 'error');
            $this->loadTasks($taskService);
        }
    }

    public function saveTask(TaskService $taskService): void
    {
        $this->validate([
            'formTitle' => ['required', 'string', 'max:255'],
            'formDescription' => ['nullable', 'string', 'max:5000'],
            'formStatus' => ['required', 'string'],
            'formPriority' => ['required', 'string'],
        ]);

        $userId = auth()->id();

        try {
            if ($this->editingTask) {
                $dto = new UpdateTaskDTO(
                    title: $this->formTitle,
                    description: $this->formDescription ?: null,
                    status: TaskStatus::from($this->formStatus),
                    priority: TaskPriority::from($this->formPriority),
                );

                $taskService->update($this->editingTask->id, $userId, $dto);
            } else {
                $dto = new CreateTaskDTO(
                    userId: $userId,
                    title: $this->formTitle,
                    description: $this->formDescription ?: null,
                    status: TaskStatus::from($this->formStatus),
                    priority: TaskPriority::from($this->formPriority),
                );

                $taskService->create($dto);
            }

            $this->showModal = false;
            $this->reset(['editingTask', 'formTitle', 'formDescription', 'formStatus', 'formPriority']);
            $this->loadTasks($taskService);
        } catch (ModelNotFoundException $e) {
             $this->dispatch('notify', message: 'Task not found or access denied.', type: 'error');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to save task: ' . $e->getMessage());
            $this->dispatch('notify', message: 'An error occurred while saving the task.', type: 'error');
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['editingTask', 'formTitle', 'formDescription', 'formStatus', 'formPriority']);
    }

    public function deleteTask(int $taskId, TaskService $taskService): void
    {
        try {
            $taskService->delete($taskId, auth()->id());
            $this->loadTasks($taskService);
        } catch (ModelNotFoundException $e) {
            $this->dispatch('notify', message: 'Task not found or access denied.', type: 'error');
            $this->loadTasks($taskService); // Refresh in case it was already deleted
        } catch (\Throwable $e) {
             \Illuminate\Support\Facades\Log::error('Failed to delete task: ' . $e->getMessage());
             $this->dispatch('notify', message: 'An error occurred while deleting the task.', type: 'error');
        }
    }

    protected function getListeners(): array
    {
        return [
            'open-create-modal' => 'openCreateModal',
            'search-query-changed' => 'handleSearchQueryChanged',
            'task-moved' => 'moveTask',
        ];
    }

    public function handleSearchQueryChanged(string $query): void
    {
        $this->searchQuery = $query;
        $this->loadTasks(app(TaskService::class));
    }

    public function moveTask(?int $taskId, string $status, TaskService $taskService): void
    {
        if (!$taskId) {
            \Illuminate\Support\Facades\Log::warning('Task moved event received without valid Task ID.');
            return;
        }

        try {
            $taskService->updateStatus(
                $taskId,
                auth()->id(),
                TaskStatus::from($status)
            );
            
            $this->loadTasks($taskService);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to move task: ' . $e->getMessage());
        }
    }

    public function getPriorities(): array
    {
        return TaskPriority::options();
    }

    public function getTasksForBoard(string $status): \Illuminate\Database\Eloquent\Collection
    {
        return $this->tasks->filter(function ($task) use ($status) {
            return $task->status->value === $status;
        });
    }
}; ?>

<div class="todos-container" wire:key="todos-main">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400">
                    Dashboard
                </h1>
                <p class="mt-1 text-gray-500 dark:text-gray-400">Manage your tasks and projects</p>
            </div>
            
            <button 
                wire:click="openCreateModal" 
                class="group w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold rounded-xl shadow-lg hover:shadow-blue-500/25 transform hover:scale-105 transition-all duration-200 flex items-center justify-center gap-2"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 transition-transform group-hover:rotate-180 duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>New Task</span>
                </div>
            </button>
        </div>

        @if ($tasks->isEmpty() && empty($searchQuery))
            <div class="text-center py-16 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-50 dark:bg-blue-900/30 mb-4">
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">No tasks yet</h3>
                <p class="mt-1 text-gray-500 dark:text-gray-400">Get started by creating your first task.</p>
                <div class="mt-6">
                    <button
                        wire:click="openCreateModal"
                        class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium"
                    >
                        Create a task &rarr;
                    </button>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 overflow-x-auto pb-6">
                @foreach ($boards as $board)
                    <x-kanban.board 
                        :board="$board" 
                        :tasks-count="$this->getTasksForBoard($board['status'])->count()"
                    >
                        @forelse ($this->getTasksForBoard($board['status']) as $task)
                            <x-kanban.card :task="$task" />
                        @empty
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No tasks yet</p>
                            </div>
                        @endforelse
                    </x-kanban.board>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Inline Modal --}}
    @if ($showModal)
    <div
        class="fixed inset-0 z-50 overflow-y-auto"
        x-data="{ show: @entangle('showModal') }"
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        wire:key="task-modal-inline"
    >
        <!-- Backdrop -->
        <div
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
            @click="show = false"
        ></div>

        <!-- Modal -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div
                class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full transform transition-all"
                @click.away="show = false"
            >
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ $editingTask ? 'Edit Task' : 'Create New Task' }}
                        </h3>
                        <button
                            type="button"
                            @click="show = false"
                            class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <form wire:submit="saveTask" class="px-6 py-4">
                    <!-- Title -->
                    <div class="mb-4">
                        <label for="formTitle" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="formTitle"
                            wire:model="formTitle"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                            placeholder="Enter task title"
                            required
                        />
                        @error('formTitle')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="formDescription" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Description
                        </label>
                        <textarea
                            id="formDescription"
                            wire:model="formDescription"
                            rows="4"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                            placeholder="Enter task description"
                        ></textarea>
                        @error('formDescription')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="mb-4">
                        <label for="formStatus" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Status
                        </label>
                        <select
                            id="formStatus"
                            wire:model="formStatus"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                        >
                            @foreach ($boards as $board)
                                <option value="{{ $board['status'] }}">{{ $board['title'] }}</option>
                            @endforeach
                        </select>
                        @error('formStatus')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Priority -->
                    <div class="mb-6">
                        <label for="formPriority" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Priority
                        </label>
                        <select
                            id="formPriority"
                            wire:model="formPriority"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                        >
                            @foreach ($this->getPriorities() as $priorityOption)
                                <option value="{{ $priorityOption['value'] }}">{{ $priorityOption['label'] }}</option>
                            @endforeach
                        </select>
                        @error('formPriority')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button
                            type="button"
                            @click="show = false"
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
</div>
