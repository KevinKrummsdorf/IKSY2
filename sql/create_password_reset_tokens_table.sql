CREATE TABLE password_reset_tokens (
    user_id INT PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    reset_token VARCHAR(64) NOT NULL,
    expires_at TIMESTAMP NOT NULL
);

CREATE INDEX idx_password_reset_token ON password_reset_tokens (reset_token);
