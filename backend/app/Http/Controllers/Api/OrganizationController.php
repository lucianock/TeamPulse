<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrganizationController extends Controller
{
    /**
     * Display a listing of organizations.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Admin can see all organizations, others see only their own
        if ($user->isAdmin()) {
            $organizations = Organization::with(['teams', 'users'])->get();
        } else {
            $organizations = Organization::where('id', $user->organization_id)
                ->with(['teams', 'users'])
                ->get();
        }

        return response()->json([
            'organizations' => $organizations
        ]);
    }

    /**
     * Display the specified organization.
     */
    public function show(Request $request, Organization $organization)
    {
        $user = $request->user();
        
        // Check if user has access to this organization
        if (!$user->isAdmin() && $user->organization_id !== $organization->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $organization->load(['teams', 'users', 'surveys']);

        return response()->json([
            'organization' => $organization
        ]);
    }

    /**
     * Store a newly created organization.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo_url' => 'nullable|url|max:255',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'timezone' => 'nullable|string|max:50',
            'locale' => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $organization = Organization::create([
            'name' => $request->name,
            'slug' => Organization::generateSlug($request->name),
            'description' => $request->description,
            'logo_url' => $request->logo_url,
            'primary_color' => $request->primary_color ?? '#3B82F6',
            'secondary_color' => $request->secondary_color ?? '#1F2937',
            'timezone' => $request->timezone ?? 'UTC',
            'locale' => $request->locale ?? 'es',
        ]);

        return response()->json([
            'message' => 'Organization created successfully',
            'organization' => $organization
        ], 201);
    }

    /**
     * Update the specified organization.
     */
    public function update(Request $request, Organization $organization)
    {
        $user = $request->user();
        
        // Check if user has access to this organization
        if (!$user->isAdmin() && $user->organization_id !== $organization->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'logo_url' => 'nullable|url|max:255',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'timezone' => 'nullable|string|max:50',
            'locale' => 'nullable|string|max:10',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $organization->update($request->only([
            'name', 'description', 'logo_url', 'primary_color', 
            'secondary_color', 'timezone', 'locale', 'is_active'
        ]));

        return response()->json([
            'message' => 'Organization updated successfully',
            'organization' => $organization
        ]);
    }

    /**
     * Remove the specified organization.
     */
    public function destroy(Organization $organization)
    {
        // Only admins can delete organizations
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $organization->delete();

        return response()->json([
            'message' => 'Organization deleted successfully'
        ]);
    }
}
