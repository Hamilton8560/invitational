<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupFilamentPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'filament:setup-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Filament Shield permissions for all resources and assign to super_admin role';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Creating Filament Shield permissions...');

        $resources = [
            'event',
            'venue',
            'sport',
            'division',
            'product',
            'sale',
            'refund',
            'team',
            'individual_player',
            'booth',
            'banner',
            'website_ad',
        ];

        $permissionCount = 0;

        foreach ($resources as $resource) {
            $permissions = ['view', 'view_any', 'create', 'update', 'delete', 'delete_any'];
            foreach ($permissions as $permission) {
                \Spatie\Permission\Models\Permission::firstOrCreate([
                    'name' => $permission.'_'.$resource,
                    'guard_name' => 'web',
                ]);
                $permissionCount++;
            }
        }

        $this->info("Created {$permissionCount} permissions.");

        $superAdmin = \Spatie\Permission\Models\Role::where('name', 'super_admin')->first();

        if ($superAdmin) {
            $superAdmin->syncPermissions(\Spatie\Permission\Models\Permission::all());
            $this->info('Assigned all permissions to super_admin role.');
        } else {
            $this->warn('super_admin role not found. Skipping role assignment.');
        }

        $this->info('✓ Filament Shield permissions setup complete!');

        return Command::SUCCESS;
    }
}
