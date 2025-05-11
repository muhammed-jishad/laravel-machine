<?php
namespace App\Models;

use App\Models\Task;
use App\Models\Comment;
use App\Models\Project;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory,HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- Relationships ---

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class); // Projects owned by the user
    }

    public function collaborations(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_user'); // Projects the user collaborates on
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_user_id'); // Tasks assigned to the user
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class); // Comments made by the user
    }
}
