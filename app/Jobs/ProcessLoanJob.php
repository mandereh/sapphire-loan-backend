<?php

namespace App\Jobs;

use App\Constants\Status;
use App\ExternalServices\RemitaService;
use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
}
