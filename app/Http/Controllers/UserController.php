<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\LoginUserRequest;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    //
    public function token(LoginUserRequest $request){

        // $sourceApp =  AppAccessToken::where('app_name', $request->input("appName", $request->header('appname')))->first();

        $email = $request->input('email', filter_var($request->username, FILTER_VALIDATE_EMAIL) ? $request->username : '');

        $phoneNumber = $request->input('phone_number', ctype_digit($request->username) && strlen($request->username) == '11' ? $request->username : '');

        $user = User::where('email', $email)->orWhere('phone_number', $phoneNumber)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->active) {
            throw ValidationException::withMessages([
                'email' => ['Your account is no longer active.'],
            ]);
        }

        // Revoke all tokens...
        //$user->tokens()->delete();

        //Update user last login
        $token = $user->createToken($request->header('appName'), $user->allPermissions()->toArray())->plainTextToken;

        Event::dispatch(new Login(auth()->guard(), $user, false));

        // $user->last_login = Carbon::now()->toDateTimeString();
        // $user->save();


        $resp = [
            'status_code' => '00',
            'message' => "Token retrieved",
            'token' => $token,
            'permissions' => $user->allPermissions()
        ];

        $statusCode = 200;

        return response($resp, $statusCode);
    }

    public function viewAllUsers(){

    }

    public function activateUsers(){
        
    }

}
