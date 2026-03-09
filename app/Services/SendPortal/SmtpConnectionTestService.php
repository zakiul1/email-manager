<?php

namespace App\Services\SendPortal;

use App\Models\SendPortal\SmtpAccount;
use Exception;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Mail;

class SmtpConnectionTestService
{
    public function __construct(
        protected SmtpAccountEncryption $encryption
    ) {
    }

    public function test(SmtpAccount $account): array
    {
        try {
            $mailerName = 'sp_test_'.$account->id.'_'.time();

            config()->set("mail.mailers.{$mailerName}", [
                'transport' => 'smtp',
                'host' => $account->host,
                'port' => $account->port,
                'username' => $account->username,
                'password' => $this->encryption->decrypt($account->encrypted_password),
                'encryption' => $account->encryption,
                'timeout' => 15,
            ]);

            $mailManager = app(MailManager::class);

            if (method_exists($mailManager, 'forgetMailers')) {
                $mailManager->forgetMailers();
            }

            $mailManager->mailer($mailerName);

            Mail::mailer($mailerName)->raw(
                'SMTP connection test from SendPortal workspace.',
                function ($message) use ($account) {
                    $to = $account->from_email ?: config('mail.from.address');
                    $fromAddress = $account->from_email ?: config('mail.from.address');
                    $fromName = $account->from_name ?: config('mail.from.name');

                    $message->to($to)
                        ->subject('SMTP Connection Test');

                    if ($fromAddress) {
                        $message->from($fromAddress, $fromName);
                    }

                    if ($account->reply_to_email) {
                        $message->replyTo(
                            $account->reply_to_email,
                            $account->reply_to_name ?: null
                        );
                    }
                }
            );

            $account->update([
                'last_tested_at' => now(),
                'last_test_status' => 'success',
                'last_test_message' => 'Connection test email sent successfully.',
            ]);

            return [
                'ok' => true,
                'message' => 'Connection test email sent successfully.',
            ];
        } catch (Exception $exception) {
            $account->update([
                'last_tested_at' => now(),
                'last_test_status' => 'failed',
                'last_test_message' => $exception->getMessage(),
            ]);

            return [
                'ok' => false,
                'message' => $exception->getMessage(),
            ];
        }
    }
}