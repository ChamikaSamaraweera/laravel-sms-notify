<?php

namespace TeamInfinityDev\SmsNotify\Tests;

use Orchestra\Testbench\TestCase;
use TeamInfinityDev\SmsNotify\Facades\SmsNotify;
use TeamInfinityDev\SmsNotify\SmsNotifyServiceProvider;
use Illuminate\Support\Facades\Http;

class NotifiTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [SmsNotifyServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'SmsNotify' => SmsNotify::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set test configuration
        config(['sms-notify.api.user_id' => 'test-user']);
        config(['sms-notify.api.api_key' => 'test-key']);
        
        Http::fake([
            '*' => Http::response(['status' => 'success'], 200),
        ]);
    }

    /** @test */
    public function it_can_send_sms()
    {
        $response = SmsNotify::send('771234567', 'Test message');
        
        $this->assertTrue($response['success']);
    }

    /** @test */
    public function it_can_check_balance()
    {
        $response = SmsNotify::checkBalance();
        
        $this->assertTrue($response['success']);
    }
}