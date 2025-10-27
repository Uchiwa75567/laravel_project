<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Only create users if they don't exist
        \App\Models\User::firstOrCreate([
            'email' => 'admin@bankapi.com'
        ], [
            'name' => 'Admin User',
            'password' => bcrypt('Password!23'),
            'is_active' => true
        ]);

        \App\Models\User::firstOrCreate([
            'email' => 'test+bot@bankapi.com'
        ], [
            'name' => 'Test Bot',
            'password' => bcrypt('Password123'),
            'is_active' => true
        ]);

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            UserSeeder::class,
            ClientSeeder::class,
            CompteSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}
