<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\RemitaCollectionNotificationWebhookController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/remita/getSalaryHistory',[\App\Http\Controllers\ApiTestController::class,'getRemitaSalaryHistory']);
Route::post('/remita/loanDisburstmentNotification',[\App\Http\Controllers\ApiTestController::class,'loanDisburstmentNotificationController']);
Route::post('/remita/mandateHistory',[\App\Http\Controllers\ApiTestController::class,'mandateHistoryController']);
Route::post('/remita/stopLoanCollection',[\App\Http\Controllers\ApiTestController::class,'stopLoanCollectionController']);

Route::post('/user/login', [UserController::class, 'token'])->name('login');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('admin')->middleware('auth:sanctum')->group(function(){

    Route::get('/roles', [AdminController::class, 'listRoles']);

    Route::get('/permissions', [AdminController::class, 'listPermissions']);

    Route::post('/roles/create', [AdminController::class, 'createRole']);

    Route::post('/user/create', [AdminController::class, 'createAdmin']);
});
Route::post('/leads/upload-lead', [LeadController::class, 'uploadLeads'])->name('leads.uploadLeads');
Route::post('/leads/reassign-lead', [LeadController::class, 'reassignLead'])->name('leads.reassignLead');
Route::post('/leads/delete-lead', [LeadController::class, 'deleteLeads'])->name('leads.deleteLeads');
Route::get('/users/{user}/view-leads-by-Account-officer', [LeadController::class, 'viewLeadsByAccountOfficer'])->name('leads.viewLeadsByAccountOfficer');
Route::get('/leads/view-all-leads', [LeadController::class, 'viewAllLeads'])->name('leads.viewAllLeads');

Route::post('/collection-notification', [RemitaCollectionNotificationWebhookController::class, 'handleCollectionNotification']);
