<?php

namespace App\Mcp\Tools;

use App\Enums\TaskPriority;
use App\Services\TaskService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

class ChangeTaskPriorityTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = 'Change the priority of an existing task.';

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
            'priority' => ['required', Rule::enum(TaskPriority::class)],
        ], [
            'task_id.required' => 'The task_id field is required.',
            'task_id.integer' => 'The task_id must be an integer.',
            'task_id.exists' => 'The specified task does not exist.',
            'priority.required' => 'The priority field is required.',
            'priority.enum' => 'The priority must be a valid priority enum value. Use one of: '.implode(', ', TaskPriority::names()),
        ]);

        try {
            $task = $this->taskService->updatePriority(
                $validatedData['task_id'],
                $request->user()->id,
                TaskPriority::fromInput($validatedData['priority'])
            );

            return Response::make(
                Response::text('Task priority updated successfully')
            )->withStructuredContent([
                'id' => $task->id,
                'title' => $task->title,
                'priority' => $task->priority->value,
            ]);
        } catch (\Exception $e) {
            return Response::make(
                Response::error("Failed to update task priority: {$e->getMessage()}")
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
                ->description('Task title.'),
            'priority' => $schema->string()
                ->description('task priority.'),
        ];
    }
}