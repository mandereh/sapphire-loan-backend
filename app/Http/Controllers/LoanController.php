<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\ExternalServices\RemitaService;
use App\Http\Requests\ApplyRequest;
use App\Http\Requests\UpdateLoanTypeRequest;
use App\Models\Loan;
use App\Models\LoanType;
use App\Models\Organization;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class LoanController extends Controller
{
    //

    public function apply(ApplyRequest $request){
        //Check Remita
        $remitaService = new  RemitaService();

        $remitaResponse = $remitaService->getSalaryHistory([]);

        $loan = new Loan();

        $loan->tenor = $request->tenor;

        $loan->user_id = $request->user_id;

        $loan->loan_type_id = $request->loan_type;

        $loan->organization_id = $request->loan_type;

        $loan->organization_id = $request->loan_type;

        $loan->address = $request->address;

        $loan->city = $request->city;

        $loan->zipcode = $request->zipcode;

        $loan->salary_account_number = $request->account_number;

        $loan->bank_code = $request->bank_code;

        $loan->state_id = $request->state;

        $loan->state_id = $request->state;

        $loan->reffered_by_id = $request->reffered_by;

        $loan->amount = $request->amount;

        if($remitaResponse && $remitaResponse['responseCode'] == "00"){
            if($remitaResponse['hasData']){
                //Do Assessment
                $offer = $loan->calculateOffer($remitaResponse['data']);

                dd($offer);
                //Create offer letter PDF


                //Email Offer with link to digisign else Email no offer available
            }else{
                // no history for this
                $loan->status = Status::FAILED;

                $loan->failure_reason = 'No salary history';
            }
        }else{
            //Update to status failed

            //
            $loan->status = Status::FAILED;

            $loan->failure_reason = 'Error retrieving salary history';
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

    //Loan Officer updates customer loan uploading -voice recording- and other information
    public function updateLoan(){

    }

    //Initiated by Digisign
    public function acceptOffer(){
        // Setup deduction

        //Disburse via Giro
    }

    //Initiated by Risk
    public function manualApproval(){

    }

    //Manual disbursement initiated by finance
    public function manualDisbursement(){

    }

    // View All loans with ability to search by loanID, customer name, customer organization, customer mobile number
    public function viewAllLoans(){

    }

    //Return loans for the logged in account officer
    public function viewLoansByAccountOfficer(){

    }

    public function bvnValidation(){

    }

    public function digisignCallback(Request $request){
        //verify call back

        //save digisign files


        //setup deductions

        //disburse loan

        //s
    }
}
