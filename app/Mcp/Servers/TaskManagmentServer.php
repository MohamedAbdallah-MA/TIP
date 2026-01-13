<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\ChangeTaskPriorityTool;
use App\Mcp\Tools\ChangeTaskStatusTool;
use App\Mcp\Tools\CreateTaskTool;
use App\Mcp\Tools\DeleteTaskTool;
use App\Mcp\Tools\GetTaskTool;
use App\Mcp\Tools\ListTasksTool;
use App\Mcp\Tools\UpdateTaskTool;
use Laravel\Mcp\Server;

class TaskManagmentServer extends Server
{
    /**
     * The MCP server's name.
     */
    protected string $name = 'Task Managment Server';

    /**
     * The MCP server's version.
     */
    protected string $version = '0.0.1';

    /**
     * The MCP server's instructions for the LLM.
     */
    protected string $instructions = 'Task Managment Server';

    /**
     * The MCP server's description.
     */
    protected string $description = 'Task Managment Server';

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        CreateTaskTool::class,
        UpdateTaskTool::class,
        ChangeTaskStatusTool::class,
        ChangeTaskPriorityTool::class,
        DeleteTaskTool::class,
        GetTaskTool::class,
        ListTasksTool::class,
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Resource>>
     */
    protected array $resources = [
        //
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Prompt>>
     */
    protected array $prompts = [
        //
    ];
}
