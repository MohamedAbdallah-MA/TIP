<?php

namespace App\Mcp\Tools;

use App\DTOs\CreateTaskDTO;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Services\TaskService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

class CreateTaskTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = 'Create a new task.';

    /**
     * Handle the tool request.
     */
    public function __construct(
        protected TaskService $taskService
    ) {}

    public function handle(Request $request): ResponseFactory
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'status' => ['required', Rule::enum(TaskStatus::class)],
        ], [
            'title.required' => 'The title field is required.',
            'title.string' => 'The title must be a string.',
            'title.max' => 'The title must be 255 characters or less.',
            'description.string' => 'The description must be a string.',
            'priority.enum' => 'The priority must be a valid priority enum value. Use one of: '.implode(', ', TaskPriority::names()),
            'status.enum' => 'The status must be a valid status enum value. Use one of: '.implode(', ', TaskStatus::names()),
        ]);

        try {
            $task = $this->taskService->create(CreateTaskDTO::fromRequest($request));

            return Response::make(
                Response::text('Task created successfully'))
                ->withStructuredContent([
                    'id' => $task->id,
                    'title' => $task->title,
                ]);

        } catch (\Exception $e) {
            return Response::make(
                Response::error("Failed to create task: {$e->getMessage()}")
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
            'title' => $schema->string()
                ->description('The title of the task')
                ->max(255),
            'description' => $schema->string()
                ->description('The description of the task'),
            'priority' => $schema->string()
                ->description('The priority of the task')
                ->enum(TaskPriority::names())
                ->default(TaskPriority::fromInput('low')?->name),
            'status' => $schema->string()
                ->description('The status of the task')
                ->enum(TaskStatus::names())
                ->default(TaskStatus::fromInput('todo')?->name),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()
                ->description('Task id number.'),
            'title' => $schema->string()
                ->description('Task title.'),
        ];
    }
}
