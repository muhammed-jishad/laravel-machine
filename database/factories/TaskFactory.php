<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'project_id' => Project::factory(),
            'assigned_user_id' => User::factory(),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'completed']),
        ];
    }
} 