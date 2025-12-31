/**
 * Todo Application JavaScript
 * Phase 1: Frontend-only interactions
 *
 * This file handles:
 * - Drag and drop readiness (architecture for Phase 2)
 * - Modal interactions
 * - Board interactions
 * - Task card interactions
 */

document.addEventListener('DOMContentLoaded', function () {
    initializeTodos();
});

/**
 * Initialize todo application interactions
 */
function initializeTodos() {
    initializeDragAndDrop();
    initializeModals();
    initializeTaskCards();
}

/**
 * Initialize drag and drop functionality
 * Architecture-ready for Phase 2 backend integration
 */
function initializeDragAndDrop() {
    const taskCards = document.querySelectorAll('[data-task-id]');
    const boards = document.querySelectorAll('[data-board-id]');

    taskCards.forEach(card => {
        card.addEventListener('dragstart', handleDragStart);
        card.addEventListener('dragend', handleDragEnd);
    });

    boards.forEach(board => {
        board.addEventListener('dragover', handleDragOver);
        board.addEventListener('drop', handleDrop);
        board.addEventListener('dragenter', handleDragEnter);
        board.addEventListener('dragleave', handleDragLeave);
    });
}

/**
 * Handle drag start event
 */
function handleDragStart(e) {
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', e.target.outerHTML);
    e.dataTransfer.setData('text/plain', e.target.dataset.taskId);
    e.target.classList.add('opacity-50', 'dragging');
}

/**
 * Handle drag end event
 */
function handleDragEnd(e) {
    e.target.classList.remove('opacity-50', 'dragging');

    // Remove drag-over styling from all boards
    document.querySelectorAll('[data-board-id]').forEach(board => {
        board.classList.remove('drag-over');
    });
}

/**
 * Handle drag over event
 */
function handleDragOver(e) {
    if (e.preventDefault) {
        e.preventDefault();
    }
    e.dataTransfer.dropEffect = 'move';
    return false;
}

/**
 * Handle drag enter event
 */
function handleDragEnter(e) {
    e.currentTarget.classList.add('drag-over');
}

/**
 * Handle drag leave event
 */
function handleDragLeave(e) {
    e.currentTarget.classList.remove('drag-over');
}

/**
 * Handle drop event
 * Placeholder for Phase 2 - will call Livewire action
 */
function handleDrop(e) {
    if (e.stopPropagation) {
        e.stopPropagation();
    }

    e.currentTarget.classList.remove('drag-over');

    const taskId = e.dataTransfer.getData('text/plain');
    const boardId = e.currentTarget.closest('[data-board-id]')?.dataset.boardId;

    if (taskId && boardId) {
        // Phase 2: Call Livewire action to update task status
        // Livewire.dispatch('task-moved', { taskId, boardId });
        console.log(`Task ${taskId} moved to board ${boardId}`);
    }

    return false;
}

/**
 * Initialize modal interactions
 */
function initializeModals() {
    // Modal interactions are handled by Alpine.js in the Blade template
    // This is a placeholder for any additional JS needed
}

/**
 * Initialize task card interactions
 */
function initializeTaskCards() {
    // Task card interactions are handled by Livewire
    // This is a placeholder for any additional JS needed
}

/**
 * Utility function to get board color class
 */
export function getBoardColorClass(color) {
    const colorMap = {
        blue: 'bg-blue-500',
        yellow: 'bg-yellow-500',
        red: 'bg-red-500',
        green: 'bg-green-500',
    };

    return colorMap[color] || 'bg-gray-500';
}

