<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class digisignWebhookController extends Controller
{
    public function webhook(Request $request)
    {
        Log::debug("Digisign Webhook", $request->all());

        if(isset($request->type)){
            
        }

        return response()->json(['success' => true],200);
    }
}
