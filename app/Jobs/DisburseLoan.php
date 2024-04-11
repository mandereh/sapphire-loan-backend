<?php

namespace App\Jobs;

use App\Constants\Status;
use App\ExternalServices\GiroService;
use App\ExternalServices\RemitaService;
use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DisburseLoan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $loan;

    /**
     * Create a new job instance.
     */
    public function __construct(Loan $loan)
    {
        //
        $this->loan = $loan;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $loan = $this->loan;
        //setup deduction
        $remitaService = new RemitaService();

        $acc = app()->environment(['local', 'staging']) ?  '0235012284' : $loan->salary_account_number;

        $bankCode = app()->environment(['local', 'staging']) ?  '023' : $loan->bankCode;

        // return now()->startOfMonth()->format('d-m-Y h:i:s+1000');

        $amount = app()->environment(['local', 'staging']) ?  ($loan->amount <= 20000 ? $loan->amount : 20000) : $loan->bankCode;

        $data = $data = [
            "customerId" => $loan->remita_customer_id,
            "phoneNumber" => $loan->user->phone_number,
            "accountNumber" => $acc,
            "currency" => "NGN",
            "loanAmount" => $amount,
            "collectionAmount" => round($loan->total_repayment_amount / $loan->tenor),
            "disbursementDate" => now()->format('d-m-Y h:i:s+0000'),
            "collectionDate" => now()->addMonthsNoOverflow()->format('d-m-Y h:i:s+0000'),
            "totalCollectionAmount" => $loan->total_repayment_amount,
            "numberOfRepayments" => $loan->tenor,
            "bankCode" => $bankCode
        ];

        $remitaResponse =  $remitaService->loanDisburstmentNotification($data);

        if($remitaResponse && $remitaResponse['responseCode'] == "00"){
            if($remitaResponse['hasData']){
                //process disbursement
                $loan->mandate_reference = $remitaResponse['data']['mandateReference'];

                $loan->status = Status::PENDING_DISBURSEMENT;

                $loan->approved_by_id = 0;

                $loan->save();

                if($loan->loan_type_id != '2'){
                    $giroService = new GiroService();

                    // return ['message' => 'Akamu'];

                    $giroResponse = $giroService->fundTransfer('callBack', $loan->salary_account_number, $loan->bank_code, config('services.giro.source_account'), $loan->amount, "Disbursement of loan to {$loan->user->phone_number} for {$loan->reference}", 'LD-'.$loan->reference);

                    if(isset($giroResponse['meta']) && $giroResponse['meta']['statusCode'] == 200 && $giroResponse['meta']['success']){
                        $loan->status = Status::DISBURSED;

                        $loan->save();
                    }
                }
                
            }else{
                $loan->status = Status::FAILED;

                $loan->failure_reason = 'Deduction setup could not be confirmed';
            }
        }else{
            $loan->status = Status::FAILED;

            $loan->failure_reason = 'Deduction setup failed';
        }
    }
}
