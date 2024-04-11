<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\LoginUserRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class UserController extends Controller
{


    public function userDetails(Request $request){
        $resp = [
            'status_code' => '00',
            'message' => "User details retrieved",
            'data' => $request->user()
        ];

        $statusCode = 200;

        return response($resp, $statusCode);
    }

    public function register(RegisterRequest $request){

        $lastCode = User::latest()->first()->refferal_code ? User::latest()->first()->refferal_code :  100000;

        $lastCode++;

        $email = $request->email;

        if($email && str_contains($email, 'example.com')){
            $email = null;
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $email,
            'phone_number' => $request->phone_number,
            'ippis_number' => $request->ippis_number,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'password' => Hash::make(Str::random(8)),
            'active' => true,
            'refferal_code' => $lastCode
        ]);
        

        $resp = [
            'status_code' => '00',
            'message' => "User created successfully",
            'data' => $user
        ];

        $statusCode = 200;

        return response($resp, $statusCode);
    }

    public function getUserByIppis(Request $request){
        // $request->validate([
        //     'ippis' => 'nullable|numeric',
        //     'ippis' => 'required|numeric'
        // ]);

        $user = User::where('ippis_number', $request->ippis)
                        ->orWhere('phone_number', $request->phone_number)
                        ->orWhere('email', $request->email)
                        ->first();

        if($user){
            $statusCode = 200;

            $resp = [
                'status_code' => '00',
                'message' => "User exists",
                'data' => $user
            ];
        }else{
            $statusCode = 404;

            $resp = [
                'status_code' => '40',
                'message' => "User does not exist",
                'data' => $user
            ];
        }

        // dd($resp);

        return response()->json($resp, $statusCode);
    }

    //
    public function token(Request $request){

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

        return response()->json($resp, $statusCode);
    }

    public function viewAllUsers(){

    }

    public function activateUsers(){
        
    }

}
