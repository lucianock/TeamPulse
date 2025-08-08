<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TeamController extends Controller
{
    /**
     * Display a listing of teams.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Admin can see all teams, team leads see their teams, members see their team
        if ($user->isAdmin()) {
            $teams = Team::with(['organization', 'teamLead', 'users'])->get();
        } elseif ($user->isTeamLead()) {
            $teams = Team::where('team_lead_id', $user->id)
                ->orWhereHas('users', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with(['organization', 'teamLead', 'users'])
                ->get();
        } else {
            $teams = Team::whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['organization', 'teamLead', 'users'])
            ->get();
        }

        return response()->json([
            'teams' => $teams
        ]);
    }

    /**
     * Display the specified team.
     */
    public function show(Request $request, Team $team)
    {
        $user = $request->user();
        
        // Check if user has access to this team
        if (!$user->isAdmin() && !$team->hasUser($user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $team->load(['organization', 'teamLead', 'users', 'surveys']);

        return response()->json([
            'team' => $team
        ]);
    }

    /**
     * Store a newly created team.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'team_lead_id' => 'nullable|exists:users,id',
            'organization_id' => 'required|exists:organizations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check permissions
        if (!$user->isAdmin() && $request->organization_id != $user->organization_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $team = Team::create([
            'name' => $request->name,
            'description' => $request->description,
            'team_lead_id' => $request->team_lead_id,
            'organization_id' => $request->organization_id,
        ]);

        // Add team lead to team if specified
        if ($request->team_lead_id) {
            $team->addUser(User::find($request->team_lead_id), 'lead');
        }

        return response()->json([
            'message' => 'Team created successfully',
            'team' => $team->load(['organization', 'teamLead'])
        ], 201);
    }

    /**
     * Update the specified team.
     */
    public function update(Request $request, Team $team)
    {
        $user = $request->user();
        
        // Check if user has access to this team
        if (!$user->isAdmin() && !$team->hasLead($user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'team_lead_id' => 'nullable|exists:users,id',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $team->update($request->only(['name', 'description', 'team_lead_id', 'is_active']));

        return response()->json([
            'message' => 'Team updated successfully',
            'team' => $team->load(['organization', 'teamLead'])
        ]);
    }

    /**
     * Remove the specified team.
     */
    public function destroy(Team $team)
    {
        // Only admins can delete teams
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $team->delete();

        return response()->json([
            'message' => 'Team deleted successfully'
        ]);
    }

    /**
     * Add a member to the team.
     */
    public function addMember(Request $request, Team $team)
    {
        $user = $request->user();
        
        // Check if user has access to this team
        if (!$user->isAdmin() && !$team->hasLead($user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role' => 'nullable|in:member,lead',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $memberUser = User::find($request->user_id);
        
        // Check if user belongs to the same organization
        if ($memberUser->organization_id !== $team->organization_id) {
            return response()->json(['message' => 'User must belong to the same organization'], 400);
        }

        // Check if user is already in the team
        if ($team->hasUser($memberUser)) {
            return response()->json(['message' => 'User is already a member of this team'], 400);
        }

        $team->addUser($memberUser, $request->role ?? 'member');

        return response()->json([
            'message' => 'Member added successfully',
            'team' => $team->load(['users'])
        ]);
    }

    /**
     * Remove a member from the team.
     */
    public function removeMember(Request $request, Team $team, User $user)
    {
        $currentUser = $request->user();
        
        // Check if user has access to this team
        if (!$currentUser->isAdmin() && !$team->hasLead($currentUser)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if the user to remove is actually in the team
        if (!$team->hasUser($user)) {
            return response()->json(['message' => 'User is not a member of this team'], 400);
        }

        $team->removeUser($user);

        return response()->json([
            'message' => 'Member removed successfully',
            'team' => $team->load(['users'])
        ]);
    }
}
