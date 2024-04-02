<?php

namespace App\ExternalServices;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Ramsey\Uuid\Uuid;

class RemitaService
{
    protected $client;

    public $baseUri;
    public $username;
    public $password;
    public $accessToken;
    public $tokenExpiresAt;
    public $apiToken;
    public $apiKey;
    public $authorization;
    public $merchantId;
    public $requestId;
    public $apiHash;



    public function __construct()
    {
        $this->client = new Client();
        $this->baseUri = config('services.remita.base_uri');
        $this->username = config('services.remita.username');
        $this->password = config('services.remita.password');
        $this->tokenExpiresAt = $this->requestAccessToken();
        $this->accessToken = $this->getAccessToken();


        $this->apiKey = 'Q1dHREVNTzEyMzR8Q1dHREVNTw==';
        $this->apiToken = 'SGlQekNzMEdMbjhlRUZsUzJCWk5saDB6SU14Zk15djR4WmkxaUpDTll6bGIxRCs4UkVvaGhnPT0=';
        $this->merchantId = '27768931';
        $this->requestId = uuid_create();
        $this->apiHash = $this->generateConsumerToken();
        $this->authorization = $this->constructAuthorization();

    }

    public function getAccessToken()
    {
        if (!$this->accessToken || $this->tokenExpiresAt <= now()){
            $this->requestAccessToken();
        }
        return $this->accessToken;
    }

    private function requestAccessToken()
    {
        try {
            $response = $this->client->post($this->baseUri."/uaasvc/uaa/token",[
                'json' => [
                    'grant_type' => 'password',
                    'username' => $this->username,
                    'password' => $this->password,

                ],
            ]);
            $data = json_decode($response->getBody()->getContents(),true);
            $this->accessToken = $data['data'][0]['accessToken'];
            $this->tokenExpiresAt = now()->addSeconds($data['data'][0]['expiresIn']);
        }catch (GuzzleException $e){
            throw $e;
        }
    }


    private function generateConsumerToken()
    {
        $concantenatedString = trim($this->apiKey . $this->requestId . $this->apiToken);
        return hash('sha512', $concantenatedString);
    }

    public function constructAuthorization()
    {
        return "remitaConsumerKey={$this->apiKey},remitaConsumerToken={$this->apiHash}";
    }

    private function makeRequest($method, $uri, $headers = [], $data = [])
    {
        $cacheKey = md5($uri.json_encode($data));
        $cacheDuration = now()->addMinutes(60);

        if (Cache::has($cacheKey)){
            return Cache::get($cacheKey);
        }

        try {
            $response = $this->client->request($method, $uri, [
                'headers' => $headers,
                'json' => $data
            ]);
            $responseData = json_decode($response->getBody()->getContents(), true);
            Cache::put($cacheKey, $responseData, $cacheDuration);
            return $responseData;
        } catch (GuzzleException $e) {
            // Handle exception
            throw $e;
        }
    }

    public function getSalaryHistory($data = [])
    {
        $headers = [
          'Content-Type' => 'application/json',
            'API_KEY' => $this->apiKey,
            'MERCHANT_ID' => $this->merchantId,
            'REQUEST_ID' => $this->requestId,
            'AUTHORIZATION' => $this->authorization,
            'Authorization' => $this->accessToken,
        ];

        if(app()->environment('local')){
            return $this->dummyData();
        }
        return $this->makeRequest('POST',$this->baseUri."/loansvc/data/api/v2/payday/salary/history/provideCustomerDetails",$headers,$data);

    }

    public function loanDisburstmentNotification($data = [])
    {
        $headers = [
          'Content-Type' => 'application/json',
            'API_KEY' => $this->apiKey,
            'MERCHANT_ID' => $this->merchantId,
            'REQUEST_ID' => $this->requestId,
            'AUTHORIZATION' => $this->authorization,
            'Authorization' => $this->accessToken,
        ];

        return $this->makeRequest('POST',"{$this->baseUri}/loansvc/data/api/v2/payday/post/loan",$headers,$data);
    }

