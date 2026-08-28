CREATE TABLE sessions (
    id VARCHAR(128) PRIMARY KEY,

    user_id BIGINT UNSIGNED NULL,

    ip_address VARCHAR(45) NULL,

    user_agent TEXT NULL,

    payload LONGTEXT NOT NULL,

    last_activity INT UNSIGNED NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_sessions_user_id (user_id),

    INDEX idx_sessions_last_activity (last_activity),

    CONSTRAINT fk_sessions_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;