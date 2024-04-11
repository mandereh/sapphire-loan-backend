<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\ExternalServices\GiroService;
use App\ExternalServices\RemitaService;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class digisignWebhookController extends Controller
{
    public function webhook(Request $request)
    {
        Log::debug("Digisign Webhook", $request->all());

        if(isset($request->type) && $request->type == 'document.sent'){
            $publicId = $request->event_data['request']['public_id'];
            $link = $request->event_data['link'];

            $loan = Loan::where('document_id', $publicId)->firstOrFail();

            $loan->document_link = $link;

            $loan->save();
        }

        if(isset($request->type) && $request->type == 'document.completed'){
            $publicId = $request->event_data['request']['public_id'];

            $loan = Loan::where('document_id', $publicId)->firstOrFail();

            //setup deduction
            $remitaService = new RemitaService();

            $acc = app()->environment('local') || app()->environment('staging') ?  '0235012284' : $loan->salary_account_number;

            $bankCode = app()->environment('local') || app()->environment('staging') ?  '023' : $loan->bankCode;

            $remitaResponse =  $remitaService->loanDisburstmentNotification([
                "customerId" => $loan->remita_customer_id,
                "phoneNumber" => $loan->user->phone_number,
                "accountNumber" => $acc,
                "currency" => "NGN",
                "loanAmount" => $loan->amount,
                "collectionAmount" => round($loan->total_repayment_amount / $loan->tenor),
                "disbursementDate" => now()->toDateString(),
                "collectionDate" => now()->addMonthsNoOverflow()->toDateString(),
                "totalCollectionAmount" => round($loan->total_repayment_amount, 2),
                "numberOfRepayments" => $loan->tenor,
                "bankCode" => $bankCode
            ]);

            if($remitaResponse && $remitaResponse['responseCode'] == "00"){
                if($remitaResponse['hasData']){
                    //process disbursement
                    $loan->mandate_reference = $remitaResponse['data']['mandateReference'];

                    $loan->status = Status::PENDING_DISBURSEMENT;

                    $loan->save();

                    $giroService = new GiroService();

                    $giroResponse = $giroService->fundTransfer('callBack', $loan->salary_account_number, $loan->bankCode, config('services.giro.source_account'), $loan->amount, "Disbursement of loan to {$loan->user->phone_number} for {$loan->reference}", 'LD-'.$loan->reference);

                    if(isset($giroResponse['meta']) && $giroResponse['meta']['statusCode'] == 200 && $giroResponse['meta']['success']){
                        $loan->status = Status::DISBURSED;

                        $loan->save();
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

        return response()->json(['success' => true],200);
    }
}
