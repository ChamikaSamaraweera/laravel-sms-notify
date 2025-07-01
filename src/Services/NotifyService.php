<?php

namespace TeamInfinityDev\SmsNotify\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use TeamInfinityDev\SmsNotify\Exceptions\NotifyException;

class NotifyService
{
    protected $userId;
    protected $apiKey;
    protected $senderId;
    protected $baseUrl;
    protected $retryAttempts;
    protected $retryDelay;
    protected $httpOptions;
    protected $logRequests;
    protected $mockResponses;

    public function __construct()
    {
        $this->userId = config('sms-notify.api.user_id');
        $this->apiKey = config('sms-notify.api.api_key');
        $this->senderId = config('sms-notify.api.sender_id');
        $this->baseUrl = config('sms-notify.api.base_url');
        $this->retryAttempts = config('sms-notify.defaults.retry_attempts', 3);
        $this->retryDelay = config('sms-notify.defaults.retry_delay', 1);
        $this->httpOptions = config('sms-notify.http', []);
        $this->logRequests = config('sms-notify.development.log_requests', false);
        $this->mockResponses = config('sms-notify.development.mock_responses', false);

        $this->validateConfig();
    }

    /**
     * Validate configuration
     *
     * @throws NotifyException
     */
    protected function validateConfig()
    {
        if (empty($this->userId) || empty($this->apiKey)) {
            throw NotifyException::invalidCredentials();
        }
    }

    /**
     * Get HTTP client with configuration
     *
     * @return \Illuminate\Http\Client\PendingRequest
     */
    protected function getHttpClient()
    {
        $client = Http::timeout($this->httpOptions['timeout'] ?? 30)
            ->connectTimeout($this->httpOptions['connect_timeout'] ?? 10)
            ->retry($this->retryAttempts, $this->retryDelay * 1000);

        // SSL verification - can be disabled for local development
        if (isset($this->httpOptions['verify'])) {
            $client = $client->withOptions(['verify' => $this->httpOptions['verify']]);
        }

        // Allow redirects
        if (isset($this->httpOptions['allow_redirects'])) {
            $client = $client->withOptions(['allow_redirects' => $this->httpOptions['allow_redirects']]);
        }

        // Add User-Agent
        $client = $client->withHeaders([
            'User-Agent' => 'Laravel-SMS-Notify/1.1.1',
            'Accept' => 'application/json',
        ]);

        return $client;
    }

    /**
     * Log request details if enabled
     *
     * @param string $method
     * @param string $url
     * @param array $data
     * @param array $response
     */
    protected function logRequest(string $method, string $url, array $data, array $response)
    {
        if ($this->logRequests) {
            Log::info('SMS Notify API Request', [
                'method' => $method,
                'url' => $url,
                'data' => array_merge($data, [
                    'api_key' => '***HIDDEN***', // Hide sensitive data
                    'user_id' => substr($this->userId, 0, 3) . '***'
                ]),
                'response_status' => $response['status_code'] ?? 'unknown',
                'response_success' => $response['success'] ?? false,
            ]);
        }
    }

    /**
     * Format phone number
     *
     * @param string $number
     * @return string
     */
    protected function formatNumber(string $number): string
    {
        $number = preg_replace('/[^0-9]/', '', $number);
        
        if (strlen($number) === 9) {
            return config('sms-notify.defaults.country_code') . $number;
        }
        
        return $number;
    }


    /**
     * Send SMS
     *
     * @param string|array $to
     * @param string $message
     * @param array $options
     * @return array
     * @throws NotifyException
     */
    public function send($to, string $message, array $options = []): array
    {
        if (empty($message)) {
            throw NotifyException::emptyMessage();
        }

        $numbers = is_array($to) ? $to : [$to];
        $formattedNumbers = array_map([$this, 'formatNumber'], $numbers);
        
        // Return mock response in development
        if ($this->mockResponses) {
            $mockResponse = [
                'success' => true,
                'data' => [
                    'message_id' => 'mock_' . uniqid(),
                    'status' => 'queued',
                    'recipients' => count($formattedNumbers),
                ],
                'status_code' => 200,
            ];
            
            $this->logRequest('POST', $this->baseUrl . '/send', ['to' => $formattedNumbers, 'message' => $message], $mockResponse);
            return $mockResponse;
        }

        $payload = array_merge([
            'user_id' => $this->userId,
            'api_key' => $this->apiKey,
            'sender_id' => $this->senderId,
            'to' => implode(',', $formattedNumbers),
            'message' => $message,
        ], $options);

        try {
            $response = $this->getHttpClient()->post($this->baseUrl . '/send', $payload);

            $result = [
                'success' => $response->successful(),
                'data' => $response->json(),
                'status_code' => $response->status(),
            ];

            // Handle specific error cases
            if ($response->status() === 401) {
                $result['success'] = false;
                $result['error'] = 'Invalid credentials';
            } elseif ($response->status() === 429) {
                $result['success'] = false;
                $result['error'] = 'API rate limit exceeded. Please try again later.';
            } elseif (!$response->successful()) {
                $result['success'] = false;
                $result['error'] = $response->json()['message'] ?? 'API request failed';
            }

            $this->logRequest('POST', $this->baseUrl . '/send', $payload, $result);
            return $result;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $error = [
                'success' => false,
                'error' => 'Connection failed: ' . $e->getMessage() . '. Check your internet connection and SSL settings.',
                'status_code' => 0,
            ];
            
            $this->logRequest('POST', $this->baseUrl . '/send', $payload, $error);
            return $error;
            
        } catch (\Exception $e) {
            $error = [
                'success' => false,
                'error' => 'Request failed: ' . $e->getMessage(),
                'status_code' => $e->getCode(),
            ];
            
            $this->logRequest('POST', $this->baseUrl . '/send', $payload, $error);
            return $error;
        }
    }



}