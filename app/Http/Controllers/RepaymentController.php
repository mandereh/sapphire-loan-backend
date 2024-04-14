<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class RepaymentController extends Controller
{
    //
    public function handleCollectionNotification(Request $request)
    {
        //Check that reference does not exist in  repayments table already

        //fetch loan using mandate reference

        //Reduce loan balance and update status to completed if balance is less than or equal to zero

        //Update the scheduled deductions based on amounts paid for this loan reducing the balances

        $this->acknowledgeCollectionNotification($request->all());
        return response()->json(['message'=>'Webhook received successfully'], 200);
    }


    private function acknowledgeCollectionNotification(array $all)
    {

    }

    //Search by LoanID or payment method (Remita or Transfer)
    public function viewRepayments(){

    }

    public function manualDeductionSetup(){
        // check if status is pending deduction

        // setup deduction on remita //CHECK DisburseLoan job for snippet
    }

    public function listPaymentMethods(){
        $response = [
            'status_code' => '00',
            'message' => "Retrieved payment methods Successfully",
            'data' => PaymentMethod::all()
        ];
        $statusCode = 200;
        return response($response, $statusCode);
    }

    // Finance can create manual repayment for repayments where notification didn't come through Remita
    public function createManualRepayment(){
        //Check that reference does not exist in  repayments table already

        //fetch loan using mandate reference

        //Reduce loan balance and update status to completed if balance is less than or equal to zero

        //Update the scheduled deductions based on amounts paid for this loan reducing the balances

    }
}
