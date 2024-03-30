<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            'Super Admin',
            'Admin',
            'Loan Officer',
            'Head Customer Service',
            'Customer Relationship Manager',
            'Risk'
        ];

        foreach($roles as $role){
            Role::where('name', $role)->firstOrCreate([
                'name' => $role,
                'guard_name' => 'sanctum'
            ]);
        }


    }
}
