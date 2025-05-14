<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'project_id' => $this->project_id,
            'assigned_user_id' => $this->assigned_user_id,
            'assigned_user' => new UserResource($this->whenLoaded('assignedUser')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 