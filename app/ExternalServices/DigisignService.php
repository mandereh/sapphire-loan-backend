<?php

namespace App\ExternalServices;

use App\Models\RequestLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class DigisignService
{

    protected $client;
    protected $baseUri;
    protected $apiKey;
//    protected $accessToken;
//    protected $tokenExpiresAt;
//    protected $organisationId;
    protected $organisationName;

    public function __construct()
    {
        $this->client = new Client();
        $this->baseUri = config('services.digisign.baseUri');
        $this->apiKey = config('services.digisign.apiKey');

//        $this->accessToken = $this->generateSession()['data']['accessToken'];
//        echo $this->tokenExpiresAt;
//        echo $this->organisationId;
//        $this->organisationName = $this->generateSession()['data']['organisationName'];
    }


//    public function getAccessToken()
//    {
//        if (!$this->accessToken || $this->isTokenExpired()) {
//            $this->requestAccessToken();
//        }
//        return $this->accessToken;
//    }

//    private function isTokenExpired()
//    {
//        return Carbon::parse($this->tokenExpiresAt) < Carbon::now();
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
                ]
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

    public function transformTemplate($templateId,$data,$loanId = null)
    {
        $session = $this->generateSession();
        if ($session['status'] != 'success'){
            return $session;
        }
        $accessToken = $session['data']['accessToken'];
        $organisationId = $session['data']['organisationId'];

        $headers = [
            'X-O10N-Identifier' => $organisationId,
            'X-WS-Identifier' => '',
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer $accessToken",
            'X-API-KEY' => $this->apiKey,
        ];
        $endpoint = "/v1/templates/{$templateId}/transform";
        $uri = $this->baseUri.$endpoint;
        $requestLog = [
            'uri' => $uri,
            'endpoint' => $endpoint,
//            'time' => $time,
            'source'=>'',
            'narration' => 'send the template as request.',
        ];

        $this->makeRequest('PUT',$uri,$headers,[],[],$data,$requestLog);

    }

    public function processVerifiedDocument(DigisignWebhook $digisignWebhook)
    {

    }

    public function getDocument($publicId)
    {

    }
}
