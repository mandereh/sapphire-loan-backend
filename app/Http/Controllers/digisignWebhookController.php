<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\ExternalServices\GiroService;
use App\ExternalServices\RemitaService;
use App\Jobs\DisburseLoan;
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

        if(isset($request->type) && ($request->type == 'document.completed' || $request->type == 'document.signed')){

            // Log::debug('lala system', $request->type);
            $publicId = $request->event_data['request']['public_id'];

            $loan = Loan::where('document_id', $publicId)->firstOrFail();

            // Log::debug('lala system', $request->type);

            if($loan->status == Status::CONFIRMATION_SENT){
                DisburseLoan::dispatch($loan)
                                ->onQueue('processLoans');
            }else{
                return response()->json(['success' => false, 'message' => 'invalid request'],400);
            }
            
        }

        return response()->json(['success' => true],200);
    }
}
