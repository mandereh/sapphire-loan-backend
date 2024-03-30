<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\CreateAdminRequest;
use App\Http\Requests\Admin\CreateRoleRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    //

    public function listRoles(){
        $resp = [
            'status_code' => '00',
            'message' => "Roles fetched",
            'data' => Role::all()
        ];

        $statusCode = 200;

        return response($resp, $statusCode);
    }

    public function listPermissions(){
        $resp = [
            'status_code' => '00',
            'message' => "Permissions fetched",
            'data' => Permission::all()
        ];

        $statusCode = 200;

        return response($resp, $statusCode);
    }

    public function createRole(CreateRoleRequest $request){

        $role = Role::create([
            'name' => $request->name
        ]);

        $role->syncPermissions($request->permissions);

        $resp = [
            'status_code' => '00',
            'message' => "Role Created Successfully",
            'data' => $role
        ];

        $statusCode = 200;

        return response($resp, $statusCode);
    }

    public function createAdmin(CreateAdminRequest $request){

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'password' => Hash::make(Str::random(8)),
            'active' => true
        ]);

        // dd($request->user());

        $roles = Role::find($request->roles);

        $user->syncRoles($roles);

        $this->broker()->sendResetLink(
            ['email' => $user->email]
        );

        event(new Registered($user));

        $resp = [
            'status_code' => '00',
            'message' => "Admin User Created Successfully",
            'data' => $user
        ];

        $statusCode = 200;

        return response($resp, $statusCode);
    }


             /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    protected function broker(): PasswordBroker
    {
        return Password::broker(config('fortify.passwords'));
    }
}
