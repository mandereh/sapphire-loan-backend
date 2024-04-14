<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ProductController;
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
Route::post('/remita/getSalaryHistoryByPhonenumber',[\App\Http\Controllers\ApiTestController::class,'getRemitaSalaryHistoryByPhonenumber']);
Route::post('/remita/loanDisbursementNotification',[\App\Http\Controllers\ApiTestController::class,'loanDisbursementNotificationController']);
Route::post('/remita/mandateHistory',[\App\Http\Controllers\ApiTestController::class,'mandateHistoryController']);
Route::post('/remita/stopLoanCollection',[\App\Http\Controllers\ApiTestController::class,'stopLoanCollectionController']);
Route::put('/digisign/transformTemplate',[\App\Http\Controllers\ApiTestController::class,'transformTemplate']);

Route::post('/user/login', [UserController::class, 'token'])->name('api.login');

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

    Route::get('/loans', [LoanController::class, 'listLoans'])->name('loans.list');

    Route::get('/loans/{loan}/details', [LoanController::class, 'details'])->name('loans.details');

    Route::get('/products', [ProductController::class, 'index'])->name('products');

    Route::post('/products/new', [ProductController::class, 'store'])->name('products.store');

    Route::put('/products/{product}/update', [ProductController::class, 'update'])->name('products.update');

    Route::get('/products/{product}/deactivate', [ProductController::class, 'deactivate'])->name('products.deactivate');

    Route::post('/loans/authorization/{loan}/upload', [LoanController::class, 'uploadAuthorization'])->name('loan.authorization.upload');

    Route::put('/loans/{loan}/approve', [LoanController::class, 'manualApproval'])->name('loan.approval');
});

Route::get('/states', [LoanController::class, 'listStates'])->name('states.list');
Route::get('/loanTypes', [LoanController::class, 'listLoanTypes'])->name('loanTypes.list');
// Route::get('/organizations', [LoanController::class, 'listOrganizations'])->name('organizations.list');
Route::get('/products/list', [ProductController::class, 'availableProducts'])->name('products.list');
Route::get('/banks', [LoanController::class, 'listBanks'])->name('banks.list');
Route::post('/banks/account/validate', [LoanController::class, 'validateBankAccount'])->name('banks.account.validate');

Route::post('/user/register', [UserController::class, 'register'])->name('register');

Route::post('/loan/apply', [LoanController::class, 'apply'])->name('loan.apply');

Route::post('/collection-notification', [RepaymentController::class, 'handleCollectionNotification']);

//Repayments
Route::post('/repayments/createManualRepayment', [RepaymentController::class, 'createManualRepayment']);
Route::post('/repayments/listPaymentMethods', [RepaymentController::class, 'listPaymentMethods']);
Route::post('/repayments/viewRepayments', [RepaymentController::class, 'viewRepayments']);
