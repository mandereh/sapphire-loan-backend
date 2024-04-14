<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\ExternalServices\GiroService;
use App\ExternalServices\RemitaService;
use App\Http\Requests\Admin\UpdateLoanRequest;
use App\Http\Requests\ApplyRequest;
use App\Http\Requests\UpdateLoanTypeRequest;
use App\Http\Requests\ValidateBankAccountRequest;
use App\Jobs\DisburseLoan;
use App\Jobs\ProcessLoanJob;
use App\Models\Loan;
use App\Models\LoanType;
use App\Models\Organization;
use App\Models\Product;
use App\Models\State;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    //

    public function listLoans(Request $request){
        $loans = new Loan();

        if(!$request->user()->hasPermissionTo('view-all-loans')){
            $loans = $loans->where('reffered_by_id', auth()->id())
                            ->orWhere('relationship_manager_id', auth()->id());
        }

        if($request->filterStatus){
            $loans = $loans->where('status', $request->statusFilter);
        }

        if($request->filterLoanType){
            $loans = $loans->whereHas('loan_type_id', $request->filterLoanType);
        }

        $loans = $loans->paginate();

        $resp = [
            'status_code' => '00',
            'message' => "Retrieved loans Successfully",
            'data' => $loans
        ];
        

        $statusCode = 200;

        return response($resp, $statusCode);
    }

    public function apply(ApplyRequest $request){
        $loan = new Loan();

        $loan->tenor = $request->tenor;

        $loan->user_id = $request->user_id;

        $loan->loan_type_id = $request->loan_type;

        $loan->organization_name = $request->organization;

        $loan->address = $request->address;

        $loan->city = $request->city;

        $loan->zipcode = $request->zipcode;

        $loan->salary_account_number = $request->account_number;

        $loan->bank_code = $request->bank_code;

        $loan->state_id = $request->state;

        $loan->product_id = $request->product_id;

        $loan->reffered_by_id = $request->reffered_by;

        $product = Product::findOrFail($loan->product_id);

        if($product && $loan->loan_type_id == 2){
            $loan->amount = $product->price;
        }else{
            $loan->amount = $request->amount;
        }

        $loan->reference = $loan->getUniqueReference();

        $loan->status = Status::PROCESSING;

        $loan->save();

        // $remitaService = new  RemitaService();

        // $remitaResponse = $remitaService->getSalaryHistory([]);

        // return $loan->calculateOffer($remitaResponse);

        ProcessLoanJob::dispatch($loan)
                            ->onQueue('processLoans');

        $resp = [
            'status_code' => '00',
            'message' => "Loan submitted Successfully",
        ];

        $statusCode = 200;

        return response($resp, $statusCode);
    }

    public function listStates(){
        $resp = [
            'status_code' => '00',
            'message' => "States retrieved Successfully",
            'data' => State::all()
        ];

        $statusCode = 200;

        return response($resp, $statusCode);
    }

    public function listLoanTypes(){
        $resp = [
            'status_code' => '00',
            'message' => "Loan Types retrieved Successfully",
            'data' => LoanType::all()
        ];

        $statusCode = 200;

        return response($resp, $statusCode);
    }

    public function updateLoanType(UpdateLoanTypeRequest $request, LoanType $loanType){
        $loanType->update([
            'cute_name' => $request->cute_name,
            'active' => $request->active,
            'rate' => $request->rate,
            'fees' => $request->fees
        ]);

        $resp = [
            'status_code' => '00',
            'message' => "Loan Type updated Successfully",
            'data' => $loanType
        ];
        

        $statusCode = 200;

        return response($resp, $statusCode);
    }

    public function listOrganizations(){
        $resp = [
            'status_code' => '00',
            'message' => "Organizations retrieved Successfully",
            'data' => Organization::all()
        ];

        $statusCode = 200;

        return response($resp, $statusCode);
    }

    public function listBanks(){
        $giroService = new GiroService();

        $banksResponse = $giroService->getBanks('staffPortal');

        if(isset($banksResponse['meta']) && $banksResponse['meta']['statusCode'] == 200 && $banksResponse['meta']['success']){
            $resp = [
                'status_code' => '00',
                'message' => "Banks retrieved Successfully",
                'data' => $banksResponse['data']
            ];
    
            $statusCode = 200;
        }else{
            $resp = [
                'status_code' => '50',
                'message' => "Something went wrong",
            ];
    
            $statusCode = 500;
        }
        return response($resp, $statusCode);
    }

    public function validateBankAccount(ValidateBankAccountRequest $request){
        $giroService = new GiroService();

        $validateAccountResponse = $giroService->validateBankAccount('staffPortal', $request->account_number, $request->bank_code);

        if(isset($validateAccountResponse['meta']) && $validateAccountResponse['meta']['statusCode'] == 200 && $validateAccountResponse['meta']['success']){
            $resp = [
                'status_code' => '00',
                'message' => "Account Details retrieved Successfully",
                'data' => $validateAccountResponse['data']
            ];
    
            $statusCode = 200;
        }else{
            $resp = [
                'status_code' => '50',
                'message' => "Something went wrong! Failed to validate account",
            ];
    
            $statusCode = 500;
        }
        return response($resp, $statusCode);
    }

    //Loan Officer updates customer loan uploading -voice recording- and other information
    public function uploadAuthorization(UpdateLoanRequest $request, Loan $loan){

        $this->authorize('update', $loan);

        $filePath = $request->file('authorization_file')->store();
        
        $filePath = explode('/', $filePath);

        array_shift($filePath);

        $loan->authorization_file = implode('/', $filePath);

        $loan->status = Status::PENDING_APPROVAL;

        $loan->save();

        $resp = [
            'status_code' => '00',
            'message' => "Authorization successfully uploaded",
        ];

        $statusCode = 200;
        
        return response($resp, $statusCode);
    }

    public function details($loan){
        $resp = [
            'status_code' => '00',
            'message' => "Loan details retrieved",
            'data' => Loan::where('id', $loan)->with('user')->with('loanType')->with('state')->with('product')->with('referrer')->with('relationshipManager')->firstOrFail()
        ];

        $statusCode = 200;

        return response($resp, $statusCode);
    }

    //Initiated by Risk
    public function manualApproval(Request $request, Loan $loan){
        $this->authorize('approve', $loan);

        $loan->status = Status::APPROVED;

        $loan->approved_by_id = auth()->id();

        $loan->save();

        DisburseLoan::dispatch($loan)
                        ->onQueue('processLoans');

        $resp = [
            'status_code' => '00',
            'message' => "Loan has been approved and pushed for disbursement",
        ];

        $statusCode = 200;
        
        return response($resp, $statusCode);
    }

    //Manual disbursement initiated by finance
    // public function manualDisbursement(){

    // }

    // View All loans with ability to search by loanID, customer name, customer organization, customer mobile number
    public function viewAllLoans(){

    }

    //Return loans for the logged in account officer
    public function viewLoansByAccountOfficer(){

    }

    public function bvnValidation(){

    }
}
