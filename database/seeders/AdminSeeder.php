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
        $adminUser = User::firstOrCreate(['email' => 'courage.bekenawei@spectrummfb.com'],[
            'name' => 'Courage Bekenawei',
            'email' => 'cbekenawei@gmail.com',
            'phone_number' => '08061148035',
            'username' => 'courage',
            'password' => Hash::make('Password1'),
        ]);

        $adminUser->email_verified_at = now();

        $adminUser->save();

        $adminRole = Role::where('name', 'Super Admin')->first();

        $allPermissions = Permission::where('guard_name', 'web')->get();

        $adminRole->syncPermissions($allPermissions, '');

        $adminUser->syncRoles($adminRole);
    }
}
