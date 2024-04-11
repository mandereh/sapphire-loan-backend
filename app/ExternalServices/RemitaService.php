<?php

namespace App\ExternalServices;

use App\Models\RequestLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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
        $this->baseUri = config('services.remita.baseUri');
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

    private function makeRequest($method,$uri,$headers = [],$queryParam = [],$formParam = [],$data ,$requestLog)
    {
//        $cacheKey = md5($uri . json_encode($data));
//        $cacheDuration = now()->addMinutes(60);

//        if (Cache::has($cacheKey)) {
//            return Cache::get($cacheKey);
//        }
        $request_log = new RequestLog();

        $request_log->request_type = $method;
        $request_log->narration = $requestLog['narration'];
        $request_log->source = $requestLog['source'];
        $request_log->end_point = $uri;
        //            $request_log->tran_id = $requestLog['time'];
        $request_log->request_payload = json_encode($data);

        try {
           
            $response = $this->client->request($method, $uri, [
                'headers' => $headers,
                'query' => $queryParam,
                'form_params' => $formParam,
                'json' => $data
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

//            Cache::put($cacheKey, $responseData, $cacheDuration);

            $request_log->response_payload = json_encode($responseData);
            $request_log->save();

            return $responseData;
        } catch (GuzzleException $e) {
            // Handle exception
            $request_log->response_payload = $e->getMessage();
            $request_log->save();
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
    public function getSalaryHistory(array $requestData)
    {
        // $headers = [
        //     'Content-Type' => 'application/json',
        //     'API_KEY' => $this->apiKey,
        //     'MERCHANT_ID' => $this->merchantId,
        //     'REQUEST_ID' => $this->requestId,
        //     'AUTHORIZATION' => $this->authorization,
        // ];
        // $data = [
        //     'authorisationCode' => $requestData['authorisationCode'],
        //     'firstName' => $requestData['firstName'],
        //     'lastName' => $requestData['lastName'],
        //     'middleName' => $requestData['middleName'],
        //     'accountNumber' => $requestData['accountNumber'],
        //     'bankCode' => $requestData['bankCode'],
        //     'bvn' => $requestData['bvn'],
        //     'authorisationChannel' => $requestData['authorisationChannel']
        // ];

        $headers = [];

        $data = [];

        if(app()->environment('local', 'staging')){
            return $this->dummyData();
        }

        $endpoint = "/loansvc/data/api/v2/payday/salary/history/provideCustomerDetails";
        $uri = $this->baseUri . $endpoint;
        $time = time();
        $requestLog = [
            'uri' => $uri,
            'endpoint' => $endpoint,
            'time' => $time,
            'source'=>'',
            'narration' => "get the salary history of the loan applicant",
        ];


        return $this->makeRequest('POST', $uri,$headers,[],[], $data,$requestLog);
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
    public function loanDisburstmentNotification(array $requestData)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'API_KEY' => $this->apiKey,
            'MERCHANT_ID' => $this->merchantId,
            'REQUEST_ID' => $this->requestId,
            'AUTHORIZATION' => $this->authorization,
//            'Authorization' => $this->accessToken,
        ];
        $data = [
            "customerId" => $requestData["customerId"],
            "authorisationCode" => isset($requestData["authorisationCode"]) ? $requestData["authorisationCode"] : "848730",
            "authorisationChannel" => isset($requestData["authorisationChannel"]) ? $requestData["authorisationChannel"] : 'USSD',
            "phoneNumber" => $requestData["phoneNumber"],
            "accountNumber" => $requestData["accountNumber"],
            "currency" => $requestData["currency"] ?? 'NGN',
            "loanAmount" => $requestData["loanAmount"],
            "collectionAmount" => $requestData["collectionAmount"],
            "dateOfDisbursement" => $requestData["disbursementDate"],
            "dateOfCollection" => $requestData["collectionDate"],
            "totalCollectionAmount" => $requestData["totalCollectionAmount"],
            "numberOfRepayments" => $requestData["numberOfRepayments"],
            "bankCode" => $requestData["bankCode"]
        ];

        Log::info('disbursement notification: ', $data);

        $endpoint = "/loansvc/data/api/v2/payday/post/loan";
        $uri = $this->baseUri . $endpoint;
        $time = time();
        $requestLog = [
            'uri' => $uri,
            'endpoint' => $endpoint,
            'time' => $time,
            'source'=>'',
            'narration' => 'utilized to let remita know about the relevant details about the loan.',
        ];

        return $this->makeRequest('POST', $uri, $headers,[],[], $data,$requestLog);
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
    public function mandateHistory(array $requestData)
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
        $uri = $this->baseUri . $endpoint;
        $time = time();
        $requestLog = [
            'uri' => $uri,
            'endpoint' => $endpoint,
            'source'=>'',
//            'tran_id' => $time,
            'narration' => 'mandate history',
        ];

        return $this->makeRequest('POST', $uri, $headers,[],[], $data, $requestLog);
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
        $details = [];
        while($times > 0){
            $percentPayed = mt_rand(0.05,0.9);
            $percentPayed = $repaymentAmount * $percentPayed;
            $details[] = [
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

        $responseData['loanHistoryDetails'] = $details;

        return $responseData;
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
    public function stopLoanCollection(array $requestData)
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
        $uri = $this->baseUri . $endpoint;
        $time = time();
        $requestLog = [
            'uri' => $uri,
            'endpoint' => $endpoint,
            'source' => '',
//            'tran_id' => $time,
            'narration' => 'stop loan collection',
        ];

        return $this->makeRequest('POST', $uri, $headers,[],[], $data, $requestLog);
    }
}
