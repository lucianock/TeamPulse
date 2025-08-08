<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'organization_id',
        'team_id',
        'avatar_url',
        'job_title',
        'phone',
        'status',
        'last_login_at',
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
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the organization that the user belongs to.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the team that the user belongs to.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the teams that the user is a member of.
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_user')
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }

    /**
     * Get the surveys created by the user.
     */
    public function surveys()
    {
        return $this->hasMany(Survey::class, 'created_by');
    }

    /**
     * Get the survey responses submitted by the user.
     */
    public function surveyResponses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is a team lead.
     */
    public function isTeamLead(): bool
    {
        return $this->hasRole('team_lead');
    }

    /**
     * Check if user is a member.
     */
    public function isMember(): bool
    {
        return $this->hasRole('member');
    }
}
