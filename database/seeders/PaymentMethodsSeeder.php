<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('payment_methods')->insert([
                [
                    'name'=>'Cash',
                ],
                [
                    'name'=>'Bank Transfer',
                ],
                [
                    'name'=>'Mobile Money',
                ],
                [
                    'name'=>'Remita',
                ]
            ]
        );
    }
}
