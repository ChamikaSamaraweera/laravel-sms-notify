<?php

namespace TeamInfinityDev\SmsNotify\Exceptions;

use Exception;

class NotifyException extends Exception
{
    /**
     * Create a new exception for invalid credentials.
     */
    public static function invalidCredentials(): self
    {
        return new self('Notifi.lk USER_ID and API_KEY are required. Please check your configuration.');
    }

    /**
     * Create a new exception for invalid phone number.
     */
    public static function invalidPhoneNumber(string $number): self
    {
        return new self("Invalid phone number format: {$number}. Phone number must be a valid Sri Lankan number.");
    }

    /**
     * Create a new exception for empty message.
     */
    public static function emptyMessage(): self
    {
        return new self('Message content cannot be empty.');
    }

    /**
     * Create a new exception for API errors.
     */
    public static function apiError(string $message, int $code = 0): self
    {
        return new self("Notifi.lk API Error: {$message}", $code);
    }

    /**
     * Create a new exception for invalid sender ID.
     */
    public static function invalidSenderId(string $senderId): self
    {
        return new self("Invalid sender ID: {$senderId}. Sender ID must be registered with Notifi.lk");
    }

    /**
     * Create a new exception for network errors.
     */
    public static function networkError(string $message): self
    {
        return new self("Network Error: Unable to connect to Notifi.lk API. {$message}");
    }

    /**
     * Create a new exception for rate limit exceeded.
     */
    public static function rateLimitExceeded(): self
    {
        return new self('API rate limit exceeded. Please try again later.');
    }


    /**
     * Create a new exception for invalid message ID.
     */
    public static function invalidMessageId(string $messageId): self
    {
        return new self("Invalid message ID: {$messageId}");
    }
}