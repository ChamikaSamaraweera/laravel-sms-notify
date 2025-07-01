<?php

namespace TeamInfinityDev\SmsNotify\Services;

use Illuminate\Support\Facades\Http;
use TeamInfinityDev\SmsNotify\Exceptions\NotifiException;

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
     * @throws NotifiException
     */
    protected function validateConfig()
    {
        if (empty($this->userId) || empty($this->apiKey)) {
            throw new NotifiException('Notifi.lk USER_ID and API_KEY are required');
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
     */
    public function send($to, string $message, array $options = []): array
    {
        $numbers = is_array($to) ? $to : [$to];
        $formattedNumbers = array_map([$this, 'formatNumber'], $numbers);

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