<?php

namespace TeamInfinityDev\SmsNotify\Tests;

use Orchestra\Testbench\TestCase;
use TeamInfinityDev\SmsNotify\Services\NotifyService;
use TeamInfinityDev\SmsNotify\SmsNotifyServiceProvider;
use Illuminate\Support\Facades\Http;
use TeamInfinityDev\SmsNotify\Exceptions\NotifyException;

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
    public function it_validates_required_config()
    {
        $this->expectException(\TeamInfinityDev\SmsNotify\Exceptions\NotifyException::class);

        config(['sms-notify.api.user_id' => null]);
        new NotifyService();
    }

    protected function tearDown(): void
    {
        Http::assertSent(function ($request) {
            return in_array($request->method(), ['POST', 'GET']) && 
                   str_contains($request->url(), 'notifi.lk/api/v1');
        });

        parent::tearDown();
    }

    /** @test */
    public function it_throws_exception_for_invalid_credentials()
    {
        $this->expectException(NotifyException::class);
        $this->expectExceptionMessage('Notifi.lk USER_ID and API_KEY are required');
        
        config(['sms-notify.api.user_id' => null]);
        new NotifyService();
    }
}