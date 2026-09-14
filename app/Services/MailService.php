<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class MailService
{
    /**
     * Send a password reset email.
     *
     * In local development, the message is written to
     * storage/logs/mail.log so the reset link can be tested
     * without a configured mail transport.
     *
     * In production, set MAIL_MODE=mail.
     */
    public function sendPasswordReset(
        string $email,
        string $name,
        string $plainToken
    ): void {
        $appUrl = rtrim(
            (string) ($_ENV['APP_URL'] ?? ''),
            '/'
        );

        $fromName = (string) (
            $_ENV['MAIL_FROM_NAME'] ?? 'My Application'
        );

        $fromAddress = (string) (
            $_ENV['MAIL_FROM_ADDRESS'] ?? ''
        );

        $mode = strtolower(
            (string) ($_ENV['MAIL_MODE'] ?? 'log')
        );

        if ($appUrl === '') {
            throw new RuntimeException(
                'APP_URL is not configured.'
            );
        }

        if (!filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )) {
            throw new RuntimeException(
                'Invalid recipient email address.'
            );
        }

        if (
            $mode === 'mail'
            && !filter_var(
                $fromAddress,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            throw new RuntimeException(
                'MAIL_FROM_ADDRESS is not configured correctly.'
            );
        }

        if ($fromAddress === '') {
            $fromAddress = 'no-reply@localhost';
        }

        $resetUrl = $appUrl
            . '/reset-password?token='
            . rawurlencode($plainToken);

        $subject = 'Reset your password';

        $body = "Hello {$name},\n\n"
            . "We received a request to reset the password for your account.\n\n"
            . "Use the link below to choose a new password:\n"
            . $resetUrl . "\n\n"
            . "This link expires in 60 minutes and can only be used once.\n\n"
            . "If you did not request a password reset, you can ignore this email.\n\n"
            . "Thanks,\n"
            . $fromName . "\n";

        if ($mode === 'log') {
            $this->writeToLog(
                $email,
                $subject,
                $body
            );

            return;
        }

        if ($mode !== 'mail') {
            throw new RuntimeException(
                'Unsupported MAIL_MODE. Use "log" or "mail".'
            );
        }

        $headers = [
            'From: ' . $fromName
                . ' <' . $fromAddress . '>',
            'Reply-To: ' . $fromAddress,
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8'
        ];

        $sent = mail(
            $email,
            $subject,
            wordwrap($body, 70),
            implode(
                "\r\n",
                $headers
            )
        );

        if (!$sent) {
            throw new RuntimeException(
                'The mail transport could not send the password reset email.'
            );
        }
    }

    private function writeToLog(
        string $email,
        string $subject,
        string $body
    ): void {
        $directory = __DIR__
            . '/../../storage/logs';

        if (
            !is_dir($directory)
            && !mkdir(
                $directory,
                0775,
                true
            )
            && !is_dir($directory)
        ) {
            throw new RuntimeException(
                'Unable to create the application log directory.'
            );
        }

        $entry = "\n"
            . "========================================\n"
            . date('Y-m-d H:i:s') . "\n"
            . "To: {$email}\n"
            . "Subject: {$subject}\n"
            . "----------------------------------------\n"
            . $body
            . "========================================\n";

        if (
            file_put_contents(
                $directory . '/mail.log',
                $entry,
                FILE_APPEND | LOCK_EX
            ) === false
        ) {
            throw new RuntimeException(
                'Unable to write the development mail log.'
            );
        }
    }
}