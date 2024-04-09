<?php

namespace App\ExternalServices;

use App\ExternalServices\Helpers\DigisignHelper;
use App\Models\RequestLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class DigisignService
{

    public $client;
    public $baseUri;
    public $apiKey;
    public $accessToken;
    public $tokenExpiresAt;
    public $organisationId;
    public $organisationName;
    public $workspaceId;
    public $templateId;

    public $session;

    public function __construct()
    {
        $this->client = new Client();
        $this->baseUri = config('services.digisign.baseUri');
        $this->apiKey = config('services.digisign.apiKey');

        $this->session = $this->generateSession();

        $this->accessToken = $this->session['data']['accessToken'];
        $this->tokenExpiresAt = $this->session['data']['tokenExpiresAt'];
        $this->organisationId = $this->session['data']['organisationId'];
        $this->organisationName = $this->session['data']['organisationName'];

        $this->workspaceId = $this->getWorkspaceId()['workspaceId'];
        $this->templateId = $this->getTemplate()['templateId'];
        $this->recipientId = $this->getTemplate()['recipientId'];

    }


//    public function getSession()
//    {
//        if (!$this->accessToken || $this->isTokenExpired()) {
//            return $this->generateSession();
//        }
//        return $this->session;
//    }

    private function isTokenExpired()
    {
        return Carbon::parse($this->tokenExpiresAt) < Carbon::now();
    }

//    public function getSession()
//    {
//
//        if (!$this->accessToken || Carbon::createFromTimestampMs($this->tokenExpiresAt) <= now()) {
//            return $this->session = $this->generateSession();
//        }
//        return $this->session;
//    }

    /**
     * @return array
     * @throws GuzzleException
     */
    public function generateSession()
    {

        try {
            $response = $this->client->post("$this->baseUri/v1/keys/session", [
                'headers' => [
                    'X-API-Key' => $this->apiKey
                ]
            ]);
            $data = json_decode($response->getBody()->getContents(), true);


            return [
                'status' => 'success',
                'message' => 'session generated successfully',
                'data' => [
                    'accessToken' => $data['meta']['access_token'],
                    'tokenExpiresAt' => $data['data']['expires_in'],
                    'organisationId' => $data['data']['organisation_id'],
                    'organisationName' => $data['data']['organisation_name'],
                ],
            ];


        } catch (GuzzleException $e) {
            throw $e;
        }
    }

    /**
     * @param $method
     * @param $uri
     * @param $headers
     * @param $queryParam
     * @param $formParam
     * @param $data
     * @param $requestLog
     * @return mixed
     * @throws GuzzleException
     */

    private function makeRequest($method, $uri, $headers = [], $queryParam = [], $formParam = [], $data, $requestLog = [])
    {
//        $cacheKey = md5($uri . json_encode($data));
//        $cacheDuration = now()->addMinutes(60);
//
//        if (Cache::has($cacheKey)) {
//            return Cache::get($cacheKey);
//        }
        try {

            $request_log = new RequestLog();

            $request_log->request_type = $method;
            $request_log->narration = $requestLog['narration'];
            $request_log->source = $requestLog['source'];
            $request_log->end_point = $requestLog['endpoint'];
//            $request_log->tran_id = $requestLog['time'];
            $request_log->request_payload = json_encode($data);

            $response = $this->client->request($method, $uri, [
                'headers' => $headers,
                'query' => $queryParam,
                'form_params' => $formParam,
                'json' => $data,
            ]);
            $responseData = json_decode($response->getBody()->getContents(), true);
//            Cache::put($cacheKey, $responseData, $cacheDuration);

            $request_log->response_payload = json_encode($responseData);
            $request_log->save();

            return $responseData;
        } catch (GuzzleException $e) {
            throw $e;
        }
    }

    public function getWorkspaceId()
    {

        $headers = [
            'X-API-KEY' => $this->apiKey,
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer $this->accessToken",
            'X-O10N-Identifier' => $this->organisationId,
        ];
        $data = [];
        $endpoint = "/v1/workspaces";
        $uri = $this->baseUri . $endpoint;
        $requestLog = [
            'uri' => $uri,
            'endpoint' => $endpoint,
//            'time' => $time,
            'source'=>'',
            'narration' => 'request for a workspace id',
        ];

        $response = $this->makeRequest('GET',$uri,$headers,[],[],$data,$requestLog);

        return [
                'workspaceId' => $response['data'][0]['public_id'],
            ];

    }

    public function getTemplate()
    {

        $headers = [
            'X-API-KEY' => $this->apiKey,
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer $this->accessToken",
            'X-O10N-Identifier' => $this->organisationId,
            'X-WS-Identifier' => $this->workspaceId,
        ];

        $data = [];

        $endpoint = "/v1/templates";
        $uri = $this->baseUri.$endpoint;
        $requestLog = [
            'uri' => $uri,
            'endpoint' => $endpoint,
//            'time' => $time,
            'source'=>'',
            'narration' => 'get the template id',
        ];

        $response = $this->makeRequest('GET',$uri,$headers,[],[],$data,$requestLog);
        return [
          'recipientId' => $response['data'][0]["recipient_aliases"][0]['alias_id'],
            'templateId' => $response['data'][0]['public_id'],
        ];
    }

    public function transformTemplate()
    {

        if (!$this->accessToken || $this->isTokenExpired()) {
            $this->generateSession();
        }


        $headers = [
            'X-API-KEY' => $this->apiKey,
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer $this->accessToken",
            'X-O10N-Identifier' => $this->organisationId,
            'X-WS-Identifier' => $this->workspaceId,
        ];

//        $data = DigisignHelper::templateDetails();

       $data = [
            "recipients" => [
        [
            "id" => "$this->recipientId",
            "name" => "Levine Cmion",
            "email" => "phronesis4xt@gmail.com",
            "fillable" => [
            "product_name" => "",
            "loan_account_number" => "",
            "name_of_customer" => "",
            "address_of_customer" => "",
            "current_date" => "",
            "customer_firstname" => "",
            "loan_type" => "",
            "disbursement_amount" => "",
            "disbursement_date" => "",
            "loan_tenor" => "",
            "monthly_repayment" => "",
            "interest_rate" => "",
            "due_amount1" => "",
            "repayment_date1" => "",
            "due_amount2" => "",
            "repayment_date2" => "",
            "due_amount3" => "",
            "repayment_date3" => "",
            ],
            "private_message" => "Jide, please check this document ASAP, for your container. This is just a test document tho. Good testing!!!",
        ],
    ],
    "message" => [
            "subject" => "Avocado Replublic Export v1.7.7",
        "body" => "Please review and sign this document as soon as you can. Thanks",
    ],
];

//        dd($data);


        $endpoint = "/v1/templates/".$this->templateId."/transform";
        $uri = $this->baseUri.$endpoint;
        $requestLog = [
            'uri' => $uri,
            'endpoint' => $endpoint,
//            'time' => $time,
            'source'=>'',
            'narration' => 'send the template as request.',
        ];


        return $this->makeRequest('PUT',$uri,$headers,[],[],$data,$requestLog);

    }

    public function processVerifiedDocument(DigisignWebhook $digisignWebhook)
    {

    }

    public function getDocument($publicId)
    {

    }
}
