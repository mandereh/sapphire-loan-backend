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
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LoanController extends Controller
{
    //

    public function listLoans(Request $request){
        $loans = new Loan();

        if(!$request->user()->hasPermissionTo('view-all-loans')){
            $loans = $loans->where(function($q){
                $q->where('reffered_by_id', auth()->id())
                            ->orWhere('relationship_manager_id', auth()->id());
            });
        }

        if($request->filterStatus){
            $loans = $loans->where('status', $request->filterStatus);
        }

        if($request->filterLoanType){
            $loans = $loans->whereHas('loan_type_id', $request->filterLoanType);
        }

        $loans = $loans->with('user')->with('loanType')->with('state')->with('referrer')->latest()->paginate(10)->withQueryString();

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

        $loan->state_id = $request->state;

        $loan->zipcode = $request->zipcode;

        $loan->salary_account_number = $request->account_number;

        $loan->bank_code = $request->bank_code;

        $allBanks = (new GiroService())->getBanks('system');
        
        $result = array_filter($allBanks['data'], function($item) use($loan){
            return $item['bankCode'] == $loan->bank_code;
        });

        if($result){
            $key = array_key_first($result);
            if($key){
                $loan->salary_bank = $result[$key]['name'];
            }
        }

        $referrer = User::where('refferal_code', $request->reffered_by)->first();

        $loan->reffered_by_id = $referrer->id;

        $loan->state_id = $request->state;

        $loan->product_id = $request->product_id;

        $loan->reffered_by_id = $request->reffered_by;

        $product = Product::find($loan->product_id);

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

    public function verificationAffordability(Request $request, Loan $loan){
        $remitaService = new  RemitaService();

        $data = [
            'authorisationCode' => '',
            'firstName' => $loan->user->first_name,
            'lastName' => $loan->user->last_name,
            'middleName' => '',
            'accountNumber' => $loan->salary_account_number,
            'bankCode' => $loan->bank_code,
            'bvn' => $loan->user->bvn,
            'authorisationChannel' => ''
        ];

        $remitaResponse = $remitaService->getSalaryHistory($data);

        if($remitaResponse && $remitaResponse['responseCode'] == "00" && $remitaResponse['hasData']){
            $validityCheck = $loan->validityCheck($remitaResponse);
            $data = [
                'remitaSearchData' =>  $remitaResponse['data'],
                'remitaLoanData' =>  $remitaResponse['loanHistoryDetails'],
                'affordabilityCheckData' => [
                    'amount' => $loan->amount,
                    'tenor' => $loan->tenor,
                    'monthlyRepayment' => $validityCheck['monthlyRepayment'],
                    'otherDeductions' => 0,
                    'netPay' => $validityCheck['averageNetPay'],
                    'remitaLoan' => 0,
                    'disposableIncome' => $validityCheck['disposableIncome'],
                    'customerQualificationStatus' => $validityCheck['offerAmount'] > 0
                ]
            ];

            $resp = [
                'status_code' => '00',
                'message' => "Verification Affordability check successful",
                'data' => $data
            ];
    
            $statusCode = 200;
    
            return response($resp, $statusCode);
        }else{
            $resp = [
            'status_code' => '50',
            'message' => "Verification Affordability check failed",
        ];

        $statusCode = 500;

        return response($resp, $statusCode);
    }

        

        
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

    public function rejectLoan($loan){
        $this->authorize('approve', $loan);

        $loan->status = Status::REJECTED;

        $loan->save();

        $resp = [
            'status_code' => '00',
            'message' => "Loan rejected successfully",
            'data' => $loan->with('user')->with('loanType')->with('state')->with('product')->with('referrer')->with('relationshipManager')->firstOrFail()
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
            'data' => $loan->with('user')->with('loanType')->with('state')->with('product')->with('referrer')->with('relationshipManager')->firstOrFail()
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
