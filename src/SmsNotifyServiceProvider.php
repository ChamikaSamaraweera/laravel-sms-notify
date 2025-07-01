<?php

namespace TeamInfinityDev\SmsNotify;

use Illuminate\Support\ServiceProvider;
use TeamInfinityDev\SmsNotify\Services\NotifyService;

class SmsNotifyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/sms-notify.php' => config_path('sms-notify.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/sms-notify.php', 'sms-notify'
        );

        $this->app->singleton('sms-notify', function ($app) {
            return new NotifyService();
        });
    }
}