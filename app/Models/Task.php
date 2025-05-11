<?php

namespace App\Models;

use App\Models\User;
use App\Models\Comment;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;

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

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
