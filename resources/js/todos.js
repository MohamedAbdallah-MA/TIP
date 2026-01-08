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
 * Uses Event Delegation to handle dynamic DOM updates from Livewire
 */
function initializeDragAndDrop() {
    document.addEventListener('dragstart', handleDragStart);
    document.addEventListener('dragend', handleDragEnd);
    document.addEventListener('dragover', handleDragOver);
    document.addEventListener('drop', handleDrop);
    document.addEventListener('dragenter', handleDragEnter);
    document.addEventListener('dragleave', handleDragLeave);
}

/**
 * Handle drag start event
 */
function handleDragStart(e) {
    // Handle Text Nodes (e.g. dragging selected text) which don't have closest()
    const target = e.target.nodeType === Node.TEXT_NODE ? e.target.parentElement : e.target;

    const card = target.closest('[data-task-id]');
    if (!card) return;

    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', card.outerHTML);
    e.dataTransfer.setData('application/x-task-id', card.dataset.taskId);

    // Defer adding the class slightly so the drag image is created from the visible element
    setTimeout(() => {
        card.classList.add('opacity-50', 'dragging');
    }, 0);
}

/**
 * Handle drag end event
 */
function handleDragEnd(e) {
    const target = e.target.nodeType === Node.TEXT_NODE ? e.target.parentElement : e.target;
    // ... no changes needed to logic, just context matching ...
    const card = target.closest('[data-task-id]');
    if (!card) return;

    card.classList.remove('opacity-50', 'dragging');

    // Remove drag-over styling from all boards
    document.querySelectorAll('[data-board-id]').forEach(board => {
        board.classList.remove('drag-over');
    });
}

/**
 * Handle drag over event
 */
function handleDragOver(e) {
    const board = e.target.closest('[data-board-id]');
    if (!board) return;

    // Only allow drop if we have our custom type (or if we can't check types in dragover)
    // Note: e.dataTransfer.types includes the types available.
    if (e.dataTransfer.types.includes && !e.dataTransfer.types.includes('application/x-task-id')) {
        return;
    }

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
    const board = e.target.closest('[data-board-id]');
    if (!board) return;

    board.classList.add('drag-over');
}

/**
 * Handle drag leave event
 */
function handleDragLeave(e) {
    const board = e.target.closest('[data-board-id]');
    if (!board) return;

    // Only remove if we are actually leaving the board (not entering a child)
    // relatedTarget is the element we are entering
    if (!board.contains(e.relatedTarget)) {
        board.classList.remove('drag-over');
    }
}

/**
 * Handle drop event
 */
function handleDrop(e) {
    const board = e.target.closest('[data-board-id]');
    if (!board) return;

    if (e.stopPropagation) {
        e.stopPropagation();
    }

    board.classList.remove('drag-over');

    const taskIdStr = e.dataTransfer.getData('application/x-task-id');
    const status = board.dataset.status;

    if (taskIdStr && status) {
        const taskId = parseInt(taskIdStr);
        if (!isNaN(taskId)) {
            Livewire.dispatch('task-moved', { taskId: taskId, status: status });
        }
    }
    // No else: silently ignore drops that don't have our custom type

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
