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
    public function it_can_send_single_sms()
    {
        $response = $this->notifyService->send('771234567', 'Test message');
        
        $this->assertTrue($response['success']);
        $this->assertEquals(200, $response['status_code']);
    }

    /** @test */
    public function it_can_send_bulk_sms()
    {
        $numbers = ['771234567', '772345678'];
        $response = $this->notifyService->send($numbers, 'Bulk test message');
        
        $this->assertTrue($response['success']);
        $this->assertEquals(200, $response['status_code']);
    }

    /** @test */
    public function it_can_check_balance()
    {
        $response = $this->notifyService->checkBalance();
        
        $this->assertTrue($response['success']);
        $this->assertEquals(200, $response['status_code']);
    }

    /** @test */
    public function it_formats_phone_numbers_correctly()
    {
        // Test with 9-digit number (should add country code)
        $response = $this->notifyService->send('771234567', 'Test message');
        Http::assertSent(function ($request) {
            return str_contains($request['to'], '94771234567');
        });

        // Test with full number (should not modify)
        $response = $this->notifyService->send('94771234567', 'Test message');
        Http::assertSent(function ($request) {
            return str_contains($request['to'], '94771234567');
        });
    }

    /** @test */
    public function it_handles_api_errors_gracefully()
    {
        Http::fake([
            'notifi.lk/api/v1/*' => Http::response([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], 401)
        ]);

        $response = $this->notifyService->send('771234567', 'Test message');
        
        $this->assertFalse($response['success']);
        $this->assertEquals(401, $response['status_code']);
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

    /** @test */
    public function it_throws_exception_for_invalid_phone_number()
    {
        $this->expectException(NotifyException::class);
        $this->expectExceptionMessage('Invalid phone number format');
        
        $this->notifyService->send('123', 'Test message');
    }

    /** @test */
    public function it_checks_balance_before_sending()
    {
        Http::fake([
            '*/balance' => Http::response([
                'status' => 'success',
                'data' => ['balance' => 0]
            ], 200),
        ]);

        $response = $this->notifyService->send('771234567', 'Test message');
        
        $this->assertFalse($response['success']);
        $this->assertEquals('Insufficient balance in your Notifi.lk account.', $response['error']);
    }

    /** @test */
    public function it_sends_message_when_balance_is_sufficient()
    {
        Http::fake([
            '*/balance' => Http::response([
                'status' => 'success',
                'data' => ['balance' => 10]
            ], 200),
            '*/send' => Http::response([
                'status' => 'success',
                'data' => ['message_id' => 'test-123']
            ], 200),
        ]);

        $response = $this->notifyService->send('771234567', 'Test message');
        
        $this->assertTrue($response['success']);
        $this->assertEquals(200, $response['status_code']);
    }
}