<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class digisignWebhookController extends Controller
{
    public function webhook(Request $request)
    {
        dd($request->all());
        return response()->json(['success' => true],200);
    }
}
