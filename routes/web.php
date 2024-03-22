<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/loanDisburstmentNotification',[\App\Http\Controllers\TestController::class,'loanDisburstmentNotification']);
Route::get('/getSalary',[\App\Http\Controllers\TestController::class,'getSalary']);
Route::post('/collection-notification',[\App\Http\Controllers\RemitaCollectionNotificationWebhookController::class,'handleCollectionNotification']);
