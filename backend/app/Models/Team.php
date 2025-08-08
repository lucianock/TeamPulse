<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'team_lead_id',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the organization that the team belongs to.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the team lead of the team.
     */
    public function teamLead()
    {
        return $this->belongsTo(User::class, 'team_lead_id');
    }

    /**
     * Get the users that belong to the team.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'team_user')
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }

    /**
     * Get the surveys that belong to the team.
     */
    public function surveys()
    {
        return $this->hasMany(Survey::class);
    }

    /**
     * Get the team members (excluding the team lead).
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'team_user')
                    ->wherePivot('role', 'member')
                    ->withPivot('joined_at')
                    ->withTimestamps();
    }

    /**
     * Get the team leads.
     */
    public function leads()
    {
        return $this->belongsToMany(User::class, 'team_user')
                    ->wherePivot('role', 'lead')
                    ->withPivot('joined_at')
                    ->withTimestamps();
    }

    /**
     * Add a user to the team.
     */
    public function addUser(User $user, string $role = 'member')
    {
        return $this->users()->attach($user->id, [
            'role' => $role,
            'joined_at' => now(),
        ]);
    }

    /**
     * Remove a user from the team.
     */
    public function removeUser(User $user)
    {
        return $this->users()->detach($user->id);
    }

    /**
     * Check if a user is a member of the team.
     */
    public function hasUser(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if a user is a lead of the team.
     */
    public function hasLead(User $user): bool
    {
        return $this->leads()->where('user_id', $user->id)->exists();
    }
}
