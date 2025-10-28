<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update admin user (idempotent)
        \App\Models\User::updateOrCreate([
            'email' => 'admin@bankapi.com',
        ], [
            'first_name' => 'Admin',
            'last_name' => 'System',
            'name' => 'Admin System',
            'phone' => '+33123456789',
            'role' => 'admin',
            'is_active' => true,
            'password' => bcrypt('password'),
            'preferences' => [
                'language' => 'fr',
                'theme' => 'dark',
                'notifications' => true,
            ],
        ]);

        // Create or update manager users (idempotent)
        \App\Models\User::updateOrCreate([
            'email' => 'marie.dubois@bankapi.com',
        ], [
            'first_name' => 'Marie',
            'last_name' => 'Dubois',
            'name' => 'Marie Dubois',
            'phone' => '+33234567890',
            'role' => 'manager',
            'is_active' => true,
            'password' => bcrypt('password'),
            'preferences' => [
                'language' => 'fr',
                'theme' => 'light',
                'notifications' => true,
            ],
        ]);

        \App\Models\User::updateOrCreate([
            'email' => ' ',
        ], [
            'first_name' => 'Pierre',
            'last_name' => 'Martin',
            'name' => 'Pierre Martin',
            'phone' => '+33345678901',
            'role' => 'manager',
            'is_active' => true,
            'password' => bcrypt('password'),
            'preferences' => [
                'language' => 'fr',
                'theme' => 'light',
                'notifications' => false,
            ],
        ]);

        // Create or update regular users (idempotent)
        \App\Models\User::updateOrCreate([
            'email' => 'jean.garcia@email.com',
        ], [
            'first_name' => 'Jean',
            'last_name' => 'Garcia',
            'name' => 'Jean Garcia',
            'phone' => '+33456789012',
            'role' => 'user',
            'is_active' => true,
            'password' => bcrypt('password'),
            'preferences' => [
                'language' => 'fr',
                'theme' => 'light',
                'notifications' => true,
            ],
        ]);

        \App\Models\User::updateOrCreate([
            'email' => 'sophie.bernard@email.com',
        ], [
            'first_name' => 'Sophie',
            'last_name' => 'Bernard',
            'name' => 'Sophie Bernard',
            'phone' => '+33567890123',
            'role' => 'user',
            'is_active' => true,
            'password' => bcrypt('password'),
            'preferences' => [
                'language' => 'en',
                'theme' => 'dark',
                'notifications' => true,
            ],
        ]);

        // Create or update some inactive users for testing
        \App\Models\User::updateOrCreate([
            'email' => 'alice.rousseau@email.com',
        ], [
            'first_name' => 'Alice',
            'last_name' => 'Rousseau',
            'name' => 'Alice Rousseau',
            'phone' => '+33678901234',
            'role' => 'user',
            'is_active' => false,
            'password' => bcrypt('password'),
            'preferences' => [
                'language' => 'fr',
                'theme' => 'light',
                'notifications' => false,
            ],
        ]);

        // Create additional random users (reduced for production)
        \App\Models\User::factory(5)->create();

        // Create or update some users with specific characteristics
        \App\Models\User::updateOrCreate([
            'email' => 'superadmin@bankapi.com',
        ], [
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'name' => 'Super Admin',
            'phone' => '+33789012345',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        \App\Models\User::updateOrCreate([
            'email' => 'claire.petit@bankapi.com',
        ], [
            'first_name' => 'Claire',
            'last_name' => 'Petit',
            'name' => 'Claire Petit',
            'phone' => '+33890123456',
            'role' => 'manager',
            'password' => bcrypt('password'),
        ]);

        \App\Models\User::updateOrCreate([
            'email' => 'lucas.moreau@email.com',
        ], [
            'first_name' => 'Lucas',
            'last_name' => 'Moreau',
            'name' => 'Lucas Moreau',
            'phone' => '+33901234567',
            'role' => 'user',
            'password' => bcrypt('password'),
        ]);

        // Create or update users with different languages
        \App\Models\User::updateOrCreate([
            'email' => 'john.smith@email.com',
        ], [
            'first_name' => 'John',
            'last_name' => 'Smith',
            'name' => 'John Smith',
            'phone' => '+440123456789',
            'password' => bcrypt('password'),
            'preferences' => ['language' => 'en'],
        ]);

        \App\Models\User::updateOrCreate([
            'email' => 'maria.garcia@email.com',
        ], [
            'first_name' => 'Maria',
            'last_name' => 'Garcia',
            'name' => 'Maria Garcia',
            'phone' => '+34123456789',
            'password' => bcrypt('password'),
            'preferences' => ['language' => 'es'],
        ]);

        \App\Models\User::updateOrCreate([
            'email' => 'hans.mueller@email.com',
        ], [
            'first_name' => 'Hans',
            'last_name' => 'Mueller',
            'name' => 'Hans Mueller',
            'phone' => '+49123456789',
            'password' => bcrypt('password'),
            'preferences' => ['language' => 'de'],
        ]);
    }
}
