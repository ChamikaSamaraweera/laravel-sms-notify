<?php

namespace TeamInfinityDev\SmsNotify\Facades;

use Illuminate\Support\Facades\Facade;

class SmsNotify extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'sms-notify';
    }
}