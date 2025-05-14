<?php

namespace App\Http\Controllers\Api;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\TaskComment;

class TaskController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $query = Task::query();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by assigned user
        

        // Sort by due date
        $sortDirection = $request->input('sort_direction', 'asc');

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:pending,in_progress,completed',
           
        ]);

        $task = Task::create($validated);
        return response()->json($task, 201);
    }

    public function show(Task $task)
    {
        return response()->json($task->load('comments.user'));
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'string|max:255',
            'description' => 'string',
            'status' => 'in:pending,in_progress,completed',
          
        ]);

        $task->update($validated);
        return response()->json($task);
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(null, 204);
    }

    // Comment related methods
    public function addComment(Request $request, Task $task)
    {
        $validated = $request->validate([
            'content' => 'required|string'
        ]);

        $comment = $task->comments()->create([
            'content' => $validated['content'],
            'user_id' => Auth::id()
        ]);

        return response()->json($comment->load('user'), 201);
    }

    public function getComments(Task $task)
    {
        return response()->json($task->comments()->with('user')->get());
    }

    public function deleteComment(Task $task, TaskComment $comment)
    {
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->delete();
        return response()->json(null, 204);
    }
} 