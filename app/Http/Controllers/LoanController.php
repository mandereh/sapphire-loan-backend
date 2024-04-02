<?php

namespace App\Http\Controllers;

use App\ExternalServices\RemitaService;
use App\Http\Requests\ApplyRequest;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    //

    public function apply(ApplyRequest $request){
        //Check Remita
        $remitaService = new  RemitaService();

        $remitaResponse = $remitaService;

        //Do Assessment



        //Create offer letter PDF

        //Email Offer with link to digisign else Email no offer available
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
}
