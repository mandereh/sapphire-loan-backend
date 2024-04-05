<?php

namespace App\ExternalServices;

use App\Models\RequestLog;
use Illuminate\Support\Facades\Http;

class GiroService
{
    protected String $secretKey;
    protected String $baseUrl;


    public function __construct()
    {
        $this->baseUrl = config('services.giro.base_uri');
        $this->secretKey = config('services.giro.api_key');
    }

    public function getBanks(String $source)
    {
        $endpoint = 'bank-accounts/banks';

        $url = $this->baseUrl . '/' . $endpoint;

        $request_log = new RequestLog();

        $request_log->request_type = "post";
        $request_log->narration = 'Getting list of banks';
        $request_log->source = $source;
        $request_log->end_point = $url;
        $request_log->tran_id = time();
        $request_log->request_payload = json_encode([]);


        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'x-giro-key' => $this->secretKey,
        ])->get($url);

        $request_log->response_payload = $response->body();
        $request_log->save();

        return json_decode($response->body(), true);
    }

    public function getBankAccounts(String $source)
    {
        $endpoint = 'bank-accounts';

        $url = $this->baseUrl . '/' . $endpoint;

        $request_log = new RequestLog();

        $request_log->request_type = "post";
        $request_log->narration = 'Getting list of bank accounts';
        $request_log->source = $source;
        $request_log->end_point = $url;
        $request_log->tran_id = time();
        $request_log->request_payload = json_encode([]);


        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'x-giro-key' => $this->secretKey,
        ])->get($url);

        $request_log->response_payload = $response->body();
        $request_log->save();

        return json_decode($response->body(), true);
    }

    public function getBankAccount(String $source, String $accountId)
    {
        $endpoint = 'bank-accounts';

        $url = $this->baseUrl . '/' . $endpoint . '/' . $accountId;

        $request_log = new RequestLog();

        $request_log->request_type = "post";
        $request_log->narration = "Getting details for bank account $accountId";
        $request_log->source = $source;
        $request_log->end_point = $url;
        $request_log->tran_id = time();
        $request_log->request_payload = json_encode([]);


        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'x-giro-key' => $this->secretKey,
        ])->get($url);

        $request_log->response_payload = $response->body();
        $request_log->save();

        return json_decode($response->body(), true);
    }

    public function validateBankAccount(String $source, String $accountNumber, String $bankCode)
    {
        $endpoint = 'bank-accounts/validate';

        $url = $this->baseUrl . '/' . $endpoint;

        $request_log = new RequestLog();

        $requestData = [
            'accountNumber' => $accountNumber,
            'bankCode' => $bankCode
        ];

        $request_log->request_type = "post";
        $request_log->narration = "Validation account number $accountNumber";
        $request_log->source = $source;
        $request_log->end_point = $url;
        $request_log->tran_id = time();
        $request_log->request_payload = json_encode($requestData);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'x-giro-key' => $this->secretKey,
        ])->post($url, $requestData);

        $request_log->response_payload = $response->body();
        $request_log->save();

        return json_decode($response->body(), true);
    }

    public function fundTransfer(String $source, String $accountNumber, String $bankCode, String $sourceAccount, float $amount, String $narration, String $reference)
    {
        $endpoint = 'virtual-accounts/transfer';

        $url = $this->baseUrl . '/' . $endpoint;

        $request_log = new RequestLog();

        $requestData = [
            "accountNumber" => $accountNumber,
            "bankCode" => $bankCode,
            "sourceAccount" => $source,
            "amount" => $amount,
            "narration" => $narration,
            "reference" => $reference
        ];

        $request_log->request_type = "post";
        $request_log->narration = $narration;
        $request_log->source = $source;
        $request_log->end_point = $url;
        $request_log->tran_id = time();
        $request_log->request_payload = json_encode($requestData);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'x-giro-key' => $this->secretKey,
        ])->post($url, $requestData);

        $request_log->response_payload = $response->body();
        $request_log->save();

        return json_decode($response->body(), true);
    }
}
