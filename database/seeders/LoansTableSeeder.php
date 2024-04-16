<?php

namespace Database\Seeders;

use App\Models\Loan;
use App\Models\LoanType;
use App\Models\Organization;
use App\Models\State;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LoansTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // Get some random users, loan types, organizations, and states


    public function run(): void
    {
        Loan::factory()->count(1)->create();
    }
}
