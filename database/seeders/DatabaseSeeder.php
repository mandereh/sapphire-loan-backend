<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Organization;
use App\Models\State;
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
        $this->call(StatesTableSeeder::class);
        $this->call(OrganizationTableSeeder::class);
        $this->call(AdminSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(PermissionsTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(PaymentMethodsSeeder::class);
        $this->call(LoansTableSeeder::class);
        $this->call(RepaymentTableSeeder::class);
    }
}
