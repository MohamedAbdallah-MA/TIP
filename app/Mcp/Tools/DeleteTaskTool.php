<?php

namespace App\Mcp\Tools;

use App\Services\TaskService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

class DeleteTaskTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = 'Delete an existing task.';

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
            $this->taskService->delete(
                $validatedData['task_id'],
                $request->user()->id
            );

            return Response::make(
                Response::text('Task deleted successfully')
            )->withStructuredContent([
                'id' => $validatedData['task_id'],
                'deleted' => true,
            ]);
        } catch (\Exception $e) {
            return Response::make(
                Response::error("Failed to delete task: {$e->getMessage()}")
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
                ->description('The ID of the task to delete'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()
                ->description('Deleted task id number.'),
            'deleted' => $schema->boolean()
                ->description('Deletion status.'),
        ];
    }
}
