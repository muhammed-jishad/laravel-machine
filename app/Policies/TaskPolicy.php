<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Task $task)
    {
        return $user->id === $task->project->user_id || 
               $task->project->members()->where('user_id', $user->id)->exists();
    }

    public function create(User $user, Task $task)
    {
        return $user->id === $task->project->user_id || 
               $task->project->members()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Task $task)
    {
        return $user->id === $task->project->user_id || 
               $task->project->members()->where('user_id', $user->id)->exists();
    }

    public function delete(User $user, Task $task)
    {
        return $user->id === $task->project->user_id;
    }
} 