    public function mandateHistory($data = [])
    {
        $headers = [
            'Content-Type' => 'application/json',
            'API_KEY' => $this->apiKey,
            'MERCHANT_ID' => $this->merchantId,
            'REQUEST_ID' => $this->requestId,
            'AUTHORIZATION' => $this->authorization,
            'Authorization' => $this->accessToken,
        ];

        return $this->makeRequest('POST',"{$this->baseUri}/loansvc/data/api/v2/payday/loan/payment/history",$headers,$data);
    }

    public function stopLoanCollection($data = [])
    {
        $headers = [
            'Content-Type' => 'application/json',
            'API_KEY' => $this->apiKey,
            'MERCHANT_ID' => $this->merchantId,
            'REQUEST_ID' => $this->requestId,
            'AUTHORIZATION' => $this->authorization,
            'Authorization' => $this->accessToken,
        ];

        return $this->makeRequest('POST',"{$this->baseUri}/loansvc/data/api/v2/payday/stop/loan",$headers,$data);
    }


    private function dummyData(){
        $monthlyPay = round(mt_rand(30000, 300000), -3);
        $loanLength = mt_rand(1, 3);
        $repaymentAmount = round(mt_rand(30000, 200000), -3);

        $responseData = [
            "status" => "success",
            "hasData" => true,
            "responseId" => "1633886042479/1633886042479",
            "responseDate" => "10-10-2021 17:14:03+0000",
            "requestDate" => "10-10-2021 17:14:02+0000",
            "responseCode" => "00",
            "responseMsg" => "SUCCESS",
            "data" => [
              "customerId" => "1366",
              "accountNumber" => "5012284010",
              "bankCode" => "023",
              "bvn" => null,
              "companyName" => "National Youth Secrvice Corps",
              "customerName" => "Teresa Stoker",
              "category" => null,
              "firstPaymentDate" => "10-08-2020 00:00:00+0000",
              "salaryCount" => "6",
              "salaryPaymentDetails" => [
                [
                  "paymentDate" => "25-06-2021 13:33:46+0000",
                  "amount" => $repaymentAmount,
                  "accountNumber" => "5012284010",
                  "bankCode" => "023"
                ],
                [
                  "paymentDate" => "25-05-2021 13:33:46+0000",
                  "amount" => $repaymentAmount,
                  "accountNumber" => "5012284010",
                  "bankCode" => "023"
                ],
                [
                  "paymentDate" => "25-04-2021 13:33:46+0000",
                  "amount" => $repaymentAmount,
                  "accountNumber" => "5012284010",
                  "bankCode" => "023"
                ],
                [
                  "paymentDate" => "25-03-2021 13:33:46+0000",
                  "amount" => $repaymentAmount,
                  "accountNumber" => "5012284010",
                  "bankCode" => "023"
                ],
                [
                  "paymentDate" => "25-02-2021 13:33:46+0000",
                  "amount" => $repaymentAmount,
                  "accountNumber" => "5012284010",
                  "bankCode" => "023"
                ],
                [
                  "paymentDate" => "25-01-2021 13:33:46+0000",
                  "amount" => $repaymentAmount,
                  "accountNumber" => "5012284010",
                  "bankCode" => "023"
                ]
              ],
              "originalCustomerId" => "1366"
            ]
        ];

        $times = $loanLength;
        while($times > 0){
            $percentPayed = mt_rand(0.05,0.9);
            $percentPayed = $repaymentAmount * $percentPayed;
            $responseData['loanHistoryDetails'] = [
                "loanProvider" => "*******",
                "loanAmount" => $repaymentAmount * 80/100,
                "outstandingAmount" => $repaymentAmount - $percentPayed,
                "loanDisbursementDate" => "19-08-2021 00:00:00+0000",
                "status" => "NEW",
                "repaymentAmount" => $repaymentAmount,
                "repaymentFreq" => "MONTHLY"
            ];
            $times--;
        }

        return $responseData;
    }





}