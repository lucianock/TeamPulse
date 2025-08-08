<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo_url',
        'primary_color',
        'secondary_color',
        'timezone',
        'locale',
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
     * Get the users that belong to the organization.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the teams that belong to the organization.
     */
    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    /**
     * Get the surveys that belong to the organization.
     */
    public function surveys()
    {
        return $this->hasMany(Survey::class);
    }

    /**
     * Get the admins of the organization.
     */
    public function admins()
    {
        return $this->users()->whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        });
    }

    /**
     * Get the team leads of the organization.
     */
    public function teamLeads()
    {
        return $this->users()->whereHas('roles', function ($query) {
            $query->where('name', 'team_lead');
        });
    }

    /**
     * Get the members of the organization.
     */
    public function members()
    {
        return $this->users()->whereHas('roles', function ($query) {
            $query->where('name', 'member');
        });
    }

    /**
     * Generate a unique slug for the organization.
     */
    public static function generateSlug($name)
    {
        $slug = \Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
