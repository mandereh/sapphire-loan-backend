<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\RepaymentController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/user/register', [UserController::class, 'register']);

Route::post('/remita/getSalaryHistory',[\App\Http\Controllers\ApiTestController::class,'getRemitaSalaryHistory']);
Route::post('/remita/loanDisburstmentNotification',[\App\Http\Controllers\ApiTestController::class,'loanDisburstmentNotificationController']);
Route::post('/remita/mandateHistory',[\App\Http\Controllers\ApiTestController::class,'mandateHistoryController']);
Route::post('/remita/stopLoanCollection',[\App\Http\Controllers\ApiTestController::class,'stopLoanCollectionController']);

Route::post('/user/login', [UserController::class, 'token'])->name('login');

Route::get('/user', [UserController::class, 'userDetails'])->middleware('auth:sanctum');

Route::get('/user/details/ippis', [UserController::class, 'getUserByIppis'])->name('user.ippis');

Route::prefix('admin')->middleware('auth:sanctum')->group(function(){

    Route::get('/roles', [AdminController::class, 'listRoles']);

    Route::get('/permissions', [AdminController::class, 'listPermissions']);

    Route::post('/roles/create', [AdminController::class, 'createRole']);

    Route::post('/user/create', [AdminController::class, 'createAdmin']);

    Route::post('/leads/upload-lead', [LeadController::class, 'uploadLeads'])->name('leads.uploadLeads');
    
    Route::post('/leads/reassign-lead', [LeadController::class, 'reassignLead'])->name('leads.reassignLead');

    Route::post('/leads/delete-lead', [LeadController::class, 'deleteLeads'])->name('leads.deleteLeads');

    Route::get('/users/{user}/view-leads-by-Account-officer', [LeadController::class, 'viewLeadsByAccountOfficer'])->name('leads.viewLeadsByAccountOfficer');

    Route::get('/leads/view-all-leads', [LeadController::class, 'viewAllLeads'])->name('leads.viewAllLeads');
    
    Route::put('/loanTypes/{loanType}/update', [LoanController::class, 'updateLoanType'])->name('loanTypes.update');

});

Route::get('/states', [LoanController::class, 'listStates'])->name('states.list');
Route::get('/loanTypes', [LoanController::class, 'listLoanTypes'])->name('loanTypes.list');
Route::get('/organizations', [LoanController::class, 'listOrganizations'])->name('organizations.list');

Route::post('/collection-notification', [RepaymentController::class, 'handleCollectionNotification']);
