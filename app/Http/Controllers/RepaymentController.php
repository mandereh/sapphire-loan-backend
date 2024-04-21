<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Models\Loan;
use App\Models\PaymentMethod;
use App\Models\Repayment;
use App\Models\ScheduledDeduction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RepaymentController extends Controller
{
    //
//    public function handleCollectionNotification(Request $request)
//    {
//        //Check that reference does not exist in repayments table already
//
//        //fetch loan using mandate reference
//
//        //Reduce loan balance and update status to completed if balance is less than or equal to zero
//
//        //Update the scheduled deductions based on amounts paid for this loan reducing the balances
//
//        $this->acknowledgeCollectionNotification($request->all());
//        return response()->json(['message'=>'Webhook received successfully'], 200);
//    }


    public function handleCollectionNotification(Request $request)
    {
        // Parse the webhook data
        $requestData = $request->all();

        // Check if the webhook data is empty
        if(empty($requestData)){
            return response()->json(['message' => 'Webhook data is empty'], 400);
        }
        // Check if the webhook data contains the 'data' key
        if(!array_key_exists('data', $requestData)){
            return response()->json(['message' => 'Webhook data does not contain the data key'], 400);
        }
        // Check if the webhook data contains the 'mandateReference' key
        if(!array_key_exists('mandateReference', $requestData['data'])){
            return response()->json(['message' => 'Webhook data does not contain the mandateReference key'], 400);
        }
        // Check if the webhook data contains the 'amount' key
        if(!array_key_exists('amount', $requestData['data'])){
            return response()->json(['message' => 'Webhook data does not contain the amount key'], 400);
        }
        // Check that reference does not exist in repayments table already
        if (Repayment::where('reference', $requestData['data']['mandateReference'])->exists()) {
            return response()->json(['message'=>'Repayment already exists'], 400);
        }

        // Fetch the loan using the mandate reference from the webhook data
        $loan = Loan::where('mandate_reference', $requestData['data']['mandateReference'])->first();

        // Get the total repayment amount from the webhook data
        $totalRepaymentAmount = $requestData['data']['amount'];

        // Reduce the loan balance by the total repayment amount
        $loan->balance -= $totalRepaymentAmount;

        // Update the loan status to 'completed' if the balance is less than or equal to zero
        $loan->status = $loan->balance <= 0 ? 'completed' : $loan->status;

        // Save the changes to the loan
        $loan->save();

        //Populate the repayment table
        Repayment::create([
            'loan_id' => $loan->id,
            'amount' => $totalRepaymentAmount,
            'reference' => $requestData['data']['mandateReference'],
            'payment_method_id' => 4
        ]);

        // Fetch the scheduled deductions for the loan
        $scheduledDeductions = ScheduledDeduction::where('loan_id', $loan->id)
            ->where('status', 'active')
            ->orWhere('balance', '>', 0)
            ->get();

        foreach($scheduledDeductions as $scheduledDeduction) {
            $balance = 0;
            $status = 'active';

            if($totalRepaymentAmount >= $scheduledDeduction->balance){
                $balance = 0;
                $status = 'completed';

                $actualSpent = $scheduledDeduction->balance;
            }else{
                $balance = $scheduledDeduction->balance - $totalRepaymentAmount;

                $actualSpent = $totalRepaymentAmount;
            }

            $scheduledDeduction->balance = $balance;
            $scheduledDeduction->status = $status;

            // Save the changes to the scheduled deduction
            $scheduledDeduction->save();

            $totalRepaymentAmount -= $actualSpent;

            if($totalRepaymentAmount <= 0){
                break;
            }
        }

        // Return a response
        return response()->json(['message' => 'Webhook handled successfully'], 200);
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
            $query->where('payment_method_id',$paymentMethodId);
        }
        $repayments = $query->get();
        return response()->json([
            'message' => 'Retrieved repayments successfully',
            'data'=>$repayments,
        ], 200);
    }

    public function manualDeductionSetup(Loan $loan){
        // check if status is pending deduction
        if ($loan->status === Status::PENDING_DISBURSEMENT) {
            $response = [
                'status_code' => '50',
                'message' => "Loan not pending disbursement.",
                'data'=>null
            ];
            return response($response, 500);
        }
        // setup deduction on remita //CHECK DisburseLoan job for snippet
        $giroService = new GiroService();
        $giroResponse = $giroService->fundTransfer('callBack', $loan->salary_account_number, $loan->bank_code, config('services.giro.source_account'), $loan->amount, "Disbursement of loan to {$loan->user->phone_number} for {$loan->reference}", 'LD-'.$loan->reference);
        if(isset($giroResponse['meta']) && $giroResponse['meta']['statusCode'] === 200 && $giroResponse['meta']['success']){
            $loan->status = Status::DISBURSED;
            $loan->save();
            $response = [
                'status_code' => '00',
                'message' => "Loan Deduction setup confirmed",
                'data'=>$loan
            ];
            return response($response, 201);
        }

        $loan->status = Status::FAILED;
        $loan->failure_reason = 'Deduction setup failed';

        $response = [
            'status_code' => '50',
            'message' => "Loan Deduction setup failed",
            'data'=>null
        ];
        return response($response, 500);

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

        //Save repayment
        $repayment = Repayment::create([
            'loan_id'=>$loan->id,
            'amount'=>$request->input('amount'),
            'reference'=>$request->input('reference'),
            'payment_method_id'=>$request->input('paymentMethodId')
        ]);

        // Fetch the scheduled deductions for the loan
        $scheduledDeductions = ScheduledDeduction::where('loan_id', $loan->id)
            ->where('status', 'active')
            ->orWhere('balance', '>', 0)
            ->get();
        //Update the scheduled deductions based on amounts paid for this loan reducing the balances
        foreach($scheduledDeductions as $scheduledDeduction) {
            $balance = 0;
            $status = 'active';

            $totalRepaymentAmount = $request->input('amount');

            if($totalRepaymentAmount >= $scheduledDeduction->balance){
                $balance = 0;
                $status = 'completed';

                $actualSpent = $scheduledDeduction->balance;
            }else{
                $balance = $scheduledDeduction->balance - $totalRepaymentAmount;

                $actualSpent = $totalRepaymentAmount;
            }

            $scheduledDeduction->balance = $balance;
            $scheduledDeduction->status = $status;

            // Save the changes to the scheduled deduction
            $scheduledDeduction->save();

            $totalRepaymentAmount -= $actualSpent;

            if($totalRepaymentAmount <= 0){
                break;
            }
        }

        return response()->json([
            'message'=>'Repayment created successfully',
            'data'=>[
            'repayment' => $repayment,
            'loan'=>$loan,
            'scheduledDeduction'=>$scheduledDeductions->toArray()
            ]
        ], 201);



    }




}
