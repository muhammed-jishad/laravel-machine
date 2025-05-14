<?php

namespace App\Models;

use App\Models\User;
use App\Models\Comment;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'project_id',
        'assigned_user_id',
        'status',
    ];

    protected $casts = [
        'due_date' => 'datetime'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }
}
