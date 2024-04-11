<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $list = [
            [
                'name' => 'Techno Camon 20',
                'price' => '128000',
                'active' => true
            ],
            [
                'name' => 'Infinix Note 20',
                'price' => '112000',
                'active' => true
            ],
            [
                'name' => 'Itel Bam Bam',
                'price' => '85000',
                'active' => true
            ]
        ];

        foreach($list as $item){
            Product::firstOrCreate($item);
        }
    }
}
