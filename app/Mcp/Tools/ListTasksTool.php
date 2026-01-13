<?php

namespace App\Mcp\Tools;

use App\Services\TaskService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

class ListTasksTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = 'List all tasks for the authenticated user.';

    public function __construct(
        protected TaskService $taskService
    ) {}

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): ResponseFactory
    {
        try {
            $tasks = $this->taskService->getAllByUser($request->user()->id);

            return Response::make(
                Response::text("Retrieved {$tasks->count()} tasks successfully")
            )->withStructuredContent(
                $tasks->map(fn ($task) => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => $task->status->value,
                    'priority' => $task->priority->value,
                ])->toArray()
            );
        } catch (\Exception $e) {
            return Response::make(
                Response::error("Failed to list tasks: {$e->getMessage()}")
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
        return [];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'tasks' => $schema->array()
                ->description('Array of tasks.')
                ->items($schema->object([
                    'id' => $schema->integer()->description('Task id.'),
                    'title' => $schema->string()->description('Task title.'),
                    'description' => $schema->string()->description('Task description.'),
                    'status' => $schema->string()->description('Task status.'),
                    'priority' => $schema->string()->description('Task priority.'),
                ])),
        ];
    }
}
