<?php

namespace App\ExternalServices;

use App\Constants\Status;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use App\Models\RequestLog;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

class Flutterwave {
    public static function bvnValidation(string $bvn) {
        $cacheKey = config('app.name').self::class."bvn_validation".$bvn;
        $bvnDetailsFromCache = Cache::get($cacheKey);

        if ($bvnDetailsFromCache) {
            return json_decode($bvnDetailsFromCache);
        }

        $apiUrl = \sprintf(config('services.flutterwave.base_url')."/v2/kyc/bvn/%s?seckey=%s", $bvn, config('services.flutterwave.secret_key'));

        $client = new \GuzzleHttp\Client();
        $response = $client->get($apiUrl);

        $bvnDetails = json_decode((string) $response->getBody());

        RequestLog::createLog($apiUrl, self::class, $apiUrl, json_encode($bvnDetails));

        if ($bvnDetails->status == "success") {
            Cache::put($cacheKey, json_encode($bvnDetails->data));
            return $bvnDetails->data;
        }
        return null;
    }

    public static function validateTransaction ($reference) : object {
        $requestBody = [
            'SECKEY' => config('services.flutterwave.secret_key'),
            'txref' => $reference
        ];

        $endpoint = config('services.flutterwave.base_url')."/flwv3-pug/getpaidx/api/v2/verify";

        $client = new Client();

        try {
            $response = $client->post($endpoint, [
                'json' => $requestBody
            ]);

            $responseObj = json_decode((string) $response->getBody());
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response =  Psr7\str($e->getResponse());
            }
            RequestLog::createLog(
                $endpoint,
                self::class,
                $requestBody,
                $e->hasResponse() ? $response : $e->getMessage()
            );

            return (new \stdClass);
        }

        RequestLog::createLog($endpoint, self::class, $requestBody, $responseObj);

        return $responseObj;
    }

    public static function chargeAuthorization ($authorizationCode, $email, $amount, $reference, $narration) : object {
        $requestBody = [
            'token' => $authorizationCode,
            'email' => $email,
            'amount' => $amount,
            'currency' => 'NGN',
            'txRef' => $reference,
            'SECKEY' => config('services.flutterwave.secret_key'),
            'narration' => $narration
        ];

        $endpoint = config('services.flutterwave.base_url')."/flwv3-pug/getpaidx/api/tokenized/charge";

        $client = new Client();
        try {
            $response = $client->post($endpoint, [
                'json' => $requestBody
            ]);

            $responseObj = json_decode((string) $response->getBody());

            RequestLog::createLog($endpoint, self::class, $requestBody, $responseObj);

            return $responseObj;
        } catch(ClientException $e) {
            RequestLog::createLog($endpoint, self::class, $requestBody, $e->getMessage());
            throw $e;
        }
    }
}
