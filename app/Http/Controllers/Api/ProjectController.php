<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;
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
    public function show($id)
    {
         $project = Project::find($id);

    if (!$project) {
        return response()->json(['message' => 'Project not found'], 404);
    }

        $user = Auth::user();
        
        if ($project->user_id !== $user->id && !$project->members()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $project->load(['user', 'tasks.assignedUser', 'members']);
        return response()->json($project);
    }

    
    public function update(Request $request, $id)
{
    $user = Auth::user();
    $project = Project::find($id);

    if (!$project) {
        return response()->json(['message' => 'Project not found'], 404);
    }

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


    
public function destroy($id)
{
    $project = Project::find($id);


    if (!$project) {
        return response()->json(['message' => 'Project not found'], 404);
    }

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

    $invitation = ProjectInvitation::create([
        'project_id' => $project->id,
        'email' => $email,
        'token' => $token,
        'user_id' => $user ? $user->id : null,
        'status' => 'pending',  // Assuming you track status
    ]);

    try {
        if ($user) {
            Mail::raw('You are invited to join our exciting new project.', function ($message) use ($email) {
                $message->to($email)
                    ->subject('Project Email');
            });
        } else {
            // 
            \Log::info('Notification sent to unregistered email');
        }

        return response()->json(['message' => 'Invitation sent!', 'invitation' => $invitation]);
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
        
        if ($project->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $invitations = $project->invitations()
            ->with('user')
            ->get();

        return response()->json($invitations);
    }
    public function acceptInvitation($token)
{
    $invitation = ProjectInvitation::where('token', $token)->first();

    if (!$invitation) {
        return response()->json(['message' => 'Invalid or expired invitation.'], 404);
    }

    if ($invitation->status === 'accepted') {
        return response()->json(['message' => 'Invitation already accepted.'], 400);
    }

    $invitation->status = 'accepted';
    $invitation->accepted_at = now();
    $invitation->save();

    if ($invitation->user_id) {
        $exists = DB::table('project_members')
                    ->where('project_id', $invitation->project_id)
                    ->where('user_id', $invitation->user_id)
                    ->exists();

        if (!$exists) {
            DB::table('project_members')->insert([
                'project_id' => $invitation->project_id,
                'user_id' => $invitation->user_id,
                'status' => 'joined',
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    return response()->json(['message' => 'Invitation accepted.']);
}

}
