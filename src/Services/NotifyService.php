<?php

namespace TeamInfinityDev\SmsNotify\Services;

use Illuminate\Support\Facades\Http;
use TeamInfinityDev\SmsNotify\Exceptions\NotifyException;

class NotifyService
{
    protected $userId;
    protected $apiKey;
    protected $senderId;
    protected $baseUrl;
    protected $retryAttempts;
    protected $retryDelay;

    public function __construct()
    {
        $this->userId = config('sms-notify.api.user_id');
        $this->apiKey = config('sms-notify.api.api_key');
        $this->senderId = config('sms-notify.api.sender_id');
        $this->baseUrl = config('sms-notify.api.base_url');
        $this->retryAttempts = config('sms-notify.defaults.retry_attempts', 3);
        $this->retryDelay = config('sms-notify.defaults.retry_delay', 1);

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
     * Check if there's sufficient balance before sending
     *
     * @param int $requiredMessages Number of messages to be sent
     * @return bool
     * @throws NotifyException
     */
    protected function hasSufficientBalance(int $requiredMessages = 1): bool
    {
        try {
            $response = Http::get($this->baseUrl . '/balance', [
                'user_id' => $this->userId,
                'api_key' => $this->apiKey,
            ]);

            if (!$response->successful()) {
                throw NotifyException::apiError('Failed to check balance', $response->status());
            }

            $data = $response->json();
            $balance = $data['data']['balance'] ?? 0;

            if ($balance < $requiredMessages) {
                throw NotifyException::insufficientBalance();
            }

            return true;
        } catch (NotifyException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw NotifyException::apiError('Failed to check balance: ' . $e->getMessage());
        }
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
        
        // Check balance before sending
        try {
            $this->hasSufficientBalance(count($numbers));
        } catch (NotifyException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status_code' => $e->getCode(),
            ];
        }

        $payload = array_merge([
            'user_id' => $this->userId,
            'api_key' => $this->apiKey,
            'sender_id' => $this->senderId,
            'to' => implode(',', $formattedNumbers),
            'message' => $message,
        ], $options);

        try {
            $response = Http::retry($this->retryAttempts, $this->retryDelay * 1000)
                ->post($this->baseUrl . '/send', $payload);

            if ($response->status() === 401) {
                throw NotifyException::apiError('Invalid credentials', 401);
            }

            if ($response->status() === 429) {
                throw NotifyException::rateLimitExceeded();
            }

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
                'status_code' => $response->status(),
            ];
        } catch (NotifyException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status_code' => $e->getCode(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status_code' => $e->getCode(),
            ];
        }
    }


    /**
     * Check balance
     *
     * @return array
     */
    public function checkBalance(): array
    {
        try {
            $response = Http::get($this->baseUrl . '/balance', [
                'user_id' => $this->userId,
                'api_key' => $this->apiKey,
            ]);

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
                'status_code' => $response->status(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status_code' => $e->getCode(),
            ];
        }
    }

    /**
     * Get delivery report
     *
     * @param string $messageId
     * @return array
     */
    public function getDeliveryReport(string $messageId): array
    {
        try {
            $response = Http::get($this->baseUrl . '/status', [
                'user_id' => $this->userId,
                'api_key' => $this->apiKey,
                'message_id' => $messageId,
            ]);

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
                'status_code' => $response->status(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status_code' => $e->getCode(),
            ];
        }
    }
}