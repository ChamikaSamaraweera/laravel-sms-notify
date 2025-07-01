# Laravel SMS Notify

[![Latest Version on Packagist](https://img.shields.io/packagist/v/teaminfinitydev/laravel-sms-notify.svg?style=flat-square)](https://packagist.org/packages/teaminfinitydev/laravel-sms-notify)
[![GitHub Tests Action Status](https://github.com/teaminfinitydev/laravel-sms-notify/actions/workflows/tests.yml/badge.svg)](https://github.com/teaminfinitydev/laravel-sms-notify/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/teaminfinitydev/laravel-sms-notify.svg?style=flat-square)](https://packagist.org/packages/teaminfinitydev/laravel-sms-notify)

Laravel package for Notify.lk SMS Gateway Integration

## Requirements

- PHP 8.1, 8.2, or 8.3
- Laravel 9.x, 10.x, 11.x, or 12.x

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
# Required Configuration
NOTIFI_USER_ID=your-user-id
NOTIFI_API_KEY=your-api-key
NOTIFI_SENDER_ID=your-sender-id

# Optional Configuration
NOTIFI_API_URL=https://notifi.lk/api/v1
NOTIFI_TIMEOUT=30
NOTIFI_CONNECT_TIMEOUT=10
NOTIFI_SSL_VERIFY=true
NOTIFI_RETRY_ATTEMPTS=3
NOTIFI_RETRY_DELAY=1

# Development/Testing Configuration
NOTIFI_MOCK_RESPONSES=false
NOTIFI_LOG_REQUESTS=false
```

### For Local Development

If you're having SSL or connection issues in local development, add these to your `.env`:

```env
# Disable SSL verification for local development
NOTIFI_SSL_VERIFY=false

# Enable request logging for debugging
NOTIFI_LOG_REQUESTS=true

# Use mock responses for testing without API calls
NOTIFI_MOCK_RESPONSES=true
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

## Laravel Version Compatibility

| Laravel Version | Package Version | PHP Version Required |
|----------------|----------------|---------------------|
| 9.x            | ^1.1           | ^8.1, ^8.2, ^8.3   |
| 10.x           | ^1.1           | ^8.1, ^8.2, ^8.3   |
| 11.x           | ^1.1           | ^8.2, ^8.3         |
| 12.x           | ^1.1           | ^8.2, ^8.3         |

## Features

- Send SMS to single or multiple numbers
- Automatic phone number formatting
- Configurable retry attempts
- Exception handling
- Comprehensive testing
- Support for Laravel 9, 10, 11, and 12
- **SSL verification control for local development**
- **Request logging for debugging**
- **Mock responses for testing**
- **Connection timeout and retry configuration**
- **Detailed error handling for network issues**

## Troubleshooting

### Common Issues

1. **"Could not resolve host" error in local development:**
   ```env
   NOTIFI_SSL_VERIFY=false
   ```

2. **Connection timeout issues:**
   ```env
   NOTIFI_TIMEOUT=60
   NOTIFI_CONNECT_TIMEOUT=30
   ```

3. **Enable debugging:**
   ```env
   NOTIFI_LOG_REQUESTS=true
   ```

4. **Test without real API calls:**
   ```env
   NOTIFI_MOCK_RESPONSES=true
   ```

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.