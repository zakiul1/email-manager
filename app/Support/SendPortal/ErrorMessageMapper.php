<?php

namespace App\Support\SendPortal;

class ErrorMessageMapper
{
    public function map(string $rawMessage): string
    {
        $message = mb_strtolower(trim($rawMessage));

        return match (true) {
            str_contains($message, 'authentication') || str_contains($message, 'username and password not accepted') => 'SMTP authentication failed. Check username, password, and provider security settings.',
            str_contains($message, 'connection could not be established') || str_contains($message, 'failed to connect') => 'SMTP connection failed. Check host, port, firewall, and encryption settings.',
            str_contains($message, 'timed out') => 'The mail server timed out. Try again or reduce provider latency.',
            str_contains($message, 'daily limit') || str_contains($message, 'hourly limit') => 'Sending is blocked because this account reached its configured limit.',
            str_contains($message, 'suppressed') => 'This recipient is suppressed and cannot receive campaign mail.',
            default => 'The operation failed. Check logs for technical details and review the current module settings.',
        };
    }
}