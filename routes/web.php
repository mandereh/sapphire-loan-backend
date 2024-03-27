<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;

Route::get('/', function () {
    return view('welcome');
});
// Route::get('/loanDisburstmentNotification',[TestController::class,'loanDisburstmentNotification']);
// Route::get('/getSalary',[TestController::class,'getSalary']);
Route::post('/collection-notification',[\App\Http\Controllers\RemitaCollectionNotificationWebhookController::class,'handleCollectionNotification']);
