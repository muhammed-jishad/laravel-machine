<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the given project can be updated by the user.
     */
    public function update(User $user, Project $project)
    {
        return $user->id === $project->user_id;
    }

    /**
     * Determine if the given project can be viewed by the user.
     */
    public function view(User $user, Project $project)
    {
        return $user->id === $project->user_id || 
               $project->members()->where('user_id', $user->id)
                    ->where('status', 'accepted')
                    ->exists();
    }
} 