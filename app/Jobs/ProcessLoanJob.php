<?php

namespace App\Jobs;

use App\Constants\Status;
use App\ExternalServices\DigisignService;
use App\ExternalServices\RemitaService;
use App\Models\Loan;
use App\Models\ScheduledDeduction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessLoanJob implements ShouldQueue
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
        //Check Remita
        $remitaService = new  RemitaService();

        $remitaResponse = $remitaService->getSalaryHistory([]);

        $loan = $this->loan;
        
        if($remitaResponse && $remitaResponse['responseCode'] == "00"){
            if($remitaResponse['hasData']){
                //Do Assessment
                $offer = $loan->calculateOffer($remitaResponse);

                dump($offer);

                $loan->remita_customer_id = $remitaResponse['data']['customerId'];

                if($offer >= $loan->amount){
                    $loan->rate = $loan->loanType->rate;

                    $loan->total_interest = $loan->amount * $loan->rate * $loan->tenor/100;
                        
                    $loan->total_repayment_amount = $loan->amount + $loan->total_interest;

                    $loan->balance = $loan->total_repayment_amount;
    
                    $loan->approved_amount = $loan->amount;

                    $loan->status = Status::APPROVED;
                        
                    $loan->save();

                    $tenor = $loan->tenor;
                    $monthlyRepayment = round($loan->total_repayment_amount / $loan->tenor);
                    $monthsToAdd = 1;
                    while($tenor > 0){
                        ScheduledDeduction::create([
                            'loan_id' => $loan->id,
                            'balance' => $monthlyRepayment,
                            'due_date' => today()->addMonths($monthsToAdd)
                        ]);
                        $monthsToAdd;
                        $tenor--;
                    }

                    if($loan->user->email){
                        $digiSign = new DigisignService();
    
                        $digisignResponse = $digiSign->transformTemplate($loan);
    
                        if(isset($digisignResponse['data']['status']) && ($digisignResponse['data']['status'] == 'success' || $digisignResponse['data']['status'] == 'pending')){
                            $loan->document_id = $digisignResponse['data']['public_id'];
                            $loan->status = Status::PENDING_CONFIRMATION;
                        }
                        $loan->save();    
                    }else{
                        $loan->status = Status::PENDING_CONFIRMATION;
                        $loan->save();
                    }
    
                }else{
                    $loan->status = Status::REJECTED;

                    $loan->save();
                }

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
}
