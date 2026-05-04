<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // Define all permissions
        $permissions = [
            // Employee permissions
            'employees.view',
            'employees.create',
            'employees.edit',
            'employees.delete',

            // Daily Hajira (Attendance) permissions
            'attendances.view',
            'attendances.create',
            'attendances.edit',
            'attendances.delete',

            // Advance permissions
            'advances.view',
            'advances.create',
            'advances.edit',
            'advances.delete',

            // Salary permissions
            'salaries.view',
            'salaries.generate',
            'salaries.lock',
            'salaries.regenerate',

            // Payment permissions
            'payments.view',
            'payments.create',
            'payments.delete',

            // Reports permissions
            'reports.view',

            // Settings/Admin permissions
            'settings.manage',
            'users.manage',
            'roles.manage',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Admin role and assign all permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        // Create Accountant role and assign specific permissions
        $accountantRole = Role::firstOrCreate(['name' => 'accountant']);
        $accountantRole->syncPermissions([
            'employees.view',
            'advances.view',
            'advances.create',
            'advances.edit',
            'advances.delete',
            'salaries.view',
            'salaries.generate',
            'salaries.lock',
            'salaries.regenerate',
            'payments.view',
            'payments.create',
            'payments.delete',
            'reports.view',
        ]);

        // Create Data Entry role and assign specific permissions
        $dataEntryRole = Role::firstOrCreate(['name' => 'data_entry']);
        $dataEntryRole->syncPermissions([
            'employees.view',
            'attendances.view',
            'attendances.create',
            'attendances.edit',
        ]);

        // Assign admin role to first user (if exists)
        $adminUser = \App\Models\User::where('email', 'admin@example.com')->first();
        if ($adminUser) {
            $adminUser->assignRole('admin');
        }

        // Assign admin role to default test user
        $testUser = \App\Models\User::where('email', 'a@a.com')->first();
        if ($testUser) {
            $testUser->assignRole('admin');
        }
    }
}
