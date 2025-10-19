<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ShieldPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all resources
        $resources = [
            'venue',
            'event',
            'sport',
            'event_time_slot',
            'event_sport',
            'age_group',
            'skill_level',
            'division',
            'product',
            'team',
            'team_player',
            'individual_player',
            'booth',
            'banner',
            'website_ad',
            'sale',
            'refund',
            'event_template',
            'user',
            'role',
        ];

        // Define all actions
        $actions = [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'restore',
            'restore_any',
            'replicate',
            'reorder',
            'force_delete',
            'force_delete_any',
        ];

        // Create permissions for each resource
        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$action}_{$resource}",
                    'guard_name' => 'web',
                ]);
            }
        }

        // Create additional custom permissions
        $customPermissions = [
            'process_refund',
            'approve_refund',
            'reject_refund',
            'view_own_team',
            'update_own_team',
            'add_player_to_own_team',
            'remove_player_from_own_team',
            'view_own_sale',
            'request_refund',
            'view_own_booth',
            'update_own_booth',
            'upload_booth_assets',
            'view_own_banner',
            'update_own_banner',
            'upload_banner_assets',
            'view_own_website_ad',
            'update_own_website_ad',
            'clone_event',
            'create_event_template',
        ];

        foreach ($customPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Update roles with permissions
        $this->assignPermissionsToRoles();
    }

    protected function assignPermissionsToRoles(): void
    {
        // Super Admin gets ALL permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions(Permission::all());

        // Admin gets most permissions (everything except super admin specific)
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(
            Permission::where('name', 'not like', '%role%')
                ->get()
        );

        // Team Owner permissions
        $teamOwner = Role::firstOrCreate(['name' => 'team_owner']);
        $teamOwner->syncPermissions([
            // View events and products
            'view_event',
            'view_any_event',
            'view_product',
            'view_any_product',
            'view_sport',
            'view_any_sport',
            'view_division',
            'view_any_division',

            // Manage own teams
            'view_own_team',
            'update_own_team',
            'add_player_to_own_team',
            'remove_player_from_own_team',
            'view_team_player',
            'create_team_player',
            'update_team_player',
            'delete_team_player',

            // Sales and refunds
            'view_own_sale',
            'request_refund',

            // View own data
            'view_user',
        ]);

        // Player permissions
        $player = Role::firstOrCreate(['name' => 'player']);
        $player->syncPermissions([
            'view_event',
            'view_any_event',
            'view_product',
            'view_any_product',
            'view_sport',
            'view_any_sport',
            'view_division',
            'view_any_division',
            'view_own_sale',
            'request_refund',
            'view_user',
        ]);

        // Vendor permissions
        $vendor = Role::firstOrCreate(['name' => 'vendor']);
        $vendor->syncPermissions([
            'view_event',
            'view_any_event',
            'view_product',
            'view_any_product',
            'view_own_booth',
            'update_own_booth',
            'upload_booth_assets',
            'view_own_banner',
            'update_own_banner',
            'upload_banner_assets',
            'view_own_website_ad',
            'update_own_website_ad',
            'view_own_sale',
            'view_user',
        ]);

        // Spectator permissions (minimal)
        $spectator = Role::firstOrCreate(['name' => 'spectator']);
        $spectator->syncPermissions([
            'view_event',
            'view_any_event',
            'view_own_sale',
            'view_user',
        ]);
    }
}
