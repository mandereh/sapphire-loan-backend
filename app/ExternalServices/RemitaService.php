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
        if (!$this->accessToken || $this->tokenExpiresAt <= now()) {
            $this->requestAccessToken();
        }
        return $this->accessToken;
    }

    private function requestAccessToken()
    {
        try {
            $response = $this->client->post($this->baseUri . "/uaasvc/uaa/token", [
                'json' => [
                    'grant_type' => 'password',
                    'username' => $this->username,
                    'password' => $this->password,

                ],
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            $this->accessToken = $data['data'][0]['accessToken'];
            $this->tokenExpiresAt = now()->addSeconds($data['data'][0]['expiresIn']);
        } catch (GuzzleException $e) {
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
        $cacheKey = md5($uri . json_encode($data));
        $cacheDuration = now()->addMinutes(60);

        if (Cache::has($cacheKey)) {
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

    public function getSalaryHistory($requestData = [])
    {
        $headers = [
            'Content-Type' => 'application/json',
            'API_KEY' => $this->apiKey,
            'MERCHANT_ID' => $this->merchantId,
            'REQUEST_ID' => $this->requestId,
            'AUTHORIZATION' => $this->authorization,
            'Authorization' => $this->accessToken,
        ];
        $data = [
            'authorisationCode' => $requestData['authorisationCode'],
            'firstname' => $requestData['firstname'],
            'lastname' => $requestData['lastname'],
            'middlename' => $requestData['middlename'],
            'accountNumber' => $requestData['accountNumber'],
            'bankCode' => $requestData['bankCode'],
            'bvn' => $requestData['bvn'],
            'authorisationChannel' => $requestData['authorisationChannel']
        ];


        return $this->makeRequest('POST', $this->baseUri . "/loansvc/data/api/v2/payday/salary/history/provideCustomerDetails", $headers, $data);
    }

    public function loanDisburstmentNotification($requestData = [])
    {
        $headers = [
            'Content-Type' => 'application/json',
            'API_KEY' => $this->apiKey,
            'MERCHANT_ID' => $this->merchantId,
            'REQUEST_ID' => $this->requestId,
            'AUTHORIZATION' => $this->authorization,
            'Authorization' => $this->accessToken,
        ];
        $data = [
            "customerId" => $requestData["customerId"],
            "authorisationCode" => $requestData["authorisationCode"],
            "authorisationChannel" => $requestData["USSD"],
            "phoneNumber" => $requestData["phoneNumber"],
            "accountNumber" => $requestData["accountNumber"],
            "currency" => $requestData["NGN"],
            "loanAmount" => $requestData["loanAmount"],
            "collectionAmount" => $requestData["collectionAmount"],
            "dateOfDisbursement" => $requestData["disbursementDate"],
            "dateOfCollection" => $requestData["disbursementDate"],
            "totalCollectionAmount" => $requestData["totalCollectionAmount"],
            "numberOfRepayments" => $requestData["numberOfRepayments"],
            "bankCode" => $requestData["bankCode"]
        ];

        return $this->makeRequest('POST', "{$this->baseUri}/loansvc/data/api/v2/payday/post/loan", $headers, $data);
    }

    public function mandateHistory($requestData = [])
    {
        $headers = [
            'Content-Type' => 'application/json',
            'API_KEY' => $this->apiKey,
            'MERCHANT_ID' => $this->merchantId,
            'REQUEST_ID' => $this->requestId,
            'AUTHORIZATION' => $this->authorization,
            'Authorization' => $this->accessToken,
        ];
        $data = [
            "authorisationCode" => $requestData["authorisationCode"],
            "customerId" => $requestData["customerId"],
            "mandateRef" => $requestData["mandateReference"]
        ];

        return $this->makeRequest('POST', "{$this->baseUri}/loansvc/data/api/v2/payday/loan/payment/history", $headers, $data);
    }

    public function stopLoanCollection($requestData = [])
    {
        $headers = [
            'Content-Type' => 'application/json',
            'API_KEY' => $this->apiKey,
            'MERCHANT_ID' => $this->merchantId,
            'REQUEST_ID' => $this->requestId,
            'AUTHORIZATION' => $this->authorization,
            'Authorization' => $this->accessToken,
        ];
        $data = [
            "authorisationCode" => ["authorisationCode"],
            "customerId" => ["customerId"],
            "mandateReference" => ["mandateReference"]
        ];

        return $this->makeRequest('POST', "{$this->baseUri}/loansvc/data/api/v2/payday/stop/loan", $headers, $data);
    }
}
