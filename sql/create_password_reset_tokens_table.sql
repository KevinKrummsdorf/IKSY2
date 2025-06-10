CREATE TABLE password_reset_tokens (
    user_id INT NOT NULL,
    reset_token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    PRIMARY KEY (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (reset_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
