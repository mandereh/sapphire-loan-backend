<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\PaymentMethod;
use App\Models\Repayment;
use App\Models\ScheduledDeduction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RepaymentController extends Controller
{
    //
    public function handleCollectionNotification(Request $request)
    {
        //Check that reference does not exist in repayments table already

        //fetch loan using mandate reference

        //Reduce loan balance and update status to completed if balance is less than or equal to zero

        //Update the scheduled deductions based on amounts paid for this loan reducing the balances

        $this->acknowledgeCollectionNotification($request->all());
        return response()->json(['message'=>'Webhook received successfully'], 200);
    }


    private function acknowledgeCollectionNotification($all)
    {

        if (Repayment::where('reference',$all['data']['mandateReference'])->exists()){
            return response()->json(['message'=>'Repayment already exists'], 400);
        }
        $loan = Loan::where('mandate_reference',$all['data']['mandateReference'])->first();
        $loan->balance = $loan->balance - $all['data']['amount'];
        $loan->status = $loan->balance <= 0 ? 'completed' : $loan->status;
        $loan->save();

        $scheduledDeduction = new ScheduledDeduction();
        $scheduledDeduction->loan_id = $loan->id;
        $scheduledDeduction->balance = $scheduledDeduction->balance - $all['data']['amount'];
        $scheduledDeduction->due_date = now()->format('Y-m-d H:i:s');
        $scheduledDeduction->save();

        Repayment::create([
            'loan_id'=>$loan->id,
            'amount'=>$all['data']['amount'],
            'reference'=>$all['data']['mandateReference'],
            'payment_method_id'=> 4
        ]);

    }

    //Search by LoanID or payment method (Remita or Transfer)
    public function viewRepayments(Request $request){

        $validator = Validator::make($request->all(), [
            'loanId' => 'required|numeric|exists:loans,id',
            'paymentMethodId' => 'required|numeric|exists:payment_methods,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $loanId = $request->input('loanId');
        $paymentMethodId = $request->input('paymentMethodId');

        $query = Repayment::query();

        if($loanId){
            $query->where('loan_id', $loanId);
        }
        if ($paymentMethodId){
            $query->where('payment_method',$paymentMethodId);
        }
        $repayments = $query->get();
        return response()->json([
            'message' => 'Retrieved repayments successfully',
            'data'=>$repayments,
        ], 200);
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
    public function createManualRepayment(Request $request){

        $validator = Validator::make($request->all(), [
            'loanId'=>'required|exists:loans,id',
            'amount'=>'required|numeric',
            'reference'=>'required|string',
            'paymentMethodId'=>'required|exists:payment_methods,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }


        //Check that reference does not exist in repayments table already
        if (Repayment::where('reference', $request->input('reference'))->exists()) {
            return response()->json(['message'=>'Repayment already exists'], 400);
        }
        //fetch loan using mandate reference
        $loan = Loan::find($request->input('loanId'));
        //Reduce loan balance and update status to completed if balance is less than or equal to zero
        $loan->balance -= $request->input('amount');
        $loan->status = $loan->balance <= 0 ? 'completed' : $loan->status;
        $loan->save();
        //Update the scheduled deductions based on amounts paid for this loan reducing the balances
        $scheduledDeduction = new ScheduledDeduction();
        $scheduledDeduction->loanId = $request->input('loanId');
        $scheduledDeduction->balance = $scheduledDeduction->balance - $request->input('amount');
        $scheduledDeduction->due_date = now()->format('d-m-Y h:i:s+0000');
        $scheduledDeduction->save();
        //Save repayment
        $repayment = Repayment::create([
            'loan_id'=>$loan->id,
            'amount'=>$request->input('amount'),
            'reference'=>$request->input('reference'),
            'payment_method_id'=>$request->input('paymentMethodId')
        ]);
        return response()->json([
            'message'=>'Repayment created successfully',
            'data'=>[
            'repayment' => $repayment,
            'loan'=>$loan,
            'scheduledDeduction'=>$scheduledDeduction
            ]
        ], 201);



    }
}
