<?php

use App\Mcp\Servers\TaskManagmentServer;
use Laravel\Mcp\Facades\Mcp;

// Mcp::web('/mcp/demo', \App\Mcp\Servers\PublicServer::class);
Mcp::web('/mcp/task-managment', TaskManagmentServer::class)
    ->middleware(['auth:sanctum']);

Mcp::local('task-managment', TaskManagmentServer::class);
