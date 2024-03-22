<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RemitaCollectionNotificationWebhookController extends Controller
{
    public function handleCollectionNotification(Request $request)
    {
        $this->acknowledgeCollectionNotification($request->all());
        return response()->json(['message'=>'Webhook recieved successfully'], 200);
    }


    private function acknowledgeCollectionNotification(array $all)
    {

    }
}
