<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    /**
     * Mock data for Phase 1 - Frontend only
     * This will be replaced with real data in Phase 2
     */
    public array $boards;

    public array $tasks;

    public string $searchQuery = '';

    public function mount(): void
    {
        $this->initializeBoards();
        $this->initializeTasks();
    }

    protected function initializeBoards(): void
    {
        $this->boards = TaskStatus::allBoards();
    }

    protected function initializeTasks(): void
    {
        $this->tasks = [
            [
                'id' => 1,
                'task_number' => 'TASK-001',
                'title' => 'Design user interface',
                'description' => 'Create wireframes and mockups for the todo application',
                'status' => TaskStatus::TODO->value,
                'priority' => TaskPriority::HIGH->value,
            ],
            [
                'id' => 2,
                'task_number' => 'TASK-002',
                'title' => 'Implement authentication',
                'description' => 'Set up Laravel Breeze with Livewire authentication',
                'status' => TaskStatus::IN_PROGRESS->value,
                'priority' => TaskPriority::MEDIUM->value,
            ],
            [
                'id' => 3,
                'task_number' => 'TASK-003',
                'title' => 'Create database schema',
                'description' => 'Design and implement the tasks table structure',
                'status' => TaskStatus::DONE->value,
                'priority' => TaskPriority::LOW->value,
            ],
            [
                'id' => 4,
                'task_number' => 'TASK-004',
                'title' => 'Write unit tests',
                'description' => 'Create comprehensive test coverage for all features',
                'status' => TaskStatus::TODO->value,
                'priority' => TaskPriority::MEDIUM->value,
            ],
            [
                'id' => 5,
                'task_number' => 'TASK-005',
                'title' => 'Fix critical bug',
                'description' => 'Resolve the issue with task deletion',
                'status' => TaskStatus::CANCELLED->value,
                'priority' => TaskPriority::HIGH->value,
            ],
        ];
    }

    public bool $showModal = false;

    public ?array $editingTask = null;

    public function openCreateModal(): void
    {
        $this->editingTask = null;
        $this->showModal = true;
    }

    public function openEditModal(int $taskId): void
    {
        $task = collect($this->tasks)->firstWhere('id', $taskId);
        $this->editingTask = $task;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingTask = null;
    }

    public function getTasksForBoard(string $status): array
    {
        $tasks = collect($this->tasks)
            ->where('status', $status);

        if (! empty($this->searchQuery)) {
            $tasks = $tasks->filter(function ($task) {
                return $this->matchesSearchQuery($task);
            });
        }

        return $tasks->values()->toArray();
    }

    protected function matchesSearchQuery(array $task): bool
    {
        $query = strtolower(trim($this->searchQuery));

        if (empty($query)) {
            return true;
        }

        $taskNumber = strtolower($task['task_number'] ?? '');
        $title = strtolower($task['title'] ?? '');
        $description = strtolower($task['description'] ?? '');

        return str_contains($taskNumber, $query)
            || str_contains($title, $query)
            || str_contains($description, $query);
    }

    public function updatedSearchQuery(): void
    {
        $this->dispatch('search-updated', query: $this->searchQuery);
    }

    protected function getListeners(): array
    {
        return [
            'open-edit-modal' => 'openEditModal',
            'modal-closed' => 'closeModal',
            'search-query-changed' => 'handleSearchQueryChanged',
        ];
    }

    public function handleSearchQueryChanged(string $query): void
    {
        $this->searchQuery = $query;
    }
}; ?>

<div class="todos-container" wire:key="todos-main">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">My Tasks</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Manage your tasks across different boards
                    </p>
                </div>
                <button
                    type="button"
                    wire:click="openCreateModal"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Task
                </button>
            </div>
        </div>

        <!-- Kanban Board -->
        <div class="boards-container">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($boards as $board)
                    <livewire:components.board
                        wire:key="board-{{ $board['id'] }}"
                        :board="$board"
                        :tasks="$this->getTasksForBoard($board['status'])"
                    />
                @endforeach
            </div>
        </div>
    </div>

    <!-- Task Modal -->
    @if ($showModal)
        <livewire:components.task-modal
            wire:key="task-modal"
            :task="$editingTask"
            :boards="$boards"
            :show="$showModal"
        />
    @endif
</div>
