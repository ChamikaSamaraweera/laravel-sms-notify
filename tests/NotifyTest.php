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
    }

    /** @test */
    public function test_addition_works()
    {
        $result = 2 + 2;
        $this->assertEquals(4, $result);
    }
}