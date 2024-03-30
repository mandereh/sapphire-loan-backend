<?php

namespace App\ExternalServices;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class DigisignService
{

    protected $client;
    protected $baseUri;
    protected $apiKey;
    protected $accessToken;
    protected $tokenExpiresAt;
    protected $organisationId;
    protected $organisationName;

    public function __construct()
    {
        $this->client = new Client();
        $this->baseUri = config('services.digisign.baseUri');
        $this->apiKey = config('services.digisign.apiKey');

        $this->accessToken = $this->getAccessToken();
        echo $this->tokenExpiresAt;
        echo $this->organisationId;
        echo $this->organisationName;
    }


    public function getAccessToken()
    {
        if (!$this->accessToken || $this->isTokenExpired()) {
            $this->requestAccessToken();
        }
        return $this->accessToken;
    }

    public function isTokenExpired()
    {
        return Carbon::parse($this->tokenExpiresAt) < Carbon::now();
    }

    public function requestAccessToken()
    {
        try {
            $response = $this->client->post("{$this->baseUri}/v1/keys/session", [
                'headers' => [
                    'X-API-Key' => $this->apiKey
                ]
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            $this->accessToken = $data['meta']['access_token'];
            $this->tokenExpiresAt = $data['data']['expires_in'];
            $this->organisationId = $data['data']['organisation_id'];
            $this->organisationName = $data['data']['organisation_name'];
        } catch (GuzzleException $e) {
            throw $e;
        }
    }

    public function makeRequest($method, $uri, $headers, $queryParam = [], $formParam = [], $data = [])
    {
        $cacheKey = md5($uri . json_encode($data));
        $cacheDuration = now()->addMinutes(60);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        try {
            $response = $this->client->request($method, $uri, [
                'headers' => $headers,
                'query' => $queryParam,
                'form_params' => $formParam,
                'json' => $data,
            ]);
            $responseData = json_decode($response->getBody()->getContents(), true);
            Cache::put($cacheKey, $responseData, $cacheDuration);
            return $responseData;
        } catch (GuzzleException $e) {
            throw $e;
        }
    }

    public function template($data = [])
    {
        $headers = [];
    }
}
