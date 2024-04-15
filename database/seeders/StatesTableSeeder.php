<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('states')->insert(
            [
                [
                    'name' => 'Abia State',
                ],
                [
                    'name' => 'Lagos State',
                ],
                [
                    'name' => 'Rivers State',
                ]
            ]
        );
    }
}
