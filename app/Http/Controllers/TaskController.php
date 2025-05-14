<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Task::class, 'task');
    }

    public function index(Project $project)
    {
        $tasks = $project->tasks()->with(['assignedUser', 'comments'])->get();
        return response()->json($tasks);
    }

    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'assigned_user_id' => 'nullable|exists:users,id',
            'status' => 'required|in:pending,in_progress,completed'
        ]);

        $task = $project->tasks()->create($validated);
        return response()->json($task, 201);
    }

    public function show(Project $project, Task $task)
    {
        $task->load(['assignedUser', 'comments']);
        return response()->json($task);
    }

    public function update(Request $request, Project $project, Task $task)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'assigned_user_id' => 'nullable|exists:users,id',
            'status' => 'sometimes|required|in:pending,in_progress,completed'
        ]);

        $task->update($validated);
        return response()->json($task);
    }

    public function destroy(Project $project, Task $task)
    {
        $task->delete();
        return response()->json(null, 204);
    }
} 