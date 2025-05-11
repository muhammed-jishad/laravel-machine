<?php

namespace App\Models;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'user_id'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function collaborators()
    {
        return $this->belongsToMany(User::class, 'project_user');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}

