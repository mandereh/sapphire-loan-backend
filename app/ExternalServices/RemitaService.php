<?php

namespace App\ExternalServices;

use App\Models\RequestLog;
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

    private function makeRequest(String $method,String $uri,array $headers = [],array $queryParam = [],array $formParam = [],array $data = [], array $requestLog = [])
    {
        $cacheKey = md5($uri . json_encode($data));
        $cacheDuration = now()->addMinutes(60);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $request_log = new RequestLog();

            $request_log->request_type = $method;
            $request_log->narration = $requestLog['narration'];
            $request_log->source = $requestLog['source'];
            $request_log->end_point = $requestLog['endpoint'];
            $request_log->tran_id = $requestLog['time'];
            $request_log->request_payload = json_encode($data);

            $response = $this->client->request($method, $uri, [
                'headers' => $headers,
                'query' => $queryParam,
                'form_params' => $formParam,
                'json' => $data
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            Cache::put($cacheKey, $responseData, $cacheDuration);

            $request_log->response_payload = json_encode($responseData);
            $request_log->save();

            return $responseData;
        } catch (GuzzleException $e) {
            // Handle exception
            throw $e;
        }
    }

    /**
     * Get salary history for the loan applicant.
     *
     * @param array $requestData An array containing the following keys:
     *   - 'authorisationCode' (string): The authorization code.
     *   - 'firstname' (string): The first name of the loan applicant.
     *   - 'lastname' (string): The last name of the loan applicant.
     *   - 'middlename' (string): The middle name of the loan applicant.
     *   - 'accountNumber' (string): The account number of the loan applicant.
     *   - 'bankCode' (string): The bank code of the loan applicant.
     *   - 'bvn' (string): The Bank Verification Number (BVN) of the loan applicant.
     *   - 'authorisationChannel' (string): The authorization channel used.
     *
     * @return mixed
     *
     * @throws GuzzleException
     */
    public function getSalaryHistory(array $requestData = [])
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
        $endpoint = "/loansvc/data/api/v2/payday/salary/history/provideCustomerDetails";
        $uri = $this->baseUri . '/' . $endpoint;
        $time = time();
        $requestLog = [
            'uri' => $uri,
            'endpoint' => $endpoint,
            'tran_id' => $time,
            'narration' => 'get the salary history of the loan applicant',
        ];


        return $this->makeRequest('POST', $uri, $headers, $data,$requestLog);
    }


    /**
     * Get loan disburstment notification
     *
     * @param array $requestData An array containing the following keys:
     *   - 'customerId' (string):
     *   - 'authorisationCode' (string):
     *   - 'authorisationChannel' (string):
     *   - 'phoneNumber' (string):
     *   - 'accountNumber' (string):
     *   - 'currency' (string):
     *   - 'loanAmount' (string):
     *   - 'collectionAmount' (string):
     *   - 'disbursementDate' (string):
     *   - 'totalCollectionAmount' (string):
     *   - 'numberOfRepayments' (string):
     *   - 'bankCode' (string):
     *
     * @return mixed
     *
     * @throws GuzzleException
     */
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

        $endpoint = "/loansvc/data/api/v2/payday/post/loan";
        $uri = $this->baseUri . '/' . $endpoint;
        $time = time();
        $requestLog = [
            'uri' => $uri,
            'endpoint' => $endpoint,
            'tran_id' => $time,
            'narration' => 'utilized to let remita know about the relevant details about the loan.',
        ];

        return $this->makeRequest('POST', $uri, $headers, $data,$requestLog);
    }

    /**
     * Get loan disburstment notification
     *
     * @param array $requestData An array containing the following keys:
     *   - 'authorisationCode' (string):
     *   - 'customerId' (string):
     *   - 'mandateReference' (string):
     *
     * @return mixed
     *
     * @throws GuzzleException
     */
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

        $endpoint = "/loansvc/data/api/v2/payday/loan/payment/history";
        $uri = $this->baseUri . '/' . $endpoint;
        $time = time();
        $requestLog = [
            'uri' => $uri,
            'endpoint' => $endpoint,
            'tran_id' => $time,
            'narration' => 'mandate history',
        ];

        return $this->makeRequest('POST', $uri, $headers, $data, $requestLog);
    }

    /**
     * Get loan disburstment notification
     *
     * @param array $requestData An array containing the following keys:
     *   - 'authorisationCode' (string):
     *   - 'customerId' (string):
     *   - 'mandateReference' (string):
     *
     * @return mixed
     *
     * @throws GuzzleException
     */
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
            "authorisationCode" => $requestData["authorisationCode"],
            "customerId" => $requestData["customerId"],
            "mandateReference" => $requestData["mandateReference"]
        ];

        $endpoint = "/loansvc/data/api/v2/payday/stop/loan";
        $uri = $this->baseUri . '/' . $endpoint;
        $time = time();
        $requestLog = [
            'uri' => $uri,
            'endpoint' => $endpoint,
            'tran_id' => $time,
            'narration' => 'stop loan collection',
        ];

        return $this->makeRequest('POST', $uri, $headers, $data, $requestLog);
    }
}
