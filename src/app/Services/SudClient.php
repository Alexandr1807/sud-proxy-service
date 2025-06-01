<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class SudClient
{
    protected Client $guzzle;
    protected int $retries;

    public function __construct()
    {
        $this->guzzle = new Client([
            'base_uri' => config('sud.base_url'),
            'timeout'  => config('sud.timeout'),
        ]);
        $this->retries = config('sud.retries', 0);
    }

    public function request(string $method, string $uri, array $options = [])
    {
        $options['headers'] = array_merge($options['headers'] ?? [], [
            'Accept' => 'application/json',
        ]);

        $attempts = 0;
        while (true) {
            try {
                $start = microtime(true);
                $response = $this->guzzle->request($method, $uri, $options);
                $duration = round((microtime(true) - $start) * 1000, 2);

                Log::info('JadvalAPI Request', [
                    'method'      => $method,
                    'uri'         => $uri,
                    'options'     => $options,
                    'status'      => $response->getStatusCode(),
                    'duration_ms' => $duration,
                ]);

                return $response;
            } catch (RequestException $e) {
                $attempts++;
                Log::error('JadvalAPI Request Error', [
                    'method'  => $method,
                    'uri'     => $uri,
                    'options' => $options,
                    'error'   => $e->getMessage(),
                    'attempt' => $attempts,
                ]);

                if ($attempts > $this->retries) {
                    throw $e;
                }
                usleep(100_000);
            }
        }
    }
}
