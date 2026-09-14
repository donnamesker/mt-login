<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Database\Database;
use App\Models\User;
use App\Services\AuthService;
use App\Services\PasswordResetService;

$db = Database::connection();
$auth = new AuthService();
$service = new PasswordResetService();

$email = 'password-reset-test@example.com';
$originalPassword = 'OriginalPassword123!';
$newPassword = 'NewPassword456!';
$userId = null;

try {
    echo "Creating test user...\n";

    $existing = (new User())->findByEmail($email);

    if ($existing !== null) {
        $userId = (int) $existing['id'];
    } else {
        $userId = $auth->register(
            'Password Reset Test User',
            $email,
            $originalPassword
        );
    }

    $delete = $db->prepare(
        'DELETE FROM password_reset_tokens
         WHERE user_id = :user_id'
    );

    $delete->execute([
        'user_id' => $userId
    ]);

    echo "Requesting password reset...\n";

    $log = __DIR__ . '/../storage/logs/mail.log';

    $logOffset = file_exists($log)
        ? (int) filesize($log)
        : 0;

    $service->requestReset($email);

    $statement = $db->prepare(
        'SELECT token_hash, expires_at
         FROM password_reset_tokens
         WHERE user_id = :user_id
         LIMIT 1'
    );

    $statement->execute([
        'user_id' => $userId
    ]);

    $row = $statement->fetch();

    if (!$row) {
        throw new RuntimeException(
            'Password reset token was not created.'
        );
    }

    echo "Password reset token created: PASS\n";

    /*
     * Local MAIL_MODE=log writes the reset link to the development
     * mail log. The test extracts the newly-created token from that entry.
     */
    if (!file_exists($log)) {
        throw new RuntimeException(
            'Development mail log was not created. Set MAIL_MODE=log for tests.'
        );
    }

    $contents = file_get_contents($log);

    if ($contents === false) {
        throw new RuntimeException(
            'Unable to read development mail log.'
        );
    }

    $newLog = substr(
        $contents,
        $logOffset
    );

    preg_match(
        '/https?:\/\/[^\s]+\/reset-password\?token=([a-f0-9]{64})/',
        $newLog,
        $matches
    );

    if (empty($matches[1])) {
        throw new RuntimeException(
            'Could not find the reset token in the development mail log.'
        );
    }

    $plainToken = $matches[1];

    if (!$service->tokenIsValid($plainToken)) {
        throw new RuntimeException(
            'Valid reset token was rejected.'
        );
    }

    echo "Valid password reset token accepted: PASS\n";

    if ($service->tokenIsValid(str_repeat('a', 64))) {
        throw new RuntimeException(
            'Invalid reset token was accepted.'
        );
    }

    echo "Invalid password reset token rejected: PASS\n";

    echo "Resetting password...\n";

    $service->resetPassword(
        $plainToken,
        $newPassword
    );

    $user = $auth->authenticate(
        $email,
        $newPassword
    );

    if ($user === null) {
        throw new RuntimeException(
            'The new password could not authenticate the user.'
        );
    }

    echo "New password authenticates: PASS\n";

    if ($service->tokenIsValid($plainToken)) {
        throw new RuntimeException(
            'Reset token remained valid after password reset.'
        );
    }

    echo "Reset token invalidated after use: PASS\n";

} finally {

    if ($userId !== null) {

        $statement = $db->prepare(
            'DELETE FROM password_reset_tokens
             WHERE user_id = :user_id'
        );

        $statement->execute([
            'user_id' => $userId
        ]);

        $statement = $db->prepare(
            'DELETE FROM users
             WHERE id = :user_id'
        );

        $statement->execute([
            'user_id' => $userId
        ]);
    }
}