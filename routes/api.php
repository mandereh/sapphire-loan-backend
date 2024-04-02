<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/user/register', [UserController::class, 'register']);

Route::post('/user/login', [UserController::class, 'token']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('admin')->middleware('auth:sanctum')->group(function(){

    Route::get('/roles', [AdminController::class, 'listRoles']);

    Route::get('/permissions', [AdminController::class, 'listPermissions']);

    Route::post('/roles/create', [AdminController::class, 'createRole']);

    Route::post('/user/create', [AdminController::class, 'createAdmin']);
});