<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoanTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('loan_types')->insert([
            'name' => 'Cash Loan',
            'cute_name' => 'I want cash',
            'status' => 'enabled',
            'rate' => 5,
            'dti' => 50,
            'fees' => 0
        ],
        [
            'name' => 'Asset Loan', 
            'status' => 'enabled', 
            'cute_name' => 'I want a Phone',
            'rate' => 5,
            'dti' => 50,
            'fees' => 0
        ]
            // ['name' => 'Salary', 'status' => 'enabled'],
            // ['name' => 'Business', 'status' => 'enabled']
        );
    }
}
