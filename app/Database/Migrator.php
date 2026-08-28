<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use RuntimeException;

class Migrator
{
    private PDO $db;

    private string $migrationPath;

    public function __construct()
    {
        $this->db = Database::connection();

        $this->migrationPath = __DIR__ . '/../../database/migrations';
    }

    public function run(): void
    {
        $this->createMigrationsTable();

        $files = glob($this->migrationPath . '/*.sql');

        if ($files === false) {
            throw new RuntimeException(
                'Unable to read the migrations directory.'
            );
        }

        sort($files);

        foreach ($files as $file) {
            $migration = basename($file);

            if ($this->hasRun($migration)) {
                continue;
            }

            $this->runMigration($file, $migration);
        }
    }

    private function createMigrationsTable(): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS migrations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_migrations_migration (migration)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
SQL;

        $this->db->exec($sql);
    }

    private function hasRun(string $migration): bool
    {
        $statement = $this->db->prepare(
            'SELECT COUNT(*) FROM migrations WHERE migration = ?'
        );

        $statement->execute([$migration]);

        return (int) $statement->fetchColumn() > 0;
    }

    private function runMigration(
        string $file,
        string $migration
    ): void {
        $sql = file_get_contents($file);

        if ($sql === false) {
            throw new RuntimeException(
                "Unable to read migration: {$migration}"
            );
        }

        try {
            $this->db->exec($sql);

            $statement = $this->db->prepare(
                'INSERT INTO migrations (migration) VALUES (?)'
            );

            $statement->execute([$migration]);

            echo "Applied: {$migration}" . PHP_EOL;
        } catch (\Throwable $e) {
            throw new RuntimeException(
                "Migration failed: {$migration}: " . $e->getMessage(),
                0,
                $e
            );
        }
    }
}