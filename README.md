# Laravel SMS Notify

[![Latest Version on Packagist](https://img.shields.io/packagist/v/teaminfinitydev/laravel-sms-notify.svg?style=flat-square)](https://packagist.org/packages/teaminfinitydev/laravel-sms-notify)
[![GitHub Tests Action Status](https://github.com/teaminfinitydev/laravel-sms-notify/actions/workflows/tests.yml/badge.svg)](https://github.com/teaminfinitydev/laravel-sms-notify/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/teaminfinitydev/laravel-sms-notify.svg?style=flat-square)](https://packagist.org/packages/teaminfinitydev/laravel-sms-notify)

Laravel package for Notifi.lk SMS Gateway Integration

## Installation

You can install the package via composer:

```bash
composer require teaminfinitydev/laravel-sms-notify
```

## Configuration

1. Publish the configuration file:

```bash
php artisan vendor:publish --provider="TeamInfinityDev\SmsNotify\SmsNotifyServiceProvider"
```

2. Add the following variables to your .env file:

```env
NOTIFI_USER_ID=your-user-id
NOTIFI_API_KEY=your-api-key
NOTIFI_SENDER_ID=your-sender-id
```

## Usage

```php
use TeamInfinityDev\SmsNotify\Services\NotifyService;

$notifyService = new NotifyService();

// Send to single number
$response = $notifyService->send('771234567', 'Your message here');

// Send to multiple numbers
$response = $notifyService->send(['771234567', '772345678'], 'Your message here');

// Response format
[
    'success' => true,
    'data' => [
        'message_id' => 'xxx',
        'status' => 'queued'
    ],
    'status_code' => 200
]
```

### Check Balance

```php
$balance = $notifyService->checkBalance();

// Response format
[
    'success' => true,
    'data' => [
        'balance' => 100
    ],
    'status_code' => 200
]
```


### Check Delivery Status

```php
$status = $notifyService->getDeliveryReport('message-id-here');

// Response format
[
    'success' => true,
    'data' => [
        'status' => 'delivered'
    ],
    'status_code' => 200
]
```

## Features

- Send SMS to single or multiple numbers
- Automatic phone number formatting
- Check account balance
- Get delivery reports
- Configurable retry attempts
- Exception handling
- Comprehensive testing

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.