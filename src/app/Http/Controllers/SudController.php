<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SudClient;
use App\Models\SudLog;

class SudController extends Controller
{
    protected SudClient $client;

    public function __construct(SudClient $client)
    {
        $this->client = $client;
    }

    public function dynamicProxy(Request $request, string $jurisdiction, string $operation, string $value)
    {
        if (strtolower($jurisdiction) === 'case') {
            return $this->handleCaseFind($request, $operation, $value);
        }

        return $this->handleOnlineMonitoring($request, $jurisdiction, $operation, $value);
    }

    protected function handleCaseFind(Request $request, string $operation, string $value)
    {
        $op = Str::camel($operation);

        $decodedValue = rawurldecode($value);
        $log = [
            'user_id'      => $request->user()->id,
            'endpoint'     => 'api/sud/case' . "/{$operation}/{$value}",
            'request_data' => ['number' => $decodedValue],
            'response_data'=> null,
            'status'       => null,
            'http_code'    => null,
            'error_message'=> null,
            'created_at'   => now(),
        ];

        try {
            $fullUrl = "https://jadval.sud.uz/case/{$op}/" . $decodedValue;
            $response = $this->client->request('GET', $fullUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            $status  = $response->getStatusCode();
            $rawBody = (string) $response->getBody();
            $decoded = json_decode($rawBody, true);

            if (trim($rawBody) === 'Иш мавжуд эмас') {
                $log['response_data'] = ['message' => 'Иш мавжуд эмас'];
                $log['status']        = 'not_found';
                $log['http_code']     = 404;
                $log['error_message'] = null;
                SudLog::create($log);

                return response()->json([
                    'message'   => 'Иш мавжуд эмас',
                    'data'      => null,
                    'timestamp' => now()->toIso8601ZuluString(),
                    'success'   => false,
                ], 404);
            }

            if ($decoded === null && $rawBody !== '') {
                $log['response_data'] = null;
                $log['status']        = 'error';
                $log['http_code']     = $status;
                $log['error_message'] = $rawBody;
                SudLog::create($log);

                return response()->json([
                    'message'   => 'External service returned non-JSON response',
                    'data'      => null,
                    'timestamp' => now()->toIso8601ZuluString(),
                    'success'   => false,
                ], $status);
            }

            $log['response_data'] = $decoded;
            $log['status']        = 'success';
            $log['http_code']     = $status;
            $log['error_message'] = null;
            SudLog::create($log);

            return response()->json([
                'message'   => 'Успешно',
                'data'      => $decoded ?: [],
                'timestamp' => now()->toIso8601ZuluString(),
                'success'   => true,
            ], $status);

        } catch (RequestException $e) {
            $status = $e->hasResponse()
                ? $e->getResponse()->getStatusCode()
                : Response::HTTP_INTERNAL_SERVER_ERROR;
            $body = $e->hasResponse()
                ? (string) $e->getResponse()->getBody()
                : $e->getMessage();

            $log['response_data'] = null;
            $log['status']        = 'error';
            $log['http_code']     = $status;
            $log['error_message'] = $body;
            SudLog::create($log);

            return response()->json([
                'message'   => 'Ошибка обращения к сервису',
                'data'      => null,
                'timestamp' => now()->toIso8601ZuluString(),
                'success'   => false,
            ], $status);
        }
    }

    protected function handleOnlineMonitoring(Request $request, string $jurisdiction, string $operation, string $value)
    {
        $jur = strtoupper($jurisdiction);
        $op  = Str::camel($operation);

        $base = rtrim(config('sud.base_url'), '/');
        $fullUrl = $base . "/{$jur}/{$op}/{$value}";

        $log = [
            'user_id'      => $request->user()->id,
            'endpoint'     => 'api/sud' . "/{$jurisdiction}/{$operation}/{$value}",
            'request_data' => ['value' => rawurldecode($value)],
            'response_data'=> null,
            'status'       => null,
            'http_code'    => null,
            'error_message'=> null,
            'created_at'   => now(),
        ];

        try {
            $response = $this->client->request('GET', $fullUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $request->bearerToken(),
                    'Accept'        => 'application/json',
                ],
                'query' => $request->query(),
            ]);

            $status  = $response->getStatusCode();
            $rawBody = (string) $response->getBody();
            $decoded = json_decode($rawBody, true);

            $log['response_data'] = $decoded;
            $log['status']        = 'success';
            $log['http_code']     = $status;
            $log['error_message'] = null;
            SudLog::create($log);

            if ($decoded === null && $rawBody !== '') {
                return response()->json([
                    'message'   => 'External service returned non-JSON response',
                    'data'      => null,
                    'timestamp' => now()->toIso8601ZuluString(),
                    'success'   => false,
                ], $status);
            }

            return response()->json([
                'message'   => 'Успешно',
                'data'      => $decoded ?: [],
                'timestamp' => now()->toIso8601ZuluString(),
                'success'   => true,
            ], $status);

        } catch (RequestException $e) {
            $status = $e->hasResponse()
                ? $e->getResponse()->getStatusCode()
                : Response::HTTP_INTERNAL_SERVER_ERROR;
            $body = $e->hasResponse()
                ? (string) $e->getResponse()->getBody()
                : $e->getMessage();

            $log['response_data'] = null;
            $log['status']        = 'error';
            $log['http_code']     = $status;
            $log['error_message'] = $body;
            SudLog::create($log);

            return response()->json([
                'message'   => 'Ошибка обращения к сервису',
                'data'      => null,
                'timestamp' => now()->toIso8601ZuluString(),
                'success'   => false,
            ], $status);
        }
    }
}
