<?php

namespace Database\Seeders;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminUser = User::firstOrCreate(['email' => 'cbekenawei@gmail.com'],[
            'first_name' => 'Courage',
            'last_name' => 'Bekenawei',
            'email' => 'cbekenawei@gmail.com',
            'phone_number' => '08061148035',
            'password' => Hash::make('Password1'),
            'active' => true,
            'refferal_code' => 0
        ]);

        $adminUser->email_verified_at = now();

        $adminUser->save();

        $adminRole = Role::where('name', 'Super Admin')->where('guard_name', 'sanctum')->first();

        $allPermissions = Permission::where('guard_name', 'sanctum')->get();

        $adminRole->syncPermissions($allPermissions);

        $adminUser->syncRoles($adminRole);
    }
}
