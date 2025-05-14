<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\ProjectInvitation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Mail\MyTestEmail;
use Illuminate\Support\Facades\Mail;
class ProjectController extends Controller
{
    /**
     * Display a listing of the projects.
     */
    public function index()
    {
        $user = Auth::user();
        $projects = $user->projects()->with('user')->get();
        $invitedProjects = $user->invitedProjects()->with('user')->get();
        
        return response()->json([
            'owned_projects' => $projects,
            'invited_projects' => $invitedProjects
        ]);
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string'
        ]);

        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'user_id' => Auth::id()
        ]);

        return response()->json($project, 201);
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project)
    {
        $user = Auth::user();
        
        // Check if user owns the project or is invited
        if ($project->user_id !== $user->id && !$project->members()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $project->load(['user', 'tasks.assignedUser', 'members']);
        return response()->json($project);
    }

    /**
     * Update the specified project in storage.
     */
    public function update(Request $request, Project $project)
    {
        $user = Auth::user();
        
        // Check if user owns the project or is an accepted member
        if ($project->user_id !== $user->id && !$project->members()->where('user_id', $user->id)->where('status', 'accepted')->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string'
        ]);

        $project->update($request->all());
        return response()->json($project);
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Project $project)
    {
        if ($project->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $project->delete();
        return response()->json(null, 204);
    }

    public function inviteUser(Request $request, Project $project)
    {
        $request->validate([
            'email' => 'nullable|email',
            'user_id' => 'nullable|exists:users,id',
        ]);

        // Ensure at least one is provided
        if (!$request->email && !$request->user_id) {
            return response()->json(['message' => 'Either email or user_id is required.'], 422);
        }

        $user = null;
        $email = $request->email;

        if ($request->user_id) {
            $user = User::find($request->user_id);
            $email = $user->email;
        } elseif ($request->email) {
            $user = User::where('email', $request->email)->first();
        }

        if (!$email) {
            return response()->json(['message' => 'Email is required if user_id is not provided.'], 422);
        }

        $token = Str::random(40);

        // Save invitation
        

        try {
            if ($user) {
                Mail::raw('This is a test email from Laravel', function($message) {
                $message->to('shajishad5@gmail.com')
                        ->subject('Test Email');});
                
            } else {
               Mail::raw('This is a test email from Laravel', function($message) {
                $message->to('shajishad5@gmail.com')
                        ->subject('Test Email');
            });
                // Send to email directly if user not registered
                \Log::info('Notification sent to unregistered email');
            }

            return response()->json(['message' => 'Invitation sent!']);
        } catch (\Exception $e) {
            \Log::error('Failed to send invitation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $email,
                'project_id' => $project->id
            ]);

            return response()->json([
                'message' => 'Failed to send invitation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    

    

    public function getProjectTasks(Project $project)
    {
        $user = Auth::user();
        
        // Check if user owns the project or is an accepted member
        if ($project->user_id !== $user->id && !$project->members()->where('user_id', $user->id)->where('status', 'accepted')->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $tasks = $project->tasks()
            ->with(['assignedUser', 'comments'])
            ->get();

        return response()->json($tasks);
    }

    public function getInvitations(Project $project)
    {
        $user = Auth::user();
        
        // Only project owner can view invitations
        if ($project->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $invitations = $project->invitations()
            ->with('user')
            ->get();

        return response()->json($invitations);
    }
}
