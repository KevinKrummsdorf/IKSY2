CREATE TABLE user_verification (
    user_id INT PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    is_verified BOOLEAN NOT NULL DEFAULT FALSE
);
