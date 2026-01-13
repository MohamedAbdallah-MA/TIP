<?php

namespace App\Mcp\Tools;

use App\Enums\TaskStatus;
use App\Services\TaskService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

class ChangeTaskStatusTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = 'Change the status of an existing task.';

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
            'status' => ['required', Rule::enum(TaskStatus::class)],
        ], [
            'task_id.required' => 'The task_id field is required.',
            'task_id.integer' => 'The task_id must be an integer.',
            'task_id.exists' => 'The specified task does not exist.',
            'status.required' => 'The status field is required.',
            'status.enum' => 'The status must be a valid status enum value. Use one of: '.implode(', ', TaskStatus::names()),
        ]);

        try {
            $task = $this->taskService->updateStatus(
                $validatedData['task_id'],
                $request->user()->id,
                TaskStatus::fromInput($validatedData['status'])
            );

            return Response::make(
                Response::text('Task status updated successfully')
            )->withStructuredContent([
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status->value,
            ]);
        } catch (\Exception $e) {
            return Response::make(
                Response::error("Failed to update task status: {$e->getMessage()}")
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
            'status' => $schema->string()
                ->description('The new status for the task')
                ->enum(TaskStatus::names()),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()
                ->description('Task id number.'),
            'title' => $schema->string()
                ->description('Task title.'),
            'status' => $schema->string()
                ->description('Updated task status.'),
        ];
    }
}
