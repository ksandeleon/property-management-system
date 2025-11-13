<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');

        // 1. Seed Permissions (must be first)
        $this->command->info('📋 Seeding permissions...');
        $this->call(PermissionSeeder::class);

        // 2. Seed Roles and assign permissions
        $this->command->info('👥 Seeding roles...');
        $this->call(RoleSeeder::class);

        // 3. Seed Categories
        $this->command->info('📦 Seeding categories...');
        $this->call(CategorySeeder::class);

        // 4. Seed Locations
        $this->command->info('📍 Seeding locations...');
        $this->call(LocationSeeder::class);

        // 5. Create Super Admin User
        $this->command->info('👑 Creating Super Admin user...');
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'department' => 'Administration',
                'position' => 'System Administrator',
                'employee_id' => 'EMP-0001',
                'phone' => '+1234567890',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Assign Super Admin role
        $superAdminRole = Role::where('name', 'super_administrator')->first();
        if ($superAdminRole) {
            $superAdmin->assignRole($superAdminRole);
        }

        // 6. Create test users for different roles
        $this->command->info('👤 Creating test users...');

        $testUser = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Property Manager',
                'password' => Hash::make('password'),
                'department' => 'Property Management',
                'position' => 'Manager',
                'employee_id' => 'EMP-0002',
                'phone' => '+1234567891',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $managerRole = Role::where('name', 'property_manager')->first();
        if ($managerRole) {
            $testUser->assignRole($managerRole);
        }

        $staffUser = User::firstOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'Staff User',
                'password' => Hash::make('password'),
                'department' => 'Operations',
                'position' => 'Staff',
                'employee_id' => 'EMP-0003',
                'phone' => '+1234567892',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $staffRole = Role::where('name', 'staff_user')->first();
        if ($staffRole) {
            $staffUser->assignRole($staffRole);
        }

        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('');
        $this->command->info('🔐 Login Credentials:');
        $this->command->info('   Super Admin: admin@example.com / password');
        $this->command->info('   Manager: manager@example.com / password');
        $this->command->info('   Staff: staff@example.com / password');
    }
}
