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
        \App\Models\Organization::factory(5)->create();

        // \App\Models\User::factory()->create();

        $this->call(RolesTableSeeder::class);
        $this->call(PermissionsTableSeeder::class);
        $this->call(LoanTypeSeeder::class);
        $this->call(AdminSeeder::class);
        $this->call(PermissionsTableSeeder::class);
        $this->call(LoanTypeSeeder::class);
        $this->call(RolesTableSeeder::class);
    }
}
