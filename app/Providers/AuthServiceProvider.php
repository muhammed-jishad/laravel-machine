<?php

namespace App\Providers;

use App\Models\Task;
use App\Models\Project;
use App\Policies\TaskPolicy;
use App\Policies\ProjectPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Task::class => TaskPolicy::class,
        Project::class => ProjectPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
} 