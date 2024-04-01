<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\RemitaCollectionNotificationWebhookController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::post('/leads/delete-lead', [LeadController::class, 'uploadLeads'])->name('leads.deleteLeads');
Route::post('/leads/view-leads-by-Account-officer', [LeadController::class, 'viewLeadsByAccountOfficer'])->name('leads.viewLeadsByAccountOfficer');

Route::post('/collection-notification', [RemitaCollectionNotificationWebhookController::class, 'handleCollectionNotification']);
