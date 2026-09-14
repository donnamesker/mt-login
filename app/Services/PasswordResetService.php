<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Database;
use App\Models\User;
use DateTimeImmutable;
use PDO;
use RuntimeException;

class PasswordResetService
{
    private const TOKEN_TTL_MINUTES = 60;
    private const REQUEST_COOLDOWN_SECONDS = 60;

    private PDO $db;
    private User $users;
    private MailService $mail;

    public function __construct()
    {
        $this->db = Database::connection();
        $this->users = new User();
        $this->mail = new MailService();
    }

    /**
     * Create and send a password-reset link for an email address.
     *
     * A missing account is intentionally treated the same as a real account.
     */
    public function requestReset(string $email): void
    {
        $email = strtolower(trim($email));

        $this->deleteExpiredTokens();

        $user = $this->users->findByEmail($email);

        if ($user === null) {
            return;
        }

        if ($this->wasRecentlyRequested((int) $user['id'])) {
            return;
        }

        $plainToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $plainToken);

        $expiresAt = (new DateTimeImmutable('now'))
            ->modify(
                '+' . self::TOKEN_TTL_MINUTES . ' minutes'
            )
            ->format('Y-m-d H:i:s');

        // Only one active token is needed per user.
        $statement = $this->db->prepare(
            'DELETE FROM password_reset_tokens
             WHERE user_id = :user_id'
        );

        $statement->execute([
            'user_id' => (int) $user['id']
        ]);

        $statement = $this->db->prepare(
            'INSERT INTO password_reset_tokens
                (user_id, token_hash, expires_at)
             VALUES
                (:user_id, :token_hash, :expires_at)'
        );

        $statement->execute([
            'user_id' => (int) $user['id'],
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt
        ]);

        $this->mail->sendPasswordReset(
            $user['email'],
            $user['name'],
            $plainToken
        );
    }

    /**
     * Determine whether a reset token is currently valid.
     */
    public function tokenIsValid(string $plainToken): bool
    {
        if (!$this->isValidTokenFormat($plainToken)) {
            return false;
        }

        $tokenHash = hash('sha256', $plainToken);

        $statement = $this->db->prepare(
            'SELECT id
             FROM password_reset_tokens
             WHERE token_hash = :token_hash
               AND expires_at > NOW()
             LIMIT 1'
        );

        $statement->execute([
            'token_hash' => $tokenHash
        ]);

        return $statement->fetchColumn() !== false;
    }

    /**
     * Replace the user's password and invalidate all reset tokens
     * for that user.
     */
    public function resetPassword(
        string $plainToken,
        string $newPassword
    ): void {
        if (!$this->isValidTokenFormat($plainToken)) {
            throw new RuntimeException(
                'Invalid password reset token.'
            );
        }

        if (strlen($newPassword) < 12) {
            throw new RuntimeException(
                'Password must be at least 12 characters.'
            );
        }

        $tokenHash = hash('sha256', $plainToken);

        $passwordHash = password_hash(
            $newPassword,
            PASSWORD_DEFAULT
        );

        if ($passwordHash === false) {
            throw new RuntimeException(
                'Unable to create password hash.'
            );
        }

        try {
            $this->db->beginTransaction();

            $statement = $this->db->prepare(
                'SELECT user_id
                 FROM password_reset_tokens
                 WHERE token_hash = :token_hash
                   AND expires_at > NOW()
                 LIMIT 1
                 FOR UPDATE'
            );

            $statement->execute([
                'token_hash' => $tokenHash
            ]);

            $userId = $statement->fetchColumn();

            if ($userId === false) {
                throw new RuntimeException(
                    'Invalid or expired password reset token.'
                );
            }

            $statement = $this->db->prepare(
                'UPDATE users
                 SET password_hash = :password_hash
                 WHERE id = :user_id'
            );

            $statement->execute([
                'password_hash' => $passwordHash,
                'user_id' => (int) $userId
            ]);

            $statement = $this->db->prepare(
                'DELETE FROM password_reset_tokens
                 WHERE user_id = :user_id'
            );

            $statement->execute([
                'user_id' => (int) $userId
            ]);

            $this->db->commit();

        } catch (\Throwable $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            if ($e instanceof RuntimeException) {
                throw $e;
            }

            throw new RuntimeException(
                'Unable to reset password.',
                0,
                $e
            );
        }
    }

    private function wasRecentlyRequested(
        int $userId
    ): bool {
        $cutoff = (new DateTimeImmutable('now'))
            ->modify(
                '-' . self::REQUEST_COOLDOWN_SECONDS . ' seconds'
            )
            ->format('Y-m-d H:i:s');

        $statement = $this->db->prepare(
            'SELECT id
             FROM password_reset_tokens
             WHERE user_id = :user_id
               AND created_at > :cutoff
             LIMIT 1'
        );

        $statement->execute([
            'user_id' => $userId,
            'cutoff' => $cutoff
        ]);

        return $statement->fetchColumn() !== false;
    }

    private function deleteExpiredTokens(): void
    {
        $this->db->exec(
            'DELETE FROM password_reset_tokens
             WHERE expires_at <= NOW()'
        );
    }

    private function isValidTokenFormat(
        string $plainToken
    ): bool {
        return preg_match(
            '/\A[a-f0-9]{64}\z/',
            $plainToken
        ) === 1;
    }
}