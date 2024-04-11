<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'review-loan',
            'approve-loan',
            'view-loan',
            'view-all-loans',
            'edit-loan',
            'request-penalty-waiver',
            'approve-penalty-waiver',
            'disburse-loan',
            'setup-deduction',
            'view-deductions',
            'view-repayments',
            'edit-repayment',
            'delete-repayment',
            'add-repayment',
            'view-users',
            'view-user-details',
            'edit-user',
            'delete-user',
            'deactivate-user',
            'view-roles',
            'assign-role',
            'view-permissions',
            'assign-permissions',
            'view-all-leads',
            'reassign-leads',
            'upload-leads',
            'delete-leads',
            'view-logs',
            'view-banks',
            'view-admin',
            'create-admin',
            'edit-admin',
        ];

        foreach($permissions as $permission){
            $permission = Permission::where(['name' => $permission, 'guard_name' => 'sanctum'])->firstOrNew(
                [
                    'name' => $permission,
                    'guard_name' => 'sanctum'
                ]
            );

            $permission->save();
        }
    }
}
