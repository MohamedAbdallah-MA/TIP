@props(['board', 'tasksCount'])

<div
    class="board-container bg-gray-50 dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col h-full min-h-[400px]"
    wire:key="board-{{ $board['id'] }}"
>
    <!-- Board Header -->
    <div class="board-header px-4 py-3 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-full {{ $board['colorClass'] ?? 'bg-gray-500' }}"></div>
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $board['title'] }}</h3>
                <span class="px-2 py-0.5 text-xs font-medium bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full">
                    {{ $tasksCount }}
                </span>
            </div>
        </div>
    </div>

    <!-- Board Content -->
    <div 
        class="board-content flex-1 overflow-y-auto px-3 py-4 space-y-3" 
        data-board-id="{{ $board['id'] }}" 
        data-status="{{ $board['status'] }}"
    >
        {{ $slot }}
    </div>
</div>
