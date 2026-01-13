<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;

class TestController extends Controller
{
    public function index()
    {
        dd(TaskStatus::fromInput('todo')->name);
        dd(TaskStatus::tryFrom('TODO'));
        dd(TaskStatus::tryFrom('1'));
    }
}
