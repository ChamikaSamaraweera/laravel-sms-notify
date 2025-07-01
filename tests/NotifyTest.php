<?php

namespace TeamInfinityDev\SmsNotify\Tests;

use Orchestra\Testbench\TestCase as TestCase2;
use TeamInfinityDev\SmsNotify\Services\NotifyService;
use TeamInfinityDev\SmsNotify\SmsNotifyServiceProvider;
use Illuminate\Support\Facades\Http;
use TeamInfinityDev\SmsNotify\Exceptions\NotifyException;
use PHPUnit\Framework\TestCase;

class NotifyTest extends TestCase
{
    protected NotifyService $notifyService;

    protected function getPackageProviders($app): array
    {
        return [
            SmsNotifyServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set test configuration
        config([
            'sms-notify.api.user_id' => 'test-user',
            'sms-notify.api.api_key' => 'test-key',
            'sms-notify.api.sender_id' => 'NotifyDemo',
            'sms-notify.api.base_url' => 'https://notifi.lk/api/v1',
        ]);
        
        // Create service instance
        $this->notifyService = new NotifyService();
        
        // Mock HTTP responses
        Http::fake([
            'notifi.lk/api/v1/send' => Http::response([
                'status' => 'success',
                'message' => 'Message sent successfully',
                'data' => ['message_id' => 'test-123']
            ], 200),
            'notifi.lk/api/v1/balance' => Http::response([
                'status' => 'success',
                'data' => ['balance' => 100]
            ], 200),
        ]);
    }

    /** @test */
    public function test_addition_works()
    {
        $result = 2 + 2;
        $this->assertEquals(4, $result);
    }
}