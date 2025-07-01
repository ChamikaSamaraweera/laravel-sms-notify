# Laravel SMS Notify

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
use TeamInfinityDev\SmsNotify\Facades\SmsNotify;

// Send SMS to a single number
SmsNotify::send('771234567', 'Your message here');

// Send SMS to multiple numbers
SmsNotify::send(['771234567', '777654321'], 'Your message here');

// Check balance
$balance = SmsNotify::checkBalance();

// Get delivery report
$report = SmsNotify::getDeliveryReport('message-id-123');
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