<?php

namespace App\Mcp\Tools;

use App\Services\TaskService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

class GetTaskTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = 'Get details of a specific task.';

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
        ], [
            'task_id.required' => 'The task_id field is required.',
            'task_id.integer' => 'The task_id must be an integer.',
            'task_id.exists' => 'The specified task does not exist.',
        ]);

        try {
            $task = $this->taskService->getByIdAndUserId(
                $validatedData['task_id'],
                $request->user()->id
            );

            return Response::make(
                Response::text('Task retrieved successfully')
            )->withStructuredContent([
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status->value,
                'priority' => $task->priority->value,
                'created_at' => $task->created_at->toDateTimeString(),
                'updated_at' => $task->updated_at->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            return Response::make(
                Response::error("Failed to retrieve task: {$e->getMessage()}")
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
                ->description('The ID of the task to retrieve'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()
                ->description('Task id number.'),
            'title' => $schema->string()
                ->description('Task title.'),
            'description' => $schema->string()
                ->description('Task description.'),
            'status' => $schema->string()
                ->description('Task status.'),
            'priority' => $schema->string()
                ->description('Task priority.'),
            'created_at' => $schema->string()
                ->description('Task creation timestamp.'),
            'updated_at' => $schema->string()
                ->description('Task last update timestamp.'),
        ];
    }
}
