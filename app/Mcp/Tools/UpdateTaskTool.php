<?php

namespace App\Mcp\Tools;

use App\DTOs\UpdateTaskDTO;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Services\TaskService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

class UpdateTaskTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = 'Update multiple fields of an existing task.';

    public function __construct(
        protected TaskService $taskService
    ) {}

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): ResponseFactory
    {
        $validatedData = $request->validate([
            'task_id' => 'required|integer|exists:tasks,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => ['nullable', Rule::enum(TaskStatus::class)],
            'priority' => ['nullable', Rule::enum(TaskPriority::class)],
        ], [
            'task_id.required' => 'The task_id field is required.',
            'task_id.integer' => 'The task_id must be an integer.',
            'task_id.exists' => 'The specified task does not exist.',
            'title.string' => 'The title must be a string.',
            'title.max' => 'The title must be 255 characters or less.',
            'description.string' => 'The description must be a string.',
            'status.enum' => 'The status must be a valid status enum value. Use one of: '.implode(', ', TaskStatus::names()),
            'priority.enum' => 'The priority must be a valid priority enum value. Use one of: '.implode(', ', TaskPriority::names()),
        ]);

        try {
            $task = $this->taskService->update(
                $validatedData['task_id'],
                $request->user()->id,
                UpdateTaskDTO::fromRequest($request)
            );

            return Response::make(
                Response::text('Task updated successfully')
            )->withStructuredContent([
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status->value,
                'priority' => $task->priority->value,
            ]);
        } catch (\Exception $e) {
            return Response::make(
                Response::error("Failed to update task: {$e->getMessage()}")
            );
        }
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\Contracts\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'task_id' => $schema->integer()
                ->description('The ID of the task to update'),
            'title' => $schema->string()
                ->description('The new title for the task')
                ->max(255),
            'description' => $schema->string()
                ->description('The new description for the task'),
            'status' => $schema->string()
                ->description('The new status for the task')
                ->enum(TaskStatus::names()),
            'priority' => $schema->string()
                ->description('The new priority for the task')
                ->enum(TaskPriority::names()),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()
                ->description('Task id number.'),
            'title' => $schema->string()
                ->description('Updated task title.'),
            'description' => $schema->string()
                ->description('Updated task description.'),
            'status' => $schema->string()
                ->description('Updated task status.'),
            'priority' => $schema->string()
                ->description('Updated task priority.'),
        ];
    }
}
