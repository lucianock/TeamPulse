<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // Organization permissions
            'view_organizations',
            'create_organizations',
            'edit_organizations',
            'delete_organizations',
            
            // Team permissions
            'view_teams',
            'create_teams',
            'edit_teams',
            'delete_teams',
            'manage_team_members',
            
            // Survey permissions
            'view_surveys',
            'create_surveys',
            'edit_surveys',
            'delete_surveys',
            'activate_surveys',
            'view_survey_responses',
            'view_survey_statistics',
            
            // User permissions
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'assign_roles',
            
            // Dashboard permissions
            'view_dashboard',
            'view_organization_stats',
            'view_team_stats',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $teamLeadRole = Role::create(['name' => 'team_lead']);
        $memberRole = Role::create(['name' => 'member']);

        // Assign permissions to admin role
        $adminRole->givePermissionTo(Permission::all());

        // Assign permissions to team lead role
        $teamLeadRole->givePermissionTo([
            'view_organizations',
            'view_teams',
            'create_teams',
            'edit_teams',
            'manage_team_members',
            'view_surveys',
            'create_surveys',
            'edit_surveys',
            'delete_surveys',
            'activate_surveys',
            'view_survey_responses',
            'view_survey_statistics',
            'view_users',
            'view_dashboard',
            'view_organization_stats',
            'view_team_stats',
        ]);

        // Assign permissions to member role
        $memberRole->givePermissionTo([
            'view_organizations',
            'view_teams',
            'view_surveys',
            'view_dashboard',
        ]);
    }
}
