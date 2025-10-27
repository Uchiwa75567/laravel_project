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
            \App\Models\User::create([
                'name' => 'Admin User',
                'email' => 'admin@bankapi.com',
                'password' => bcrypt('Password!23'),
                'is_active' => true
            ]);
        
            \App\Models\User::create([
                'name' => 'Test Bot',
                'email' => 'test+bot@bankapi.com',
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
