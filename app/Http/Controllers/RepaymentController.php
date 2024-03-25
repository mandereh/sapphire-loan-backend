<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RepaymentController extends Controller
{
    //
    public function handleCollectionNotification(Request $request)
    {
        $this->acknowledgeCollectionNotification($request->all());
        return response()->json(['message'=>'Webhook recieved successfully'], 200);
    }


    private function acknowledgeCollectionNotification(array $all)
    {

    }

    //Search by LoanID or payment method (Remita or Transfer)
    public function viewRepayments(){

    }

    public function manualDeductionSetup(){

    }

    // Finance can create manual repayment for repayments where notification didn't come through Remita
    public function createManualRepayment(){

    }
}
